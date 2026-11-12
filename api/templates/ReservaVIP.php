<?php

class ReservaVIP extends ReservaTemplate {

    // 20 % de descuento para clientes VIP
    public function calcularDescuento(): float {
        return round($this->precioBase * 0.20, 2);
    }

    public function generarComprobante(): array {
        return [
            'tipo'        => 'VIP',
            'cliente'     => $this->cliente['nombre'],
            'habitacion'  => $this->habitacion['numero'],
            'tipo_hab'    => $this->habitacion['tipo'],
            'ingreso'     => $this->fechaIngreso,
            'salida'      => $this->fechaSalida,
            'precioBase'  => $this->precioBase,
            'descuento'   => $this->descuento,
            'precioFinal' => $this->precioFinal,
            'beneficios'  => [
                'Desayuno y cena incluidos',
                'Acceso al spa',
                'Traslado al aeropuerto',
                'Late check-out sin cargo',
            ],
        ];
    }
}
