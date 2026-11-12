<?php

class SocioEstudiante implements CategoriaSocioStrategy {

    // 10% de descuento para estudiantes
    public function calcular(float $precio, array $servicio): float {
        return round($precio * 0.90, 2);
    }
}
