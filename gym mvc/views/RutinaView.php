<?php
require_once __DIR__ . '/Layout.php';

class RutinaView
{
    private array $ejerciciosActuales = [];

    // $rutina: rutina seleccionada (o null si se está creando una nueva)
    // $opciones['formularioNuevo']: true -> mostrar el form en blanco para "Nueva rutina"
    public function render(?array $rutina, array $rutinas, array $ejercicios, array $asignados, array $opciones = []): void
    {
        $formularioNuevo = $opciones['formularioNuevo'] ?? !$rutina;
        $this->ejerciciosActuales = $ejercicios;

        // ---- columna izquierda: lista de rutinas + botón "Nueva" ----
        $filasLista = '';
        foreach ($rutinas as $r) {
            $activo = !$formularioNuevo && $rutina && (int)$rutina['id'] === (int)$r['id'];
            $clase = $activo
                ? 'border-indigo-600 bg-indigo-50 text-indigo-700 font-medium'
                : 'border-transparent text-slate-700 hover:bg-slate-50';
            $filasLista .= '<a href="/rutina?rutina=' . $r['id'] . '" class="block px-4 py-3 text-sm border-l-4 ' . $clase . '">' . esc($r['nombre']) . '</a>';
        }
        if ($filasLista === '') {
            $filasLista = '<div class="px-4 py-3 text-sm text-slate-400">No hay rutinas</div>';
        }
        $listaRutinas = '
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
          <span class="text-sm font-semibold text-slate-600">Rutinas</span>
          <a href="/rutina?nueva=1" class="text-xs font-medium text-indigo-600 hover:underline">+ Nueva</a>
        </div>
        <nav class="divide-y divide-slate-100 max-h-[600px] overflow-y-auto">' . $filasLista . '</nav>
      </div>';

        // ---- columna derecha: form de la cabecera (rutina) ----
        $r = $rutina ?? ['id' => null, 'nombre' => '', 'descripcion' => '', 'duracion_semanas' => ''];
        $titulo = $rutina ? 'Editar rutina' : 'Nueva rutina';
        $idHidden = $rutina ? '<input type="hidden" name="id" value="' . $r['id'] . '">' : '';
        $btnEliminar = $rutina
            ? '<form method="POST" action="/rutina?eliminar=' . $r['id'] . '" class="inline" onsubmit="return confirm(\'¿Eliminar esta rutina y sus ejercicios asignados?\')">
                 <button class="px-4 py-2 rounded-lg border border-rose-300 text-rose-600 hover:bg-rose-50">Eliminar rutina</button>
               </form>'
            : '';

        // Al crear (sin rutina todavía) la cabecera y el detalle van en UN
        // solo <form>: los ejercicios se acumulan por JS como inputs hidden
        // y viajan junto con el POST que crea la rutina (como un pedido con
        // su detalle, en un único alta).
        $formId = $rutina ? '' : ' id="form-nueva-rutina"';

        $formularioCabecera = '
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h1 class="text-lg font-semibold text-slate-800 mb-4">' . $titulo . '</h1>
        <form method="POST" action="/rutina" class="grid grid-cols-1 sm:grid-cols-6 gap-3 items-end"' . $formId . '>
          ' . $idHidden . '
          <div class="sm:col-span-2">
            <label class="block text-sm text-slate-600 mb-1">Nombre</label>
            <input name="nombre" value="' . esc($r['nombre']) . '" class="w-full border border-slate-300 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500" required>
          </div>
          <div class="sm:col-span-3">
            <label class="block text-sm text-slate-600 mb-1">Descripción</label>
            <input name="descripcion" value="' . esc($r['descripcion']) . '" class="w-full border border-slate-300 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500">
          </div>
          <div class="sm:col-span-1">
            <label class="block text-sm text-slate-600 mb-1">Semanas</label>
            <input name="duracion_semanas" type="number" min="1" value="' . esc($r['duracion_semanas']) . '" class="w-full border border-slate-300 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500" required>
          </div>';

        if (!$rutina) {
            // ---- detalle embebido dentro del mismo form, para crear todo junto ----
            $formularioCabecera .= '
          <div class="sm:col-span-6 pt-3 mt-1 border-t border-slate-100">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Agregar ejercicios (se guardan junto con la rutina)</h2>
            ' . $this->camposDetalle('nuevo-') . '
              <div class="col-span-2 sm:col-span-1 text-right">
                <button type="button" id="btn-agregar-pendiente" class="w-full bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg text-sm">Agregar</button>
              </div>
            </div>

            <div class="mt-3 bg-slate-50 rounded-xl border border-slate-200 overflow-x-auto">
              ' . $this->tablaCabecera() . '
                <tbody id="pendientes-body">
                  <tr id="pendientes-vacio"><td colspan="7" class="px-3 py-4 text-center text-slate-400">Todavía no agregaste ejercicios</td></tr>
                </tbody>
              </table>
            </div>
          </div>';
        }

        $formularioCabecera .= '
          <div class="sm:col-span-6 flex gap-2 pt-1">
            <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-500">Guardar rutina</button>
            ' . $btnEliminar . '
          </div>
        </form>
      </div>';

        if (!$rutina) {
            $formularioCabecera .= $this->scriptPendientes();
        }

        // ---- detalle: ejercicios de la rutina (solo si ya existe la rutina) ----
        $detalle = '';
        if ($rutina) {
            $formularioDetalle = '
      <div>
        <h2 class="text-base font-semibold text-slate-800 mb-3">Ejercicios de esta rutina</h2>
        <form method="POST" action="/rutina" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
          <input type="hidden" name="accion" value="agregar_ejercicio">
          <input type="hidden" name="rutina_id" value="' . $rutina['id'] . '">
          ' . $this->camposDetalle('') . '
            <div class="col-span-2 sm:col-span-1 text-right">
              <button class="w-full bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm">Agregar</button>
            </div>
          </div>
        </form>
      </div>';

            $filasTabla = '';
            foreach ($asignados as $a) {
                $filasTabla .= '
      <tr class="hover:bg-slate-50">
        <td class="px-3 py-2 text-slate-400">' . $a['orden'] . '</td>
        <td class="px-3 py-2 font-medium text-slate-800">' . esc($a['ejercicio_nombre']) . '</td>
        <td class="px-3 py-2">' . $a['series'] . '</td>
        <td class="px-3 py-2">' . esc($a['repeticiones']) . '</td>
        <td class="px-3 py-2">' . esc($a['peso_sugerido'] ?: '—') . '</td>
        <td class="px-3 py-2">' . esc($a['notas'] ?: '—') . '</td>
        <td class="px-3 py-2 text-right">
          <form method="POST" action="/rutina" class="inline" onsubmit="return confirm(\'¿Quitar este ejercicio de la rutina?\')">
            <input type="hidden" name="accion" value="eliminar_ejercicio">
            <input type="hidden" name="rutina_id" value="' . $rutina['id'] . '">
            <input type="hidden" name="ejercicio_id" value="' . $a['ejercicio_id'] . '">
            <button class="text-rose-600 hover:underline">Quitar</button>
          </form>
        </td>
      </tr>';
            }
            if ($filasTabla === '') {
                $filasTabla = '<tr><td colspan="7" class="px-3 py-6 text-center text-slate-400">Esta rutina no tiene ejercicios</td></tr>';
            }

            $tablaDetalle = '
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-x-auto">
        ' . $this->tablaCabecera() . '
          <tbody class="divide-y divide-slate-100">' . $filasTabla . '</tbody>
        </table>
      </div>';

            $detalle = '<div class="space-y-6 pt-2">' . $formularioDetalle . $tablaDetalle . '</div>';
        }

        $contenido = '
      <h1 class="text-xl font-bold text-slate-800 mb-6">Rutinas</h1>
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1">' . $listaRutinas . '</div>
        <div class="lg:col-span-3 space-y-6">' . $formularioCabecera . $detalle . '</div>
      </div>';

        echo layout('Rutinas', 'rutina', $contenido);
    }

