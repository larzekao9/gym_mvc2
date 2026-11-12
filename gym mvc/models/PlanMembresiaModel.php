<?php
require_once __DIR__ . '/../Conexion.php';

class PlanMembresiaModel
{
    public int    $id;
    public string $nombre;
    public string $tipo;
    public float  $precioBase;
    public int    $mesesCompromiso;

    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = Conexion::getConexion();
    }

    public function obtenerTodos(): array
    {
        return $this->conexion->query(
            'SELECT id, nombre, tipo, precio_base, meses_compromiso FROM plan_membresia ORDER BY id'
        )->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->conexion->prepare(
            'SELECT id, nombre, tipo, precio_base, meses_compromiso FROM plan_membresia WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
