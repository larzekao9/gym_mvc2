<?php

class SocioNormal implements CategoriaSocioStrategy {

    public function calcular(float $precio, array $servicio): float {
        return $precio;
    }
}
