<?php

class AmenidadNormal implements AmenidadStrategy {

    public function calcular(float $precio, array $amenidad): float {
        return $precio;
    }
}
