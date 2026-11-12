<?php
require_once __DIR__ . '/Layout.php';

class ServicioView
{
    public function render(array $clientes, array $servicios, array $historial, ?array $recibo, array $opciones = []): void
    {
        $error = $opciones['error'] ?? null;
        $usuarioIdSeleccionado = $opciones['usuarioIdSeleccionado'] ?? 0;
        $servicioIdSeleccionado = $opciones['servicioIdSeleccionado'] ?? 0;
        $categoriaSeleccionada = $opciones['categoriaSeleccionada'] ?? 'normal';

        $categorias = [
            'normal'      => 'Normal — precio base',
            'vip'         => 'VIP — 15% descuento',
            'estudiante'  => 'Estudiante — 10% descuento',
            'corporativo' => 'Corporativo — 5% descuento',
            'promocion'   => 'Promoción — 20% descuento',
        ];

        // Monto chiquito al lado del botón mientras es vista previa
        $montoInline = '';
        if ($recibo && $recibo['preview']) {
            $montoInline = '<span class="text-sm text-slate-600">Total: <strong class="text-indigo-700">Bs ' . number_format($recibo['precioFinal'], 2) . '</strong></span>';
        }

        $bannerError = $error
            ? '<div class="mb-4 max-w-lg rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">' . esc($error) . '</div>'
            : '';

        // ---- lista de servicios (izquierda) ----
        $filasLista = '';
        foreach ($servicios as $s) {
            $checked = (int)$s['id'] === (int)$servicioIdSeleccionado ? 'checked' : '';
            $filasLista .= '
      <label class="flex items-center justify-between gap-2 px-4 py-3 text-sm border-l-4 border-transparent hover:bg-slate-50 cursor-pointer has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50 has-[:checked]:text-indigo-700 has-[:checked]:font-medium">
        <span class="flex items-center gap-2">
          <input type="radio" name="servicio_id" value="' . $s['id'] . '" form="form-servicio" class="accent-indigo-600 js-auto-preview" ' . $checked . ' required>
          ' . esc($s['nombre']) . '
        </span>
        <span class="text-slate-500 text-xs">Bs ' . number_format((float)$s['precio'], 2) . '</span>
      </label>';
        }
        if ($filasLista === '') {
            $filasLista = '<div class="px-4 py-3 text-sm text-slate-400">No hay servicios</div>';
        }
        $listaServicios = '
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 text-sm font-semibold text-slate-600">Servicios</div>
        <div class="divide-y divide-slate-100">' . $filasLista . '</div>
      </div>';

        // ---- select de cliente ----
        $opcionesCliente = '<option value="">-- Selecciona un cliente --</option>';
        foreach ($clientes as $c) {
            $sel = (int)$c['id'] === (int)$usuarioIdSeleccionado ? 'selected' : '';
            $opcionesCliente .= '<option value="' . $c['id'] . '" ' . $sel . '>' . esc($c['nombre'] . ' ' . $c['apellido']) . '</option>';
        }

        // ---- categorías de socio ----
        $opcionesCategoria = '';
        foreach ($categorias as $valor => $etiqueta) {
            $checked = $valor === $categoriaSeleccionada ? 'checked' : '';
            $opcionesCategoria .= '
          <label class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg text-sm cursor-pointer hover:bg-slate-50 has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50">
            <input type="radio" name="categoria" value="' . $valor . '" form="form-servicio" class="accent-indigo-600 js-auto-preview" ' . $checked . '>
            ' . esc($etiqueta) . '
          </label>';
        }

        // ---- formulario (derecha) ----
        $formulario = '
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h1 class="text-lg font-semibold text-slate-800 mb-4">Contratar servicio</h1>
        ' . $bannerError . '
        <form id="form-servicio" method="POST" action="/servicios">
          <input type="hidden" name="accion" id="s-accion" value="calcular">
          <div class="mb-3">
            <label class="block text-sm text-slate-600 mb-1">Cliente</label>
            <select name="usuario_id" class="js-auto-preview w-full border border-slate-300 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500" required>' . $opcionesCliente . '</select>
          </div>
          <label class="block text-sm text-slate-600 mb-1">Categoría de socio</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-3">' . $opcionesCategoria . '</div>
          <div class="flex items-center gap-3 pt-1">
            <button type="button" onclick="servicioEnviar(\'contratar\')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-500">Contratar</button>
            ' . $montoInline . '
          </div>
        </form>
      </div>';

        // ---- recibo (solo cuando ya se confirmó la contratación) ----
        $panelRecibo = '';
        if ($recibo && !$recibo['preview']) {
            $etiquetaCategoria = $categorias[$recibo['categoria']] ?? $recibo['categoria'];
            $nota = $recibo['nota'];

            $panelRecibo = '
      <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5">
        <h2 class="text-sm font-semibold text-indigo-800 mb-3">Contratación confirmada &middot; <span class="px-2 py-0.5 rounded-full text-xs bg-indigo-100">' . esc($etiquetaCategoria) . '</span></h2>
        <table class="text-sm w-full max-w-md">
          <tr><td class="px-3 py-2 text-slate-500 w-32">Cliente</td><td class="px-3 py-2">' . esc($recibo['usuario']) . '</td></tr>
          <tr><td class="px-3 py-2 text-slate-500">Servicio</td><td class="px-3 py-2">' . esc($recibo['servicio']) . '</td></tr>
          <tr><td class="px-3 py-2 text-slate-500">Descripción</td><td class="px-3 py-2">' . esc($recibo['descripcion'] ?: '—') . '</td></tr>
          <tr><td class="px-3 py-2 text-slate-500">Precio base</td><td class="px-3 py-2">Bs ' . number_format($recibo['precioBase'], 2) . '</td></tr>
          <tr><td class="px-3 py-2 text-slate-500">Descuento</td><td class="px-3 py-2 text-emerald-600 font-medium">- Bs ' . number_format($recibo['descuento'], 2) . '</td></tr>
          <tr><td class="px-3 py-2 text-slate-500 font-medium">Total</td><td class="px-3 py-2 text-indigo-700 font-bold">Bs ' . number_format($recibo['precioFinal'], 2) . '</td></tr>
        </table>
        ' . ($nota ? '<p class="text-sm text-indigo-800 italic mt-2">' . esc($nota) . '</p>' : '') . '
      </div>';
        }

        // ---- historial ----
        $filasHist = '';
        foreach ($historial as $h) {
            $filasHist .= '
      <tr class="hover:bg-slate-50">
        <td class="px-3 py-2 text-slate-400">#' . $h['id'] . '</td>
        <td class="px-3 py-2 font-medium text-slate-800">' . esc($h['usuario_nombre']) . '</td>
        <td class="px-3 py-2">' . esc($h['servicio_nombre']) . '</td>
        <td class="px-3 py-2">Bs ' . number_format((float)$h['precio_base'], 2) . '</td>
        <td class="px-3 py-2 text-emerald-600">- Bs ' . number_format((float)$h['descuento'], 2) . '</td>
        <td class="px-3 py-2 font-medium">Bs ' . number_format((float)$h['precio_final'], 2) . '</td>
      </tr>';
        }
        if ($filasHist === '') {
            $filasHist = '<tr><td colspan="6" class="px-3 py-6 text-center text-slate-400">Sin contrataciones registradas</td></tr>';
        }

        $tablaHistorial = '
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
              <th class="px-3 py-2 font-medium">#</th>
              <th class="px-3 py-2 font-medium">Cliente</th>
              <th class="px-3 py-2 font-medium">Servicio</th>
              <th class="px-3 py-2 font-medium">Base</th>
              <th class="px-3 py-2 font-medium">Descuento</th>
              <th class="px-3 py-2 font-medium">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">' . $filasHist . '</tbody>
        </table>
      </div>';

        $script = '
      <script>
      function servicioEnviar(accion) {
        document.getElementById("s-accion").value = accion;
        document.getElementById("form-servicio").submit();
      }
      document.querySelectorAll(".js-auto-preview").forEach(function (el) {
        el.addEventListener("change", function () { servicioEnviar("calcular"); });
      });
      </script>';

        $contenido = '
      <h1 class="text-xl font-bold text-slate-800 mb-6">Servicios Adicionales</h1>
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
        <div class="lg:col-span-1">' . $listaServicios . '</div>
        <div class="lg:col-span-3 space-y-6">' . $formulario . $panelRecibo . '</div>
      </div>
      <h2 class="text-lg font-semibold text-slate-800 mb-3">Historial de contrataciones</h2>
      ' . $tablaHistorial . $script;

        echo layout('Servicios', 'servicios', $contenido);
    }
}
