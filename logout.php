<?php
/**
 * Cierre de sesión de SISCOIR.
 *
 * Flujo:
 * 1. Carga la configuración común y las funciones de autenticación.
 * 2. Elimina los datos y la cookie de la sesión.
 * 3. Redirige al formulario de login.
 */

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';

cerrarSesion();

header('Location: index.php');
exit;
