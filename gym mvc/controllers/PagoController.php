<?php
// =====================================================
// CONTROLADOR - Pago de Membresía
// Misma forma que ReservaController: constructor, handle()
// y procesarConTemplate(). El descuento lo calcula el Template
// Method según el tipo de plan.
// =====================================================
class PagoController {

    public array $tipos = [
        'mensual'    => PagoMensual::class,
        'trimestral' => PagoTrimestral::class,
        'anual'      => PagoAnual::class,
    ];

    public UsuarioModel $usuarioModel;
    public PlanMembresiaModel $planModel;
    public PagoModel $pagoModel;
    public PagoView $vista;

    public function __construct(
        UsuarioModel $usuarioModel,
        PlanMembresiaModel $planModel,
        PagoModel $pagoModel,
        PagoView $vista
    ) {
        $this->usuarioModel = $usuarioModel;
        $this->planModel = $planModel;
        $this->pagoModel = $pagoModel;
        $this->vista = $vista;
    }

    public function handle(string $metodo, array $datos): void {
        $recibo = null;
        $error = null;

        if ($metodo === 'POST') {
            $preview = ($datos['accion'] ?? 'calcular') !== 'pagar';

            // La vista previa se dispara sola apenas se toca un campo: mientras
            // falte elegir cliente o plan todavía no hay nada que calcular.
            $faltanDatos = empty($datos['usuario_id']) || empty($datos['plan_id']);

            if ($faltanDatos) {
                if (!$preview) {
                    $error = 'Elegí un cliente y un plan antes de confirmar el pago.';
                }
            } else {
                try {
                    $recibo = $this->procesarConTemplate($datos, $preview);
                } catch (Throwable $e) {
                    $error = $e->getMessage();
                }
            }
        }

        $clientes = array_values(array_filter(
            $this->usuarioModel->obtenerTodos(),
            fn($u) => $u['rol'] === 'cliente'
        ));

        $this->vista->render($clientes, $this->planModel->obtenerTodos(), $this->pagoModel->obtenerHistorial(), $recibo, [
            'error' => $error,
            'usuarioIdSeleccionado' => (int) ($datos['usuario_id'] ?? 0),
            'planIdSeleccionado' => (int) ($datos['plan_id'] ?? 0),
        ]);
    }

    private function procesarConTemplate(array $datos, bool $preview): array {
        $usuarioId = (int) ($datos['usuario_id'] ?? 0);
        $planId = (int) ($datos['plan_id'] ?? 0);

        $usuario = $this->usuarioModel->obtenerPorId($usuarioId);
        if (empty($usuario)) {
            throw new RuntimeException("Cliente #$usuarioId no encontrado.");
        }

        $plan = $this->planModel->buscarPorId($planId);
        if (empty($plan)) {
            throw new RuntimeException("Plan #$planId no encontrado.");
        }

        $tipo = $plan['tipo'];
        if (!isset($this->tipos[$tipo])) {
            throw new InvalidArgumentException("Tipo de plan inválido: $tipo");
        }

        $yaPagoHoy = $this->pagoModel->existePagoHoy($usuario['id'], $plan['id']);

        $pago = new $this->tipos[$tipo]();
        $resultado = $pago->templateMethod($usuario, $plan, $yaPagoHoy);

        if (!$preview) {
            $this->pagoModel->registrar([
                'usuario_id'       => $usuario['id'],
                'plan_id'          => $plan['id'],
                'monto_base'       => $resultado['montoBase'],
                'descuento'        => $resultado['descuento'],
                'bono_permanencia' => $resultado['bonoPermanencia'],
                'monto_final'      => $resultado['montoFinal'],
            ]);
        }

        $resultado['preview'] = $preview;

        return $resultado;
    }
}
