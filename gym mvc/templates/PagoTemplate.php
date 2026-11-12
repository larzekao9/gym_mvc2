<?php

// =====================================================
// TEMPLATE METHOD - Pago de una membresía
// El algoritmo tiene 5 pasos y su orden es fijo:
//   1. validarPagoDuplicado()   -> común
//   2. calcularMontoBase()      -> común
//   3. aplicarBonoPermanencia() -> común, pero el templateMethod solo lo
//                                  ejecuta si el plan tiene permanencia
//                                  (más de 1 mes de compromiso)
//   4. calcularDescuento()      -> abstracto (cada plan el suyo)
//   5. generarRecibo()          -> abstracto (cada plan el suyo)
// =====================================================
abstract class PagoTemplate {

    // Premio fijo por comprometerse más de un mes (igual para todo plan
    // con permanencia; lo que varía por plan es el descuento del paso 4).
    const BONO_PERMANENCIA = 20.0;

    public array $usuario = [];
    public array $plan    = [];

    public bool $yaPagoHoy = false;

    public float $montoBase       = 0.0;
    public float $bonoPermanencia = 0.0;
    public float $descuento       = 0.0;
    public float $montoFinal      = 0.0;

    // ── Template Method — recibe los datos ya cargados por el Controller ──────
    final public function templateMethod(array $usuario, array $plan, bool $yaPagoHoy): array {
        $this->usuario   = $usuario;
        $this->plan      = $plan;
        $this->yaPagoHoy = $yaPagoHoy;

        $this->validarPagoDuplicado();
        $this->calcularMontoBase();

        // Solo los planes con permanencia (más de 1 mes) pasan por este paso;
        // el plan mensual se lo salta y no recibe el bono.
        if ((int) $this->plan['meses_compromiso'] > 1) {
            $this->aplicarBonoPermanencia();
        }

        $this->descuento  = $this->calcularDescuento();
        $this->montoFinal = round($this->montoBase - $this->descuento - $this->bonoPermanencia, 2);

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

    protected function aplicarBonoPermanencia(): void {
        $this->bonoPermanencia = self::BONO_PERMANENCIA;
    }

    // ── Pasos abstractos (obligatorios en subclases) ─────────────────────────
    abstract public function calcularDescuento(): float;
    abstract public function generarRecibo(): array;
}
