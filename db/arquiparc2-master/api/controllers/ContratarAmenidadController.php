<?php

class ContratarAmenidadController {

    public array $strategies = [
        'normal'         => AmenidadNormal::class,
        'vip'            => AmenidadVIP::class,
        'corporativo'    => AmenidadCorporativo::class,
        'temporada_alta' => AmenidadTemporadaAlta::class,
        'promocion'      => AmenidadPromocion::class,
    ];

    public ClienteModel      $clienteModel;
    public AmenidadModel     $amenidadModel;
    public ContratacionModel $contratacionModel;

    public function __construct() {
        $this->clienteModel      = new ClienteModel();
        $this->amenidadModel     = new AmenidadModel();
        $this->contratacionModel = new ContratacionModel();
    }

    public function handle(): void {
        header('Content-Type: application/json; charset=utf-8');
        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $action = $body['action'] ?? ($_GET['action'] ?? '');

        try {
            $result = match ($action) {
                'listar_clientes'   => $this->clienteModel->get(),
                'listar_amenidades' => $this->amenidadModel->get(),
                'historial'         => $this->contratacionModel->obtenerHistorial(),
                'calcular'          => $this->procesarConEstrategia($body, preview: true),
                'contratar'         => $this->procesarConEstrategia($body, preview: false),
                default             => throw new InvalidArgumentException("Accion desconocida: $action"),
            };

            echo json_encode(['ok' => true, 'data' => $result]);

        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    private function procesarConEstrategia(array $body, bool $preview): array {
        $tipo       = $body['estrategia']  ?? 'normal';
        $clienteId  = (int) ($body['clienteId']  ?? 0);
        $amenidadId = (int) ($body['amenidadId'] ?? 0);

        if (!isset($this->strategies[$tipo])) {
            throw new InvalidArgumentException("Estrategia invalida: $tipo");
        }

        $cliente = $this->clienteModel->getById($clienteId);
        if (empty($cliente)) {
            throw new RuntimeException("Cliente #$clienteId no encontrado.");
        }

        $amenidad = $this->amenidadModel->getById($amenidadId);
        if (empty($amenidad)) {
            throw new RuntimeException("Amenidad #$amenidadId no encontrada.");
        }

        $contexto = new ContratacionAmenidad();
        $contexto->setStrategy(new $this->strategies[$tipo]());

        $resultado = $contexto->procesarContratacion($cliente, $amenidad);

        if (!$preview) {
            $this->contratacionModel->create([
                ':cliente_id'   => $cliente['id'],
                ':amenidad_id'  => $amenidad['id'],
                ':precio_base'  => $resultado['precioBase'],
                ':cargo_extra'  => $resultado['cargoExtra'],
                ':precio_final' => $resultado['precioFinal'],
            ]);
        }

        $resultado['preview'] = $preview;

        return $resultado;
    }
}
