<?php
// =====================================================
// CONTROLADOR - Contratar Servicio Adicional
// Misma forma que ContratarAmenidadController: constructor,
// handle() y procesarConEstrategia(). El ajuste de precio lo
// hace la Strategy elegida según la categoría del socio.
// =====================================================
class ServicioController {

    public array $strategies = [
        'normal'      => SocioNormal::class,
        'vip'         => SocioVIP::class,
        'estudiante'  => SocioEstudiante::class,
        'corporativo' => SocioCorporativo::class,
        'promocion'   => SocioPromocion::class,
    ];

    public UsuarioModel $usuarioModel;
    public ServicioModel $servicioModel;
    public ContratacionServicioModel $contratacionModel;
    public ServicioView $vista;

    public function __construct(
        UsuarioModel $usuarioModel,
        ServicioModel $servicioModel,
        ContratacionServicioModel $contratacionModel,
        ServicioView $vista
    ) {
        $this->usuarioModel = $usuarioModel;
        $this->servicioModel = $servicioModel;
        $this->contratacionModel = $contratacionModel;
        $this->vista = $vista;
    }

    public function handle(string $metodo, array $datos): void {
        $recibo = null;
        $error = null;

        if ($metodo === 'POST') {
            $preview = ($datos['accion'] ?? 'calcular') !== 'contratar';

            // La vista previa se dispara sola apenas se toca un campo: mientras
            // falte elegir cliente o servicio todavía no hay nada que calcular.
            $faltanDatos = empty($datos['usuario_id']) || empty($datos['servicio_id']);

            if ($faltanDatos) {
                if (!$preview) {
                    $error = 'Elegí un cliente y un servicio antes de contratar.';
                }
            } else {
                try {
                    $recibo = $this->procesarConEstrategia($datos, $preview);
                } catch (Throwable $e) {
                    $error = $e->getMessage();
                }
            }
        }

        $clientes = array_values(array_filter(
            $this->usuarioModel->obtenerTodos(),
            fn($u) => $u['rol'] === 'cliente'
        ));

        $this->vista->render($clientes, $this->servicioModel->obtenerTodos(), $this->contratacionModel->obtenerHistorial(), $recibo, [
            'error' => $error,
            'usuarioIdSeleccionado' => (int) ($datos['usuario_id'] ?? 0),
            'servicioIdSeleccionado' => (int) ($datos['servicio_id'] ?? 0),
            'categoriaSeleccionada' => $datos['categoria'] ?? 'normal',
        ]);
    }

    private function procesarConEstrategia(array $datos, bool $preview): array {
        $categoria = $datos['categoria'] ?? 'normal';
        $usuarioId = (int) ($datos['usuario_id'] ?? 0);
        $servicioId = (int) ($datos['servicio_id'] ?? 0);

        if (!isset($this->strategies[$categoria])) {
            throw new InvalidArgumentException("Categoría de socio inválida: $categoria");
        }

        $usuario = $this->usuarioModel->obtenerPorId($usuarioId);
        if (empty($usuario)) {
            throw new RuntimeException("Cliente #$usuarioId no encontrado.");
        }

        $servicio = $this->servicioModel->buscarPorId($servicioId);
        if (empty($servicio)) {
            throw new RuntimeException("Servicio #$servicioId no encontrado.");
        }

        $contexto = new ContratacionServicioContext();
        $contexto->setStrategy(new $this->strategies[$categoria]());

        $resultado = $contexto->procesarContratacion($usuario, $servicio);

        if (!$preview) {
            $this->contratacionModel->registrar([
                'usuario_id'      => $usuario['id'],
                'servicio_id'     => $servicio['id'],
                'categoria_socio' => $categoria,
                'precio_base'     => $resultado['precioBase'],
                'descuento'       => $resultado['descuento'],
                'precio_final'    => $resultado['precioFinal'],
            ]);
        }

        $resultado['preview'] = $preview;
        $resultado['categoria'] = $categoria;

        return $resultado;
    }
}
