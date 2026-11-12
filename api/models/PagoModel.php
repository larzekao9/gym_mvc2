<?php

class PagoModel {
    public int    $id;
    public int    $reservaId;
    public float  $monto;
    public string $metodoPago;
    public string $fechaPago;

    private PDO $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function get(): array {
        return $this->db->query('SELECT * FROM pagos ORDER BY fecha_pago DESC')->fetchAll();
    }

    public function getById(int $id): array {
        $stmt = $this->db->prepare('SELECT * FROM pagos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: [];
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO pagos (reserva_id, monto, metodo_pago, fecha_pago)
             VALUES (:reserva_id, :monto, :metodo_pago, :fecha_pago)
             RETURNING id'
        );
        $stmt->execute($data);
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE pagos SET reserva_id=:reserva_id, monto=:monto,
             metodo_pago=:metodo_pago, fecha_pago=:fecha_pago
             WHERE id=:id'
        );
        return $stmt->execute(array_merge($data, [':id' => $id]));
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM pagos WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
