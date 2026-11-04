<?php
/**
 * Restablecimiento de contraseña después de verificar las preguntas.
 *
 * Flujo:
 * 1. Comprueba que exista una autorización temporal en la sesión.
 * 2. Valida la nueva contraseña y su confirmación.
 * 3. Guarda el nuevo hash en la tabla usuarios.
 * 4. Elimina la autorización para que no pueda reutilizarse.
 */

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/password_reset.php';

// Variables que utiliza la vista.
$error = '';
$exito = false;
$usuario = '';

// Recupera la autorización creada por recuperar.php.
$autorizacion = $_SESSION['recuperacion_autorizada'] ?? null;

if (!is_array($autorizacion)) {
    header('Location: recuperar.php');
    exit;
}

$expira = (int) ($autorizacion['expira'] ?? 0);
$usuarioId = (int) ($autorizacion['user_id'] ?? 0);

// La autorización vence para evitar que quede abierta indefinidamente.
if ($expira < time()) {
    unset($_SESSION['recuperacion_autorizada']);
    header('Location: recuperar.php?expirado=1', true, 303);
    exit;
}

$datosUsuario = obtenerUsuarioRecuperacion($conn, $usuarioId);

if (!$datosUsuario) {
    unset($_SESSION['recuperacion_autorizada']);
    header('Location: recuperar.php?expirado=1', true, 303);
    exit;
}

$usuario = $datosUsuario['username'];

// Procesa el formulario de nueva contraseña.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $confirmar = is_string($_POST['confirmar_password'] ?? null)
        ? $_POST['confirmar_password']
        : '';

    if ($password === '' || $confirmar === '') {
        $error = 'Por favor, complete ambos campos de contraseña.';
    } elseif ($password !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif (actualizarPasswordUsuario($conn, $usuarioId, $password)) {
        // La autorización se elimina después de guardar el nuevo hash.
        unset($_SESSION['recuperacion_autorizada']);

        // Renueva el identificador de sesión antes de mostrar el resultado.
        session_regenerate_id(true);

        $exito = true;
    } else {
        $error = 'No se pudo actualizar la contraseña. Intente de nuevo.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>SISCOIR - Restablecer contraseña</title>

    <!-- Estilos base, estilos del login y estilos propios de recuperación. -->
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/recovery.css">
</head>
<body>
    <!-- La vista muestra el formulario o el mensaje de éxito. -->
    <?php require __DIR__ . '/views/auth/restablecer.php'; ?>

    <?php if (!$exito): ?>
        <!-- Control visual para mostrar u ocultar las contraseñas. -->
        <script src="assets/js/login.js" defer></script>
    <?php endif; ?>
</body>
</html>
