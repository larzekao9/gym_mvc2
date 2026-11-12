<?php

// =====================================================
// TEMPLATE METHOD - Pago de una membresía
// El algoritmo tiene 5 pasos y su orden es fijo:
//   1. validarPagoDuplicado()        -> común
//   2. calcularMontoBase()           -> común
//   3. aplicarBeneficioPermanencia() -> común, pero el templateMethod
//                                       solo lo llama si el plan tiene
//                                       permanencia > 1 mes
//   4. calcularDescuento()           -> abstracto (cada plan el suyo)
//   5. generarRecibo()               -> abstracto (cada plan el suyo)
// =====================================================
abstract class PagoTemplate {

    public array $usuario = [];
    public array $plan    = [];

    public bool $yaPagoHoy = false;

    public float  $montoBase  = 0.0;
    public string $beneficio  = '';
    public float  $descuento  = 0.0;
    public float  $montoFinal = 0.0;

    // ── Template Method — recibe los datos ya cargados por el Controller ──────
    final public function templateMethod(array $usuario, array $plan, bool $yaPagoHoy): array {
        $this->usuario   = $usuario;
        $this->plan      = $plan;
        $this->yaPagoHoy = $yaPagoHoy;

        $this->validarPagoDuplicado();
        $this->calcularMontoBase();

        // Solo los planes con permanencia (más de 1 mes) pasan por este paso;
        // el plan mensual se lo salta.
        if ((int) $this->plan['meses_compromiso'] > 1) {
            $this->aplicarBeneficioPermanencia();
        }

        $this->descuento  = $this->calcularDescuento();
        $this->montoFinal = round($this->montoBase - $this->descuento, 2);

        return $this->generarRecibo();
    }

    // ── Pasos comunes (iguales para todos los planes) ─────────────────────────
    protected function validarPagoDuplicado(): void {
        if ($this->yaPagoHoy) {
            throw new RuntimeException("{$this->usuario['nombre']} ya pagó el plan {$this->plan['nombre']} hoy.");
        }
    }

    protected function calcularMontoBase(): void {
        $this->montoBase = round((float) $this->plan['precio_base'], 2);
    }

    protected function aplicarBeneficioPermanencia(): void {
        $this->beneficio = 'Congelamiento de membresía hasta 15 días';
    }

    // ── Pasos abstractos (obligatorios en subclases) ─────────────────────────
    abstract public function calcularDescuento(): float;
    abstract public function generarRecibo(): array;
}
