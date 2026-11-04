<?php
/**
 * Punto de entrada del sistema SISCOIR.
 *
 * Flujo:
 * 1. Carga la configuración común y las funciones de autenticación.
 * 2. Redirige al panel si el usuario ya tiene una sesión activa.
 * 3. Procesa el formulario cuando llega por POST.
 * 4. Muestra la vista del login con el mensaje correspondiente.
 */

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';

// Si ya existe una sesión válida, no se vuelve a mostrar el login.
if (estaAutenticado()) {
    header('Location: dashboard.php');
    exit;
}

// Variables que utiliza la vista para mostrar mensajes y conservar el usuario.
$error = '';
$usuario = '';

// Procesa únicamente las peticiones enviadas por el formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Se valida que los valores recibidos sean cadenas antes de usarlos.
    $usuario = is_string($_POST['usuario'] ?? null) ? trim($_POST['usuario']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    // Usuario y contraseña son obligatorios.
    if ($usuario === '' || $password === '') {
        $error = 'Por favor, ingrese usuario y contraseña.';
    } else {
        // Verifica las credenciales contra la tabla usuarios.
        $datosUsuario = autenticarUsuario($conn, $usuario, $password);

        if ($datosUsuario === false) {
            $error = 'Usuario o contraseña incorrectos.';
        } else {
            // Guarda los datos en la sesión y registra el último acceso.
            iniciarSesionUsuario($datosUsuario);
            actualizarUltimoAcceso($conn, $datosUsuario['id']);

            // Envía al panel principal después de un login correcto.
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>SISCOIR - Iniciar sesión</title>

    <!-- Estilos base y estilos exclusivos del login. -->
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <!-- La vista contiene el formulario y los textos institucionales. -->
    <?php require __DIR__ . '/views/auth/login.php'; ?>

    <!-- Control visual para mostrar u ocultar la contraseña. -->
    <script src="assets/js/login.js" defer></script>
</body>
</html>
