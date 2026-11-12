<?php

interface AmenidadStrategy {
    public function calcular(float $precio, array $amenidad): float;
}
