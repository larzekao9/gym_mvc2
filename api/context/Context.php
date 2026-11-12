<?php

class ContratacionAmenidad {

    private AmenidadStrategy $strategy;

    public array $cliente  = [];
    public array $amenidad = [];

    public float $precioBase  = 0.0;
    public float $cargoExtra  = 0.0;
    public float $precioFinal = 0.0;

    // ── Setter de estrategia (intercambiable en tiempo de ejecucion) ──────────
    public function setStrategy(AmenidadStrategy $strategy): void {
        $this->strategy = $strategy;
    }

    // ── Metodo principal — recibe los datos ya cargados por el Controller ─────
    public function procesarContratacion(array $cliente, array $amenidad): array {
        $this->cliente  = $cliente;
        $this->amenidad = $amenidad;

        $this->precioBase  = (float) $this->amenidad['precio'];
        $this->precioFinal = $this->strategy->calcular($this->precioBase, $this->amenidad);
        $this->cargoExtra  = round($this->precioFinal - $this->precioBase, 2);

        return [
            'cliente'        => $this->cliente['nombre'],
            'amenidad'       => $this->amenidad['nombre'],
            'descripcion'    => $this->amenidad['descripcion'],
            'precioBase'     => $this->precioBase,
            'cargoExtra'     => $this->cargoExtra,
            'precioFinal'    => $this->precioFinal,
            'notaCargo'      => $this->cargoExtra > 0 ? 'Recargo aplicado: Bs ' . number_format($this->cargoExtra, 2) : '',
            'notaBeneficio'  => $this->cargoExtra < 0 ? 'Descuento aplicado: Bs ' . number_format(abs($this->cargoExtra), 2) : '',
        ];
    }
}
