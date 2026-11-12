<?php

class ContratacionModel {
    public int    $id;
    public int    $clienteId;
    public int    $amenidadId;
    public float  $precioBase;
    public float  $cargoExtra;
    public float  $precioFinal;
    public string $createdAt;

    private PDO $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function get(): array {
        return $this->db->query('SELECT * FROM contrataciones ORDER BY created_at DESC')->fetchAll();
    }

    public function getById(int $id): array {
        $stmt = $this->db->prepare('SELECT * FROM contrataciones WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: [];
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO contrataciones (cliente_id, amenidad_id, precio_base, cargo_extra, precio_final)
             VALUES (:cliente_id, :amenidad_id, :precio_base, :cargo_extra, :precio_final)
             RETURNING id'
        );
        $stmt->execute($data);
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE contrataciones SET cliente_id=:cliente_id, amenidad_id=:amenidad_id,
             precio_base=:precio_base, cargo_extra=:cargo_extra, precio_final=:precio_final
             WHERE id=:id'
        );
        return $stmt->execute(array_merge($data, [':id' => $id]));
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM contrataciones WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function obtenerHistorial(): array {
        return $this->db->query(
            'SELECT c.*, cl.nombre AS cliente_nombre, a.nombre AS amenidad_nombre
             FROM contrataciones c
             JOIN clientes   cl ON cl.id = c.cliente_id
             JOIN amenidades a  ON a.id  = c.amenidad_id
             ORDER BY c.created_at DESC
             LIMIT 50'
        )->fetchAll();
    }
}
