<?php
require_once __DIR__ . '/../Conexion.php';

class PagoModel
{
    public int    $id;
    public int    $usuarioId;
    public int    $planId;
    public float  $montoBase;
    public float  $descuento;
    public float  $montoFinal;
    public string $createdAt;

    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = Conexion::getConexion();
    }

    public function registrar(array $d): int
    {
        $stmt = $this->conexion->prepare(
            'INSERT INTO pago (usuario_id, plan_id, monto_base, descuento, monto_final)
             VALUES (:usuario_id, :plan_id, :monto_base, :descuento, :monto_final)
             RETURNING id'
        );
        $stmt->execute($d);
        return (int) $stmt->fetchColumn();
    }

    public function existePagoHoy(int $usuarioId, int $planId): bool
    {
        $stmt = $this->conexion->prepare(
            'SELECT COUNT(*) FROM pago
             WHERE usuario_id = :usuario_id AND plan_id = :plan_id AND created_at::date = CURRENT_DATE'
        );
        $stmt->execute(['usuario_id' => $usuarioId, 'plan_id' => $planId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function obtenerHistorial(int $limit = 20): array
    {
        $stmt = $this->conexion->prepare(
            'SELECT p.*, u.nombre AS usuario_nombre, pl.nombre AS plan_nombre, pl.tipo
             FROM pago p
             JOIN usuario u ON u.id = p.usuario_id
             JOIN plan_membresia pl ON pl.id = p.plan_id
             ORDER BY p.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
