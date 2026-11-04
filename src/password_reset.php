<?php
/**
 * Funciones de recuperación de contraseña de SISCOIR.
 *
 * Este archivo debe cargarse después de src/bootstrap.php porque utiliza
 * la conexión a la base de datos.
 *
 * Responsabilidades:
 * - Buscar usuarios activos para iniciar la recuperación.
 * - Comprobar que el usuario tenga sus dos preguntas de seguridad.
 * - Verificar las respuestas contra los hashes almacenados.
 * - Guardar una nueva contraseña con password_hash().
 */

/**
 * Busca un usuario activo por su nombre de usuario.
 *
 * Devuelve también las preguntas y los hashes de las respuestas para que
 * el controlador pueda mostrar el formulario y verificar los datos.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param string $usuario Nombre de usuario escrito en el formulario.
 * @return array|false Datos del usuario o false si no existe o está inactivo.
 */
function buscarUsuarioRecuperacion(mysqli $conn, string $usuario): array|false
{
    $usuario = trim($usuario);

    if ($usuario === '') {
        return false;
    }

    $sql = 'SELECT id, username, nombre_completo, pregunta1, respuesta1, pregunta2, respuesta2
            FROM usuarios
            WHERE username = ? AND activo = 1
            LIMIT 1';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $datosUsuario = $resultado->fetch_assoc();
    $stmt->close();

    if (!$datosUsuario) {
        return false;
    }

    $datosUsuario['id'] = (int) $datosUsuario['id'];

    return $datosUsuario;
}

/**
 * Busca un usuario activo por su identificador.
 *
 * Se utiliza después de que el usuario supera el paso de las preguntas
 * de seguridad, para volver a comprobar sus datos antes de cambiar la
 * contraseña.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $usuarioId Identificador del usuario.
 * @return array|false Datos del usuario o false si no existe o está inactivo.
 */
function obtenerUsuarioRecuperacion(mysqli $conn, int $usuarioId): array|false
{
    if ($usuarioId <= 0) {
        return false;
    }

    $sql = 'SELECT id, username, nombre_completo, pregunta1, respuesta1, pregunta2, respuesta2
            FROM usuarios
            WHERE id = ? AND activo = 1
            LIMIT 1';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $datosUsuario = $resultado->fetch_assoc();
    $stmt->close();

    if (!$datosUsuario) {
        return false;
    }

    $datosUsuario['id'] = (int) $datosUsuario['id'];

    return $datosUsuario;
}

/**
 * Indica si el usuario tiene configuradas sus dos preguntas de seguridad.
 *
 * La recuperación necesita ambas preguntas para que la verificación sea
 * consistente con el formulario que se muestra en pantalla.
 *
 * @param array $usuario Datos obtenidos de las funciones de búsqueda.
 * @return bool True si las dos preguntas tienen texto.
 */
function tienePreguntasSeguridad(array $usuario): bool
{
    $pregunta1 = trim((string) ($usuario['pregunta1'] ?? ''));
    $pregunta2 = trim((string) ($usuario['pregunta2'] ?? ''));

    return $pregunta1 !== '' && $pregunta2 !== '';
}

/**
 * Comprueba las dos respuestas de seguridad del usuario.
 *
 * Las respuestas se guardan con password_hash(), por eso se comparan con
 * password_verify() y no directamente como texto.
 *
 * @param array $usuario Datos del usuario con sus respuestas cifradas.
 * @param string $respuesta1 Respuesta escrita para la primera pregunta.
 * @param string $respuesta2 Respuesta escrita para la segunda pregunta.
 * @return bool True si ambas respuestas coinciden.
 */
function verificarRespuestasSeguridad(
    array $usuario,
    string $respuesta1,
    string $respuesta2
): bool {
    if (!tienePreguntasSeguridad($usuario)) {
        return false;
    }

    $respuesta1 = trim($respuesta1);
    $respuesta2 = trim($respuesta2);

    if ($respuesta1 === '' || $respuesta2 === '') {
        return false;
    }

    $coincidePrimera = password_verify(
        $respuesta1,
        (string) ($usuario['respuesta1'] ?? '')
    );

    if (!$coincidePrimera) {
        return false;
    }

    return password_verify(
        $respuesta2,
        (string) ($usuario['respuesta2'] ?? '')
    );
}

/**
 * Actualiza la contraseña de un usuario.
 *
 * La contraseña se guarda únicamente como hash. La validación de longitud
 * se repite aquí para que la función no dependa solo del formulario.
 *
 * @param mysqli $conn Conexión activa a la base de datos.
 * @param int $usuarioId Identificador del usuario.
 * @param string $password Nueva contraseña escrita por el usuario.
 * @return bool True si la actualización se ejecutó correctamente.
 */
function actualizarPasswordUsuario(
    mysqli $conn,
    int $usuarioId,
    string $password
): bool {
    if ($usuarioId <= 0 || strlen($password) < 8) {
        return false;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    if ($hash === false) {
        return false;
    }

    $sql = 'UPDATE usuarios SET password = ? WHERE id = ?';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $hash, $usuarioId);
    $stmt->execute();
    $stmt->close();

    return true;
}
