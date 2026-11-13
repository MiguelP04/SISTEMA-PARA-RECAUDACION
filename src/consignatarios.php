<?php

require_once __DIR__ . '/contribuyentes.php';

function obtenerFilasConsignatario(mysqli $conn, string $sql): array
{
    $resultado = $conn->query($sql);

    if ($resultado === false) {
        throw new RuntimeException('No se pudieron obtener los catálogos del consignatario.');
    }

    $filas = [];

    while ($fila = $resultado->fetch_assoc()) {
        $filas[] = $fila;
    }

    $resultado->free();

    return $filas;
}

function obtenerCatalogosConsignatario(mysqli $conn): array
{
    return [
        'tipos' => obtenerFilasConsignatario(
            $conn,
            'SELECT id_tipo_contribuyente, desc_tipo_contribuyente
             FROM tipo_contribuyente
             ORDER BY desc_tipo_contribuyente'
        ),
        'ciudades' => obtenerFilasConsignatario(
            $conn,
            'SELECT codigo, nombre, estado, abreviatura, zona_postal
             FROM ciudades
             ORDER BY nombre'
        ),
    ];
}

function buscarConsignatario(mysqli $conn, string $rif): ?array
{
    $sql = 'SELECT
                c.*,
                tc.desc_tipo_contribuyente,
                ci.nombre AS nombre_ciudad,
                ci.estado AS estado_ciudad,
                ci.zona_postal AS zona_postal_ciudad
            FROM consignatarios c
            LEFT JOIN tipo_contribuyente tc
                ON tc.id_tipo_contribuyente = c.tipo_contribuyente
            LEFT JOIN ciudades ci
                ON ci.codigo = c.cod_ciudad
            WHERE c.rif_consignatario = ?
            LIMIT 1';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $rif);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();
    $resultado->free();
    $stmt->close();

    return $fila ?: null;
}

function validarConsignatario(
    mysqli $conn,
    array $datos,
    array $catalogos,
    bool $esEdicion,
    ?string $rifOriginal = null
): array {
    $errores = [];
    $rif = $esEdicion
        ? normalizarRifContribuyente((string) $rifOriginal)
        : normalizarRifContribuyente(
            (string) ($datos['rif_prefijo'] ?? '')
            . (string) ($datos['rif_numero'] ?? '')
        );

    $nombre = limpiarTextoContribuyente($datos['nombre_consignatario'] ?? '');
    $direccion = limpiarTextoContribuyente($datos['direccion'] ?? '');
    $telefono = limpiarTextoContribuyente($datos['telefono'] ?? '');
    $siglas = limpiarTextoContribuyente($datos['siglas'] ?? '');
    $representante = limpiarTextoContribuyente($datos['representante'] ?? '');
    $codCiudad = trim((string) ($datos['cod_ciudad'] ?? ''));
    $referencia = limpiarTextoContribuyente($datos['referencia'] ?? '');
    $cir = strtoupper(trim((string) ($datos['cir'] ?? '')));
    $tipoTexto = trim((string) ($datos['tipo_contribuyente'] ?? ''));
    $tipo = $tipoTexto === '' ? false : filter_var($tipoTexto, FILTER_VALIDATE_INT);
    $zonaTexto = trim((string) ($datos['zona_postal'] ?? ''));
    $zonaPostal = $zonaTexto === '' ? 0 : filter_var($zonaTexto, FILTER_VALIDATE_INT);

    if (!validarRifContribuyente($rif)) {
        $errores[] = 'El RIF debe tener un prefijo válido y entre 7 y 9 dígitos.';
    } elseif (!$esEdicion) {
        $stmt = $conn->prepare('SELECT 1 FROM consignatarios WHERE rif_consignatario = ? LIMIT 1');
        $stmt->bind_param('s', $rif);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errores[] = 'Ya existe un consignatario con ese RIF.';
        }

        $stmt->close();
    }

    if ($nombre === '') {
        $errores[] = 'Debes indicar el nombre o razón social.';
    } elseif (longitudContribuyente($nombre) > 80) {
        $errores[] = 'El nombre no puede superar los 80 caracteres.';
    }

    $tiposValidos = array_map(
        'intval',
        array_column($catalogos['tipos'], 'id_tipo_contribuyente')
    );

    if ($tipo === false || $tipo === null || !in_array((int) $tipo, $tiposValidos, true)) {
        $errores[] = 'Debes seleccionar un tipo de contribuyente válido.';
    }

    if (!$esEdicion && $direccion === '') {
        $errores[] = 'Debes indicar la dirección fiscal.';
    } elseif (longitudContribuyente($direccion) > 120) {
        $errores[] = 'La dirección no puede superar los 120 caracteres.';
    }

    if (!$esEdicion && $telefono === '') {
        $errores[] = 'Debes indicar el teléfono.';
    } elseif (longitudContribuyente($telefono) > 12) {
        $errores[] = 'El teléfono no puede superar los 12 caracteres.';
    }

    if (!$esEdicion && $representante === '') {
        $errores[] = 'Debes indicar el representante.';
    } elseif (longitudContribuyente($representante) > 50) {
        $errores[] = 'El representante no puede superar los 50 caracteres.';
    }

    if (longitudContribuyente($siglas) > 50) {
        $errores[] = 'Las siglas no pueden superar los 50 caracteres.';
    }

    if (longitudContribuyente($referencia) > 70) {
        $errores[] = 'La referencia no puede superar los 70 caracteres.';
    }

    $ciudadesValidas = array_column($catalogos['ciudades'], 'codigo');

    if ($codCiudad !== '' && !in_array($codCiudad, $ciudadesValidas, true)) {
        $errores[] = 'La ciudad seleccionada no es válida.';
    }

    if ($zonaTexto !== '' && ($zonaPostal === false || $zonaPostal < 0 || $zonaPostal > 9999)) {
        $errores[] = 'La zona postal debe ser un número entre 0 y 9999.';
    }

    if (longitudContribuyente($cir) > 1) {
        $errores[] = 'El CIR debe tener un solo carácter.';
    }

    return [
        'errores' => array_values(array_unique($errores)),
        'datos' => [
            'rif_consignatario' => $rif,
            'nombre_consignatario' => $nombre,
            'direccion' => $direccion,
            'telefono' => $telefono,
            'siglas' => $siglas,
            'representante' => $representante,
            'tipo_contribuyente' => $tipo === false || $tipo === null ? 0 : (int) $tipo,
            'cod_ciudad' => $codCiudad,
            'referencia' => $referencia,
            'zona_postal' => $zonaPostal === false ? 0 : (int) $zonaPostal,
            'cir' => $cir,
        ],
    ];
}

