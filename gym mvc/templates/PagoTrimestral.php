<?php

class PagoTrimestral extends PagoTemplate {

    // 5% de descuento por pagar los 3 meses juntos
    public function calcularDescuento(): float {
        return round($this->montoBase * 0.05, 2);
    }

    public function generarRecibo(): array {
        return [
            'tipo'            => 'Trimestral',
            'usuario'         => $this->usuario['nombre'],
            'plan'            => $this->plan['nombre'],
            'montoBase'       => $this->montoBase,
            'descuento'       => $this->descuento,
            'montoFinal'      => $this->montoFinal,
            'bonoPermanencia' => $this->bonoPermanencia,
            'nota'            => '5% de descuento por pago trimestral adelantado.',
        ];
    }
}