    // Campos del ejercicio a agregar. $prefijo distingue los ids cuando se
    // repite el bloque (form embebido en "nueva rutina" vs. instantáneo
    // en una rutina ya existente).
    private function camposDetalle(string $prefijo): string
    {
        $opcionesEj = '';
        foreach ($this->ejerciciosActuales as $e) {
            $opcionesEj .= '<option value="' . $e['id'] . '">' . esc($e['nombre']) . '</option>';
        }

        $idEj = $prefijo . 'ejercicio-id';
        $idSeries = $prefijo . 'series';
        $idReps = $prefijo . 'repeticiones';
        $idPeso = $prefijo . 'peso';
        $idOrden = $prefijo . 'orden';
        $idNotas = $prefijo . 'notas';

        // Cuando no hay prefijo (form instantáneo de rutina ya existente) los
        // campos van con name= para que viajen por POST directo.
        $attrEj = $prefijo === '' ? ' name="ejercicio_id"' : '';
        $attrSeries = $prefijo === '' ? ' name="series"' : '';
        $attrReps = $prefijo === '' ? ' name="repeticiones"' : '';
        $attrPeso = $prefijo === '' ? ' name="peso_sugerido"' : '';
        $attrOrden = $prefijo === '' ? ' name="orden"' : '';
        $attrNotas = $prefijo === '' ? ' name="notas"' : '';

        return '
            <div class="grid grid-cols-2 sm:grid-cols-6 gap-2 items-end">
              <div class="col-span-2">
                <label class="block text-xs font-medium text-slate-500 mb-1">Ejercicio</label>
                <select id="' . $idEj . '"' . $attrEj . ' class="w-full border border-slate-300 rounded-lg px-2 py-2 text-sm outline-none">' . $opcionesEj . '</select>
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Series</label>
                <input id="' . $idSeries . '"' . $attrSeries . ' type="number" min="1" class="w-full border border-slate-300 rounded-lg px-2 py-2 text-sm outline-none">
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Reps</label>
                <input id="' . $idReps . '"' . $attrReps . ' type="text" placeholder="8-10" class="w-full border border-slate-300 rounded-lg px-2 py-2 text-sm outline-none">
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Peso</label>
                <input id="' . $idPeso . '"' . $attrPeso . ' type="text" placeholder="40kg" class="w-full border border-slate-300 rounded-lg px-2 py-2 text-sm outline-none">
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Orden</label>
                <input id="' . $idOrden . '"' . $attrOrden . ' type="number" min="1" value="1" class="w-full border border-slate-300 rounded-lg px-2 py-2 text-sm outline-none">
              </div>
              <div class="col-span-2 sm:col-span-5">
                <label class="block text-xs font-medium text-slate-500 mb-1">Notas</label>
                <input id="' . $idNotas . '"' . $attrNotas . ' type="text" placeholder="Opcional" class="w-full border border-slate-300 rounded-lg px-2 py-2 text-sm outline-none">
              </div>';
    }

