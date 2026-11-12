<?php

class ReservaController {

    public array $tipos = [
        'estandar' => ReservaEstandar::class,
        'vip'      => ReservaVIP::class,
    ];

    public ClienteModel    $clienteModel;
    public HabitacionModel $habitacionModel;
    public ReservaModel    $reservaModel;
    public PagoModel       $pagoModel;

    public function __construct() {
        $this->clienteModel    = new ClienteModel();
        $this->habitacionModel = new HabitacionModel();
        $this->reservaModel    = new ReservaModel();
        $this->pagoModel       = new PagoModel();
    }

    public function handle(): void {
        header('Content-Type: application/json; charset=utf-8');
        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $action = $body['action'] ?? ($_GET['action'] ?? '');

        try {
            $result = match ($action) {
                'listar_habitaciones' => $this->habitacionModel->get(),
                'listar_clientes'     => $this->clienteModel->get(),
                'historial'           => $this->reservaModel->obtenerHistorial(),
                'calcular'            => $this->procesarConTemplate($body, preview: true),
                'reservar'            => $this->procesarConTemplate($body, preview: false),
                default               => throw new InvalidArgumentException("Acción desconocida: $action"),
            };

            echo json_encode(['ok' => true, 'data' => $result]);

        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    private function procesarConTemplate(array $body, bool $preview): array {
        $tipo         = $body['tipo']         ?? 'estandar';
        $clienteId    = (int)  ($body['clienteId']    ?? 0);
        $habitacionId = (int)  ($body['habitacionId'] ?? 0);
        $fechaIngreso = $body['fechaIngreso'] ?? '';
        $fechaSalida  = $body['fechaSalida']  ?? '';
        $metodoPago   = $body['metodoPago']   ?? 'efectivo';

        if (!isset($this->tipos[$tipo])) {
            throw new InvalidArgumentException("Tipo de reserva inválido: $tipo");
        }

        $cliente = $this->clienteModel->getById($clienteId);
        if (empty($cliente)) {
            throw new RuntimeException("Cliente #$clienteId no encontrado.");
        }

        $habitacion = $this->habitacionModel->getById($habitacionId);
        if (empty($habitacion)) {
            throw new RuntimeException("Habitación #$habitacionId no encontrada.");
        }

        $reserva   = new $this->tipos[$tipo]();
        $resultado = $reserva->templateMethod($cliente, $habitacion, $fechaIngreso, $fechaSalida);

        if (!$preview) {
            $reservaId = $this->reservaModel->create([
                ':cliente_id'    => $cliente['id'],
                ':habitacion_id' => $habitacion['id'],
                ':fecha_ingreso' => $fechaIngreso,
                ':fecha_salida'  => $fechaSalida,
                ':precio_base'   => $resultado['precioBase'],
                ':descuento'     => $resultado['descuento'],
                ':precio_final'  => $resultado['precioFinal'],
            ]);

            $this->habitacionModel->update($habitacion['id'], [
                ':numero'       => $habitacion['numero'],
                ':tipo'         => $habitacion['tipo'],
                ':precio_noche' => $habitacion['precio_noche'],
                ':disponible'   => 0,
            ]);

            $this->pagoModel->create([
                ':reserva_id'  => $reservaId,
                ':monto'       => $resultado['precioFinal'],
                ':metodo_pago' => $metodoPago,
                ':fecha_pago'  => date('Y-m-d'),
            ]);
        }

        $resultado['preview'] = $preview;

        return $resultado;
    }
}
