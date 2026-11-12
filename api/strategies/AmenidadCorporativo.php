<?php

class AmenidadCorporativo implements AmenidadStrategy {

    // 20% de descuento para clientes corporativos
    public function calcular(float $precio, array $amenidad): float {
        return round($precio * 0.80, 2);
    }
}
