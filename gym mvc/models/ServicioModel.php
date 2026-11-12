<?php
require_once __DIR__ . '/../Conexion.php';

class ServicioModel
{
    public int    $id;
    public string $nombre;
    public string $descripcion;
    public float  $precio;

    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = Conexion::getConexion();
    }

    public function obtenerTodos(): array
    {
        return $this->conexion->query(
            'SELECT id, nombre, descripcion, precio FROM servicio_adicional ORDER BY id'
        )->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->conexion->prepare(
            'SELECT id, nombre, descripcion, precio FROM servicio_adicional WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
