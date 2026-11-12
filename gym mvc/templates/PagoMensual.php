<?php

class PagoMensual extends PagoTemplate {

    // Sin descuento: plan mes a mes, al precio de lista
    public function calcularDescuento(): float {
        return 0.0;
    }

    public function generarRecibo(): array {
        return [
            'tipo'            => 'Mensual',
            'usuario'         => $this->usuario['nombre'],
            'plan'            => $this->plan['nombre'],
            'montoBase'       => $this->montoBase,
            'descuento'       => $this->descuento,
            'montoFinal'      => $this->montoFinal,
            'bonoPermanencia' => $this->bonoPermanencia,
            'nota'            => 'Plan mes a mes, sin descuentos ni permanencia.',
        ];
    }
}
