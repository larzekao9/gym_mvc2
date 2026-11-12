<?php

class AmenidadTemporadaAlta implements AmenidadStrategy {

    // 25% de recargo por temporada alta
    public function calcular(float $precio, array $amenidad): float {
        return round($precio * 1.25, 2);
    }
}
