<?php
/**
 * Recuperación de contraseña: identificación y preguntas de seguridad.
 *
 * Flujo:
 * 1. El usuario escribe su nombre de usuario.
 * 2. Se comprueba que la cuenta exista, esté activa y tenga preguntas.
 * 3. Se guarda una autorización temporal en la sesión.
 * 4. El usuario responde sus dos preguntas de seguridad.
 * 5. Si las respuestas son correctas, se redirige a restablecer.php.
 */

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/password_reset.php';

// Un usuario que ya inició sesión no necesita recuperar su contraseña.
if (estaAutenticado()) {
    header('Location: dashboard.php');
    exit;
}

// Variables que utiliza la vista.
$paso = 'usuario';
$error = '';
$usuario = '';
$preguntas = [];
$aviso = '';

// Permite abandonar una recuperación iniciada y volver al primer paso.
if (isset($_GET['reiniciar'])) {
    unset($_SESSION['recuperacion'], $_SESSION['recuperacion_autorizada']);
    header('Location: recuperar.php');
    exit;
}

// Mensaje que se muestra cuando una autorización anterior venció.
if (isset($_GET['expirado'])) {
    $aviso = 'La solicitud de recuperación expiró. Ingrese su usuario nuevamente.';
}

// Reanuda el paso de preguntas si existe una recuperación vigente.
$recuperacion = $_SESSION['recuperacion'] ?? null;

if (is_array($recuperacion)) {
    $expira = (int) ($recuperacion['expira'] ?? 0);
    $usuarioId = (int) ($recuperacion['user_id'] ?? 0);

    if ($expira < time()) {
        unset($_SESSION['recuperacion']);
        $aviso = 'La solicitud de recuperación expiró. Ingrese su usuario nuevamente.';
    } else {
        $datosUsuario = obtenerUsuarioRecuperacion($conn, $usuarioId);

        if (!$datosUsuario || !tienePreguntasSeguridad($datosUsuario)) {
            unset($_SESSION['recuperacion']);
            $error = 'No se pudieron cargar las preguntas de seguridad. Intente de nuevo.';
        } else {
            $paso = 'preguntas';
            $usuario = $datosUsuario['username'];
            $preguntas = [
                [
                    'campo' => 'pregunta1',
                    'texto' => $datosUsuario['pregunta1'],
                ],
                [
                    'campo' => 'pregunta2',
                    'texto' => $datosUsuario['pregunta2'],
                ],
            ];
        }
    }
}

// Procesa los formularios de identificación y de respuestas.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = is_string($_POST['accion'] ?? null) ? $_POST['accion'] : '';

    if ($accion === 'identificar') {
        // El primer paso siempre reinicia cualquier recuperación anterior.
        $paso = 'usuario';
        unset($_SESSION['recuperacion'], $_SESSION['recuperacion_autorizada']);

        $usuario = is_string($_POST['usuario'] ?? null)
            ? trim($_POST['usuario'])
            : '';

        if ($usuario === '') {
            $error = 'Por favor, ingrese su nombre de usuario.';
        } else {
            $datosUsuario = buscarUsuarioRecuperacion($conn, $usuario);

            if (!$datosUsuario) {
                $error = 'Usuario no encontrado.';
            } elseif (!tienePreguntasSeguridad($datosUsuario)) {
                $error = 'El usuario no tiene preguntas de seguridad configuradas. Contacte al administrador.';
            } else {
                // Guarda el usuario y una vigencia de diez minutos.
                $_SESSION['recuperacion'] = [
                    'user_id' => $datosUsuario['id'],
                    'expira' => time() + 600,
                ];

                // Cambia el identificador antes de continuar con las preguntas.
                session_regenerate_id(true);

                header('Location: recuperar.php', true, 303);
                exit;
            }
        }
    } elseif ($accion === 'verificar') {
        $datosUsuario = null;

        if (is_array($recuperacion)) {
            $expira = (int) ($recuperacion['expira'] ?? 0);
            $usuarioId = (int) ($recuperacion['user_id'] ?? 0);

            if ($expira >= time()) {
                $datosUsuario = obtenerUsuarioRecuperacion($conn, $usuarioId);
            }
        }

        if (!$datosUsuario || !tienePreguntasSeguridad($datosUsuario)) {
            unset($_SESSION['recuperacion']);
            $paso = 'usuario';
            $error = 'La solicitud de recuperación expiró. Ingrese su usuario nuevamente.';
        } else {
            $paso = 'preguntas';
            $usuario = $datosUsuario['username'];
            $preguntas = [
                [
                    'campo' => 'pregunta1',
                    'texto' => $datosUsuario['pregunta1'],
                ],
                [
                    'campo' => 'pregunta2',
                    'texto' => $datosUsuario['pregunta2'],
                ],
            ];

            $respuestas = $_POST['respuestas'] ?? [];
            $respuesta1 = is_array($respuestas) && is_string($respuestas['pregunta1'] ?? null)
                ? trim($respuestas['pregunta1'])
                : '';
            $respuesta2 = is_array($respuestas) && is_string($respuestas['pregunta2'] ?? null)
                ? trim($respuestas['pregunta2'])
                : '';

            if ($respuesta1 === '' || $respuesta2 === '') {
                $error = 'Por favor, responda las dos preguntas de seguridad.';
            } elseif (!verificarRespuestasSeguridad($datosUsuario, $respuesta1, $respuesta2)) {
                $error = 'Una o más respuestas son incorrectas.';
            } else {
                // La autorización solo permite cambiar la contraseña del usuario.
                unset($_SESSION['recuperacion']);
                $_SESSION['recuperacion_autorizada'] = [
                    'user_id' => $datosUsuario['id'],
                    'expira' => time() + 600,
                ];

                session_regenerate_id(true);

                header('Location: restablecer.php', true, 303);
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>SISCOIR - Recuperar contraseña</title>

    <!-- Estilos base, estilos del login y estilos propios de recuperación. -->
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/recovery.css">
</head>
<body>
    <!-- La vista muestra el paso correspondiente y los mensajes del controlador. -->
    <?php require __DIR__ . '/views/auth/recuperar.php'; ?>
</body>
</html>
