<?php

class ClienteModel {
    public int    $id;
    public string $nombre;
    public string $telefono;
    public string $correo;

    private PDO $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function get(): array {
        return $this->db->query('SELECT * FROM clientes ORDER BY nombre')->fetchAll();
    }

    public function getById(int $id): array {
        $stmt = $this->db->prepare('SELECT * FROM clientes WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: [];
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO clientes (nombre, telefono, correo)
             VALUES (:nombre, :telefono, :correo)
             RETURNING id'
        );
        $stmt->execute($data);
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE clientes SET nombre=:nombre, telefono=:telefono, correo=:correo
             WHERE id=:id'
        );
        return $stmt->execute(array_merge($data, [':id' => $id]));
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM clientes WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
