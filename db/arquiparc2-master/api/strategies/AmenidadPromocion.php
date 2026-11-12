<?php

class AmenidadPromocion implements AmenidadStrategy {

    // 30% de descuento por promocion especial
    public function calcular(float $precio, array $amenidad): float {
        return round($precio * 0.70, 2);
    }
}
