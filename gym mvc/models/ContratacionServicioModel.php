<?php
require_once __DIR__ . '/../Conexion.php';

class ContratacionServicioModel
{
    public int    $id;
    public int    $usuarioId;
    public int    $servicioId;
    public string $categoriaSocio;
    public float  $precioBase;
    public float  $descuento;
    public float  $precioFinal;
    public string $createdAt;

    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = Conexion::getConexion();
    }

    public function registrar(array $d): int
    {
        $stmt = $this->conexion->prepare(
            'INSERT INTO contratacion_servicio (usuario_id, servicio_id, categoria_socio, precio_base, descuento, precio_final)
             VALUES (:usuario_id, :servicio_id, :categoria_socio, :precio_base, :descuento, :precio_final)
             RETURNING id'
        );
        $stmt->execute($d);
        return (int) $stmt->fetchColumn();
    }

    public function obtenerHistorial(int $limit = 20): array
    {
        $stmt = $this->conexion->prepare(
            'SELECT c.*, u.nombre AS usuario_nombre, s.nombre AS servicio_nombre
             FROM contratacion_servicio c
             JOIN usuario u ON u.id = c.usuario_id
             JOIN servicio_adicional s ON s.id = c.servicio_id
             ORDER BY c.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
