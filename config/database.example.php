<?php
/**
 * Plantilla de conexión a la base de datos de SISCOIR.
 *
 * Este archivo sí se sube al repositorio y sirve como guía para configurar
 * un entorno nuevo. No contiene credenciales reales.
 *
 * Uso desde src/bootstrap.php:
 *   $conn = require __DIR__ . '/../config/database.php';
 *
 * Pasos:
 * 1. Copiar este archivo como config/database.php.
 * 2. Reemplazar TU_USUARIO y TU_CONTRASENA por los datos locales.
 * 3. Mantener el nombre de la base en contabilidad.
 */

// Datos de conexión. Se usa 127.0.0.1 para forzar TCP y evitar
// diferencias entre el socket de XAMPP y el PHP de la terminal.
$host = '127.0.0.1';
$usuario = 'TU_USUARIO';
$password = 'TU_CONTRASENA';
$baseDatos = 'contabilidad';

// Activa las excepciones de mysqli para capturar los errores con try/catch.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Crea la conexión con el servidor y selecciona la base de datos.
    $conn = new mysqli($host, $usuario, $password, $baseDatos);

    // Usa el mismo juego de caracteres que la base actual.
    $conn->set_charset('utf8');
} catch (mysqli_sql_exception $error) {
    // El detalle técnico queda en el log del servidor.
    error_log('Error de conexión a la base de datos: ' . $error->getMessage());

    // El usuario solo recibe un mensaje genérico.
    http_response_code(500);
    exit('No se pudo conectar con la base de datos.');
}

// Devuelve la conexión al archivo que cargue esta plantilla.
return $conn;
