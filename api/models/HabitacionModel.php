<?php

class HabitacionModel {
    public int    $id;
    public string $numero;
    public string $tipo;
    public float  $precioNoche;
    public int    $disponible;

    private PDO $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function get(): array {
        return $this->db->query('SELECT * FROM habitaciones ORDER BY numero')->fetchAll();
    }

    public function getById(int $id): array {
        $stmt = $this->db->prepare('SELECT * FROM habitaciones WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: [];
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO habitaciones (numero, tipo, precio_noche, disponible)
             VALUES (:numero, :tipo, :precio_noche, :disponible)
             RETURNING id'
        );
        $stmt->execute($data);
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE habitaciones SET numero=:numero, tipo=:tipo,
             precio_noche=:precio_noche, disponible=:disponible
             WHERE id=:id'
        );
        return $stmt->execute(array_merge($data, [':id' => $id]));
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM habitaciones WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function hayDisponibles(): bool {
        $count = $this->db->query('SELECT COUNT(*) FROM habitaciones WHERE disponible = 1')->fetchColumn();
        return (int) $count > 0;
    }
}
