<?php

class SocioVIP implements CategoriaSocioStrategy {

    // 15% de descuento para socios VIP
    public function calcular(float $precio, array $servicio): float {
        return round($precio * 0.85, 2);
    }
}
