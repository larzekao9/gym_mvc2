<?php

class SocioCorporativo implements CategoriaSocioStrategy {

    // Convenio corporativo: 5% de descuento
    public function calcular(float $precio, array $servicio): float {
        return round($precio * 0.95, 2);
    }
}
