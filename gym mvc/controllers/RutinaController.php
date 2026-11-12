<?php
// =====================================================
// CONTROLADOR - Rutina (maestro) + RutinaEjercicio (detalle)
// Una sola pantalla: alta/edición/borrado de la rutina (cabecera)
// y asignación de ejercicios a la rutina elegida (detalle),
// igual que un Pedido con su Detalle de pedido: se crean juntos.
// =====================================================
class RutinaController
{
    private RutinaModel $modeloRutina;
    private EjercicioModel $modeloEjercicio;
    private RutinaEjercicioModel $modeloRutinaEj;
    private RutinaView $vista;

    public function __construct(
        RutinaModel $modeloRutina,
        EjercicioModel $modeloEjercicio,
        RutinaEjercicioModel $modeloRutinaEj,
        RutinaView $vista
    ) {
        $this->modeloRutina = $modeloRutina;
        $this->modeloEjercicio = $modeloEjercicio;
        $this->modeloRutinaEj = $modeloRutinaEj;
        $this->vista = $vista;
    }

    public function obtenerTodos(): void
    {
        $rutinas = $this->modeloRutina->obtenerTodos();
        $rutinaId = $rutinas[0]['id'] ?? null;
        $this->renderizar($rutinaId ? (int)$rutinaId : null, false);
    }

    public function obtenerPorId(int $id): void
    {
        $this->renderizar($id, false);
    }

    public function nueva(): void
    {
        $this->renderizar(null, true);
    }

    // Alta de la rutina (cabecera) + su detalle de ejercicios, ambos
    // en un mismo paso: nunca se crea un "pedido" sin su "detalle".
    public function crear(array $datos): void
    {
        $id = $this->modeloRutina->registrar($datos);

        foreach ($datos['ejercicios'] ?? [] as $ej) {
            if (empty($ej['ejercicio_id']) || $ej['series'] === '' || $ej['repeticiones'] === '') {
                continue;
            }
            $this->modeloRutinaEj->agregar([
                'rutina_id' => $id,
                'ejercicio_id' => $ej['ejercicio_id'],
                'series' => $ej['series'],
                'repeticiones' => $ej['repeticiones'],
                'peso_sugerido' => $ej['peso_sugerido'] ?? '',
                'orden' => $ej['orden'] ?? 1,
                'notas' => $ej['notas'] ?? '',
            ]);
        }

        $this->renderizar($id, false);
    }

    public function actualizar(int $id, array $datos): void
    {
        $this->modeloRutina->actualizar($id, $datos);
        $this->renderizar($id, false);
    }

    public function eliminar(int $id): void
    {
        $this->modeloRutina->eliminar($id);
        $rutinas = $this->modeloRutina->obtenerTodos();
        $siguienteId = $rutinas[0]['id'] ?? null;
        $this->renderizar($siguienteId ? (int)$siguienteId : null, false);
    }

    public function agregarEjercicio(array $datos): void
    {
        $this->modeloRutinaEj->agregar($datos);
        $this->renderizar((int)$datos['rutina_id'], false);
    }

    public function eliminarEjercicio(int $rutinaId, int $ejercicioId): void
    {
        $this->modeloRutinaEj->eliminar($rutinaId, $ejercicioId);
        $this->renderizar($rutinaId, false);
    }

    private function renderizar(?int $rutinaId, bool $formularioNuevo): void
    {
        $rutinas = $this->modeloRutina->obtenerTodos();
        $rutina = $rutinaId ? $this->modeloRutina->buscarPorId($rutinaId) : null;
        $ejercicios = $this->modeloEjercicio->obtenerTodos();
        $asignados = $rutina ? $this->modeloRutinaEj->obtenerPorRutina((int)$rutina['id']) : [];

        $this->vista->render($rutina, $rutinas, $ejercicios, $asignados, [
            'formularioNuevo' => $formularioNuevo || !$rutina,
        ]);
    }
}
