<?php
/**
 * Funciones de autenticación y manejo de sesión de SISCOIR.
 *
 * Este archivo debe cargarse después de src/bootstrap.php, porque utiliza
 * la sesión iniciada y la conexión a la base de datos.
 *
 * Responsabilidades:
 * - Validar las credenciales de un usuario.
 * - Crear y cerrar la sesión.
 * - Proteger las páginas que requieren autenticación.
 * - Actualizar la fecha del último acceso.
 */

/**
 * Busca un usuario y verifica su contraseña.
 *
 * La consulta usa una sentencia preparada para evitar inyección SQL.
 * Solo se aceptan usuarios activos y contraseñas que coincidan con el
 * hash almacenado en la base de datos.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param string $usuario Nombre de usuario escrito en el formulario.
 * @param string $password Contraseña escrita por el usuario.
 * @return array|false Datos del usuario sin la contraseña, o false si falla.
 */
function autenticarUsuario(mysqli $conn, string $usuario, string $password): array|false
{
    // Limpia espacios accidentales del nombre de usuario.
    $usuario = trim($usuario);

    // Un usuario o una contraseña vacíos no pueden autenticarse.
    if ($usuario === '' || $password === '') {
        return false;
    }

    // Consulta preparada: el valor del usuario se envía por separado
    // y nunca se concatena directamente en la sentencia SQL.
    $sql = 'SELECT id, username, password, nombre_completo, rol, activo
            FROM usuarios
            WHERE username = ?
            LIMIT 1';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $usuario);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $datosUsuario = $resultado->fetch_assoc();
    $stmt->close();

    // Si no existe el usuario, se devuelve false sin revelar el motivo.
    if (!$datosUsuario) {
        return false;
    }

    // Solo se permite el acceso a usuarios marcados como activos.
    if ((int) $datosUsuario['activo'] !== 1) {
        return false;
    }

    // Compara la contraseña escrita con el hash guardado en la base.
    if (!password_verify($password, $datosUsuario['password'])) {
        return false;
    }

    // Normaliza los campos numéricos y elimina el hash antes de devolverlos.
    $datosUsuario['id'] = (int) $datosUsuario['id'];
    $datosUsuario['activo'] = (int) $datosUsuario['activo'];
    unset($datosUsuario['password']);

    return $datosUsuario;
}

/**
 * Guarda los datos del usuario en la sesión actual.
 *
 * Antes de guardar la información se regenera el identificador de sesión
 * para evitar que una sesión anterior pueda reutilizarse después del login.
 *
 * @param array $usuario Datos obtenidos de autenticarUsuario().
 * @return void
 */
function iniciarSesionUsuario(array $usuario): void
{
    // Cambia el ID de sesión y elimina el archivo de la sesión anterior.
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    // Datos mínimos que utilizarán las páginas internas del sistema.
    $_SESSION['loggedin'] = true;
    $_SESSION['user_id'] = (int) $usuario['id'];
    $_SESSION['username'] = $usuario['username'];
    $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
    $_SESSION['rol'] = $usuario['rol'];
}

/**
 * Registra la fecha y hora del último acceso correcto.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $usuarioId Identificador del usuario autenticado.
 * @return void
 */
function actualizarUltimoAcceso(mysqli $conn, int $usuarioId): void
{
    $sql = 'UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $stmt->close();
}

/**
 * Indica si existe una sesión autenticada válida.
 *
 * @return bool True si el usuario inició sesión; false en caso contrario.
 */
function estaAutenticado(): bool
{
    return isset($_SESSION['loggedin'], $_SESSION['user_id'])
        && $_SESSION['loggedin'] === true;
}

/**
 * Redirige al login cuando no existe una sesión autenticada.
 *
 * Debe utilizarse al inicio de las páginas internas.
 *
 * @return void
 */
function requiereLogin(): void
{
    if (!estaAutenticado()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Elimina la sesión actual y su cookie.
 *
 * Se vacían los datos de sesión, se borra la cookie del navegador y
 * se destruye la sesión en el servidor.
 *
 * @return void
 */
function cerrarSesion(): void
{
    // Si no hay una sesión activa, no hay nada que cerrar.
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    // Elimina todas las variables guardadas en la sesión.
    $_SESSION = [];

    // Borra la cookie con los mismos parámetros usados al crearla.
    // Solo se intenta si las cabeceras todavía no fueron enviadas.
    if (ini_get('session.use_cookies') && !headers_sent()) {
        $parametros = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            [
                'expires' => time() - 42000,
                'path' => $parametros['path'],
                'domain' => $parametros['domain'],
                'secure' => $parametros['secure'],
                'httponly' => $parametros['httponly'],
                'samesite' => $parametros['samesite'] ?? 'Lax',
            ]
        );
    }

    // Destruye la sesión en el servidor.
    session_destroy();
}
