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
require_once __DIR__ . '/models/ReservaModel.php';
require_once __DIR__ . '/models/PagoModel.php';
require_once __DIR__ . '/templates/ReservaTemplate.php';
require_once __DIR__ . '/templates/ReservaEstandar.php';
require_once __DIR__ . '/templates/ReservaVIP.php';
require_once __DIR__ . '/controllers/ReservaController.php';

(new ReservaController())->handle();
