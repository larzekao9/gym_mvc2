<?php

class SocioPromocion implements CategoriaSocioStrategy {

    // 20% de descuento por promoción vigente
    public function calcular(float $precio, array $servicio): float {
        return round($precio * 0.80, 2);
    }
}
