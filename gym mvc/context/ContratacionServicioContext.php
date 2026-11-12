<?php

// =====================================================
// CONTEXT (Strategy) - igual que ContratacionAmenidad:
// guarda el estado (socio, servicio, precioBase/descuento/
// precioFinal) y delega el cómo se ajusta el precio a la
// estrategia activa.
// =====================================================
class ContratacionServicioContext {

    private CategoriaSocioStrategy $strategy;

    public array $usuario  = [];
    public array $servicio = [];

    public float $precioBase  = 0.0;
    public float $descuento   = 0.0;
    public float $precioFinal = 0.0;

    // ── Setter de estrategia (intercambiable en tiempo de ejecucion) ──────────
    public function setStrategy(CategoriaSocioStrategy $strategy): void {
        $this->strategy = $strategy;
    }

    // ── Metodo principal — recibe los datos ya cargados por el Controller ─────
    public function procesarContratacion(array $usuario, array $servicio): array {
        $this->usuario  = $usuario;
        $this->servicio = $servicio;

        $this->precioBase  = (float) $this->servicio['precio'];
        $this->precioFinal = $this->strategy->calcular($this->precioBase, $this->servicio);
        $this->descuento   = round($this->precioBase - $this->precioFinal, 2);

        return [
            'usuario'     => $this->usuario['nombre'],
            'servicio'    => $this->servicio['nombre'],
            'descripcion' => $this->servicio['descripcion'],
            'precioBase'  => $this->precioBase,
            'descuento'   => $this->descuento,
            'precioFinal' => $this->precioFinal,
            'nota'        => $this->descuento > 0 ? 'Descuento aplicado: Bs ' . number_format($this->descuento, 2) : '',
        ];
    }
}
