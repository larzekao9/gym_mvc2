<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/DB.php';
require_once __DIR__ . '/models/ClienteModel.php';
require_once __DIR__ . '/models/HabitacionModel.php';
require_once __DIR__ . '/models/AmenidadModel.php';
require_once __DIR__ . '/models/ContratacionModel.php';
require_once __DIR__ . '/strategies/AmenidadStrategy.php';
require_once __DIR__ . '/strategies/AmenidadNormal.php';
require_once __DIR__ . '/strategies/AmenidadVIP.php';
require_once __DIR__ . '/strategies/AmenidadCorporativo.php';
require_once __DIR__ . '/strategies/AmenidadTemporadaAlta.php';
require_once __DIR__ . '/strategies/AmenidadPromocion.php';
require_once __DIR__ . '/context/Context.php';
require_once __DIR__ . '/controllers/ContratarAmenidadController.php';

(new ContratarAmenidadController())->handle();
