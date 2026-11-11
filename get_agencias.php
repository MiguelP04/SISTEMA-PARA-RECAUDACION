<?php

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/consultas.php';

header('Content-Type: application/json; charset=utf-8');

if (!estaAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado'], JSON_UNESCAPED_UNICODE);
    exit;
}

$codBanco = filter_input(INPUT_GET, 'cod_banco', FILTER_VALIDATE_INT);

if ($codBanco === null || $codBanco === false) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

$agencias = obtenerAgenciasConsulta($conn, $codBanco);
echo json_encode($agencias, JSON_UNESCAPED_UNICODE);
