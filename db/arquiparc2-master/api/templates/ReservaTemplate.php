<?php

abstract class ReservaTemplate {

    public array $cliente    = [];
    public array $habitacion = [];

    public string $fechaIngreso = '';
    public string $fechaSalida  = '';

    public float $precioBase  = 0.0;
    public float $descuento   = 0.0;
    public float $precioFinal = 0.0;

    // ── Template Method — recibe los datos ya cargados por el Controller ──────
    final public function templateMethod(
        array  $cliente,
        array  $habitacion,
        string $fechaIngreso,
        string $fechaSalida
    ): array {
        $this->cliente      = $cliente;
        $this->habitacion   = $habitacion;
        $this->fechaIngreso = $fechaIngreso;
        $this->fechaSalida  = $fechaSalida;

        $this->validarDisponibilidad();
        $this->calcularPrecioBase();
        $this->descuento   = $this->calcularDescuento();
        $this->precioFinal = round($this->precioBase - $this->descuento, 2);

        return $this->generarComprobante();
    }

    // ── Pasos del algoritmo ────────────────────────────────────────────────────
    protected function validarDisponibilidad(): void {
        if ((int) $this->habitacion['disponible'] === 0) {
            throw new RuntimeException("La habitación {$this->habitacion['numero']} no está disponible.");
        }
    }

    protected function calcularPrecioBase(): void {
        $ingreso = new DateTime($this->fechaIngreso);
        $salida  = new DateTime($this->fechaSalida);
        $dias    = (int) $ingreso->diff($salida)->days;
        if ($dias <= 0) {
            throw new RuntimeException('La fecha de salida debe ser posterior a la de ingreso.');
        }
        $this->precioBase = round($dias * (float) $this->habitacion['precio_noche'], 2);
    }

    // ── Pasos abstractos (obligatorios en subclases) ─────────────────────────
    abstract public function calcularDescuento(): float;
    abstract public function generarComprobante(): array;
}
