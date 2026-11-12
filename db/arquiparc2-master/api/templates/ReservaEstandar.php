<?php

class ReservaEstandar extends ReservaTemplate {

    // 5 % de descuento en reservas estándar
    public function calcularDescuento(): float {
        return round($this->precioBase * 0.05, 2);
    }

    public function generarComprobante(): array {
        return [
            'tipo'        => 'Estándar',
            'cliente'     => $this->cliente['nombre'],
            'habitacion'  => $this->habitacion['numero'],
            'tipo_hab'    => $this->habitacion['tipo'],
            'ingreso'     => $this->fechaIngreso,
            'salida'      => $this->fechaSalida,
            'precioBase'  => $this->precioBase,
            'descuento'   => $this->descuento,
            'precioFinal' => $this->precioFinal,
            'beneficios'  => ['Desayuno incluido'],
        ];
    }
}
