<?php

class PagoAnual extends PagoTemplate {

    // 15% de descuento por fidelizar un año completo
    public function calcularDescuento(): float {
        return round($this->montoBase * 0.15, 2);
    }

    public function generarRecibo(): array {
        return [
            'tipo'            => 'Anual',
            'usuario'         => $this->usuario['nombre'],
            'plan'            => $this->plan['nombre'],
            'montoBase'       => $this->montoBase,
            'descuento'       => $this->descuento,
            'montoFinal'      => $this->montoFinal,
            'bonoPermanencia' => $this->bonoPermanencia,
            'nota'            => '15% de descuento por pago anual.',
        ];
    }
}
