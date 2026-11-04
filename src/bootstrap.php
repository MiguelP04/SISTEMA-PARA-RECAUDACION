<?php
/**
 * Arranque común de SISCOIR.
 *
 * Este archivo debe cargarse antes de imprimir cualquier HTML y antes de
 * usar $_SESSION o $conn. Centraliza dos cosas que necesitan casi todas
 * las páginas:
 *
 * 1. La configuración y el inicio de la sesión.
 * 2. La conexión a la base de datos.
 *
 * Uso desde la raíz del proyecto:
 *   require __DIR__ . '/src/bootstrap.php';
 *
 * Al terminar, deja disponibles:
 * - $_SESSION: datos de la sesión actual.
 * - $conn: conexión mysqli a la base contabilidad.
 */

// Obliga a PHP a identificar la sesión únicamente mediante cookies.
// Así se evita que el ID de sesión viaje en la URL.
ini_set('session.use_only_cookies', '1');

// Rechaza IDs de sesión que PHP no haya creado previamente.
// Esto reduce el riesgo de reutilizar sesiones inventadas.
ini_set('session.use_strict_mode', '1');

// Mantiene los datos de la sesión en el servidor durante 8 horas.
// No cambia la duración de la cookie: esta sigue siendo de sesión.
ini_set('session.gc_maxlifetime', '28800');

// Nombre propio de la cookie para evitar colisiones con otras aplicaciones.
session_name('SISCOIR_SESSID');

// Configura la cookie antes de iniciar la sesión.
session_set_cookie_params([
    'lifetime' => 0,       // Se borra al cerrar el navegador.
    'path' => '/',         // Disponible en todo el sitio.
    'httponly' => true,    // JavaScript no puede leer la cookie.
    'samesite' => 'Lax',   // Reduce el envío de la cookie desde otros sitios.
]);

// Crea o reanuda la sesión actual.
// Debe ejecutarse antes de cualquier salida HTML.
session_start();

// Ruta del archivo que contiene las credenciales reales.
$archivoConfiguracion = __DIR__ . '/../config/database.php';

// Si el archivo no existe, se detiene la ejecución con una instrucción clara.
// Esto es útil al clonar el proyecto en otro equipo.
if (!is_file($archivoConfiguracion)) {
    http_response_code(500);
    exit('Falta config/database.php. Copia config/database.example.php y completa las credenciales.');
}

// Carga la configuración y guarda la conexión que retorna database.php.
$conn = require $archivoConfiguracion;
