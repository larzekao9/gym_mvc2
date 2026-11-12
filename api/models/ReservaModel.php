<?php

class ReservaModel {
    public int    $id;
    public int    $clienteId;
    public int    $habitacionId;
    public string $fechaIngreso;
    public string $fechaSalida;
    public float  $precioBase;
    public float  $descuento;
    public float  $precioFinal;
    public string $createdAt;

    private PDO $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function get(): array {
        return $this->db->query('SELECT * FROM reservas ORDER BY created_at DESC')->fetchAll();
    }

    public function getById(int $id): array {
        $stmt = $this->db->prepare('SELECT * FROM reservas WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: [];
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO reservas
               (cliente_id, habitacion_id, fecha_ingreso, fecha_salida,
                precio_base, descuento, precio_final)
             VALUES
               (:cliente_id, :habitacion_id, :fecha_ingreso, :fecha_salida,
                :precio_base, :descuento, :precio_final)
             RETURNING id'
        );
        $stmt->execute($data);
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE reservas SET cliente_id=:cliente_id, habitacion_id=:habitacion_id,
             fecha_ingreso=:fecha_ingreso, fecha_salida=:fecha_salida,
             precio_base=:precio_base, descuento=:descuento, precio_final=:precio_final
             WHERE id=:id'
        );
        return $stmt->execute(array_merge($data, [':id' => $id]));
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM reservas WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function obtenerHistorial(int $limit = 20): array {
        $stmt = $this->db->prepare(
            'SELECT r.*, c.nombre AS cliente_nombre, h.numero AS habitacion_numero, h.tipo
             FROM reservas r
             JOIN clientes    c ON c.id = r.cliente_id
             JOIN habitaciones h ON h.id = r.habitacion_id
             ORDER BY r.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
