<?php

class AmenidadModel {
    public int    $id;
    public string $nombre;
    public float  $precio;
    public string $descripcion;

    private PDO $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function get(): array {
        return $this->db->query('SELECT * FROM amenidades ORDER BY nombre')->fetchAll();
    }

    public function getById(int $id): array {
        $stmt = $this->db->prepare('SELECT * FROM amenidades WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: [];
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO amenidades (nombre, precio, descripcion)
             VALUES (:nombre, :precio, :descripcion) RETURNING id'
        );
        $stmt->execute($data);
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE amenidades SET nombre=:nombre, precio=:precio, descripcion=:descripcion
             WHERE id=:id'
        );
        return $stmt->execute(array_merge($data, [':id' => $id]));
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM amenidades WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function obtenerPorNombre(string $nombre): array {
        $stmt = $this->db->prepare('SELECT * FROM amenidades WHERE nombre ILIKE :nombre');
        $stmt->execute([':nombre' => "%$nombre%"]);
        return $stmt->fetchAll();
    }
}