    private function tablaCabecera(): string
    {
        return '
        <table class="w-full text-sm">
          <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
              <th class="px-3 py-2 font-medium">#</th>
              <th class="px-3 py-2 font-medium">Ejercicio</th>
              <th class="px-3 py-2 font-medium">Series</th>
              <th class="px-3 py-2 font-medium">Reps</th>
              <th class="px-3 py-2 font-medium">Peso</th>
              <th class="px-3 py-2 font-medium">Notas</th>
              <th class="px-3 py-2 font-medium text-right">Quitar</th>
            </tr>
          </thead>';
    }

    // JS: junta los ejercicios elegidos como inputs hidden dentro del mismo
    // <form id="form-nueva-rutina">, para que viajen en el mismo POST que
    // crea la rutina (un solo alta de cabecera + detalle).
    private function scriptPendientes(): string
    {
        return <<<'HTML'
      <script>
      (function () {
        const form = document.getElementById('form-nueva-rutina');
        if (!form) return;

        const tbody = document.getElementById('pendientes-body');
        const filaVacia = document.getElementById('pendientes-vacio');
        let contador = 0;

        function campo(id) { return document.getElementById(id); }
        function celda(texto) {
            const td = document.createElement('td');
            td.className = 'px-3 py-2';
            td.textContent = texto;
            return td;
        }

        function agregarHidden(idx, nombre, valor) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `ejercicios[${idx}][${nombre}]`;
            input.value = valor;
            form.appendChild(input);
        }

        function agregarPendiente() {
            const select = campo('nuevo-ejercicio-id');
            const series = campo('nuevo-series').value.trim();
            const repeticiones = campo('nuevo-repeticiones').value.trim();
            const peso = campo('nuevo-peso').value.trim();
            const orden = campo('nuevo-orden').value.trim() || '1';
            const notas = campo('nuevo-notas').value.trim();

            if (!select.value) { alert('Elige un ejercicio.'); return; }
            if (!series || !repeticiones) { alert('Completa series y repeticiones.'); return; }

            const idx = contador++;
            agregarHidden(idx, 'ejercicio_id', select.value);
            agregarHidden(idx, 'series', series);
            agregarHidden(idx, 'repeticiones', repeticiones);
            agregarHidden(idx, 'peso_sugerido', peso);
            agregarHidden(idx, 'orden', orden);
            agregarHidden(idx, 'notas', notas);

            if (filaVacia) filaVacia.remove();

            const tr = document.createElement('tr');
            tr.id = 'pendiente-' + idx;
            tr.appendChild(celda(orden));
            tr.appendChild(celda(select.options[select.selectedIndex].text));
            tr.appendChild(celda(series));
            tr.appendChild(celda(repeticiones));
            tr.appendChild(celda(peso || '—'));
            tr.appendChild(celda(notas || '—'));

            const tdAccion = document.createElement('td');
            tdAccion.className = 'px-3 py-2 text-right';
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'text-rose-600 hover:underline';
            btn.textContent = 'Quitar';
            btn.addEventListener('click', () => {
                tr.remove();
                form.querySelectorAll(`input[name^="ejercicios[${idx}]"]`).forEach(i => i.remove());
                if (!tbody.querySelector('tr')) tbody.appendChild(filaVacia);
            });
            tdAccion.appendChild(btn);
            tr.appendChild(tdAccion);

            tbody.appendChild(tr);

            campo('nuevo-series').value = '';
            campo('nuevo-repeticiones').value = '';
            campo('nuevo-peso').value = '';
            campo('nuevo-notas').value = '';
            campo('nuevo-orden').value = String(parseInt(orden, 10) + 1);
        }

        campo('btn-agregar-pendiente').addEventListener('click', agregarPendiente);
      })();
      </script>
HTML;
    }
}
