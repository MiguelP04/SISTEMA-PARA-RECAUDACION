<?php
/**
 * Punto de entrada del sistema SISCOIR.
 *
 * Por ahora este archivo solo muestra el formulario de login.
 * Más adelante aquí se recibirá el formulario y se validarán
 * las credenciales contra la base de datos.
 */

session_name('SISCOIR_SESSID');
session_start();

/*
 * Variables que la vista puede utilizar para mostrar mensajes
 * o conservar el nombre de usuario escrito.
 */
$error = '';
$usuario = '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>SISCOIR - Iniciar sesión</title>

    <!-- Estilos base y estilos del login -->
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <?php require __DIR__ . '/views/auth/login.php'; ?>

    <!-- Funcionalidad para mostrar u ocultar la contraseña -->
    <script src="assets/js/login.js" defer></script>
</body>
</html>
