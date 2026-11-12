<?php

class AmenidadVIP implements AmenidadStrategy {

    // 15% de descuento para clientes VIP
    public function calcular(float $precio, array $amenidad): float {
        return round($precio * 0.85, 2);
    }
}
