<?php
/**
 * Plantilla de conexión a la base de datos de SISCOIR.
 *
 * Copia este archivo como config/database.php y completa los valores
 * según el entorno local.
 */

$host = '127.0.0.1';
$usuario = 'TU_USUARIO';
$password = 'TU_CONTRASENA';
$baseDatos = 'contabilidad';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $usuario, $password, $baseDatos);
    $conn->set_charset('utf8');
} catch (mysqli_sql_exception $error) {
    error_log('Error de conexión a la base de datos: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudo conectar con la base de datos.');
}

return $conn;