function insertarConsignatario(mysqli $conn, array $datos): void
{
    $stmt = $conn->prepare(
        'INSERT INTO consignatarios (
            rif_consignatario,
            nombre_consignatario,
            direccion,
            telefono,
            siglas,
            representante,
            tipo_contribuyente,
            cod_ciudad,
            referencia,
            zona_postal,
            cir
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $stmt->bind_param(
        'ssssssissis',
        $datos['rif_consignatario'],
        $datos['nombre_consignatario'],
        $datos['direccion'],
        $datos['telefono'],
        $datos['siglas'],
        $datos['representante'],
        $datos['tipo_contribuyente'],
        $datos['cod_ciudad'],
        $datos['referencia'],
        $datos['zona_postal'],
        $datos['cir']
    );

    $stmt->execute();
    $stmt->close();
}

function actualizarConsignatario(mysqli $conn, string $rif, array $datos): void
{
    $stmt = $conn->prepare(
        'UPDATE consignatarios
         SET nombre_consignatario = ?,
             direccion = ?,
             telefono = ?,
             siglas = ?,
             representante = ?,
             tipo_contribuyente = ?,
             cod_ciudad = ?,
             referencia = ?,
             zona_postal = ?,
             cir = ?
         WHERE rif_consignatario = ?'
    );

    $stmt->bind_param(
        'sssssississ',
        $datos['nombre_consignatario'],
        $datos['direccion'],
        $datos['telefono'],
        $datos['siglas'],
        $datos['representante'],
        $datos['tipo_contribuyente'],
        $datos['cod_ciudad'],
        $datos['referencia'],
        $datos['zona_postal'],
        $datos['cir'],
        $rif
    );

    $stmt->execute();
    $stmt->close();
}

function eliminarConsignatario(mysqli $conn, string $rif): void
{
    $stmt = $conn->prepare('DELETE FROM consignatarios WHERE rif_consignatario = ?');
    $stmt->bind_param('s', $rif);
    $stmt->execute();
    $stmt->close();
}
