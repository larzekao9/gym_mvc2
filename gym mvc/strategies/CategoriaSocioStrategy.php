<?php

// =====================================================
// STRATEGY - ajuste de precio de un servicio adicional
// según la categoría del socio que lo contrata.
// Igual que AmenidadStrategy.
// =====================================================
interface CategoriaSocioStrategy {
    public function calcular(float $precio, array $servicio): float;
}
