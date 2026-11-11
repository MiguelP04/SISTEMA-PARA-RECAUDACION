<?php

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/registros.php';

header('Content-Type: application/json; charset=utf-8');

if (!estaAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Sesión no válida.']);
    exit;
}

$tipo = trim((string) ($_GET['tipo'] ?? ''));
$termino = trim((string) ($_GET['q'] ?? ''));
$letra = trim((string) ($_GET['letra'] ?? ''));

if (!in_array($tipo, ['consignatario', 'agente'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo de contribuyente no válido.']);
    exit;
}

try {
    $resultados = buscarContribuyentesPlanilla($conn, $tipo, $termino, $letra);
    echo json_encode(['resultados' => $resultados], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo realizar la búsqueda.']);
}
