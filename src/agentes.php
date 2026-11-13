<?php

require_once __DIR__ . '/contribuyentes.php';

function buscarAgente(mysqli $conn, string $rif): ?array
{
    $stmt = $conn->prepare(
        'SELECT *
         FROM agentes_aduanales
         WHERE rif_agentes = ?
         LIMIT 1'
    );
    $stmt->bind_param('s', $rif);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();
    $resultado->free();
    $stmt->close();

    return $fila ?: null;
}

function validarAgente(
    mysqli $conn,
    array $datos,
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

    $nombre = limpiarTextoContribuyente($datos['nombre_agente'] ?? '');
    $siglas = limpiarTextoContribuyente($datos['siglas_agente'] ?? '');
    $representante = limpiarTextoContribuyente($datos['representante'] ?? '');
    $telefono = limpiarTextoContribuyente($datos['telefono'] ?? '');
    $domicilio = limpiarTextoContribuyente($datos['domicilio_fiscal'] ?? '');
    $numeroRegistro = limpiarTextoContribuyente(
        $datos['numero_registro_agente_aduanal'] ?? ''
    );

    if (!validarRifContribuyente($rif)) {
        $errores[] = 'El RIF debe tener un prefijo válido y entre 7 y 9 dígitos.';
    } elseif (!$esEdicion) {
        $stmt = $conn->prepare(
            'SELECT 1 FROM agentes_aduanales WHERE rif_agentes = ? LIMIT 1'
        );
        $stmt->bind_param('s', $rif);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errores[] = 'Ya existe un agente aduanal con ese RIF.';
        }

        $stmt->close();
    }

    if ($nombre === '') {
        $errores[] = 'Debes indicar el nombre o razón social.';
    } elseif (longitudContribuyente($nombre) > 50) {
        $errores[] = 'El nombre no puede superar los 50 caracteres.';
    }

    if (!$esEdicion && $representante === '') {
        $errores[] = 'Debes indicar el representante.';
    } elseif (longitudContribuyente($representante) > 40) {
        $errores[] = 'El representante no puede superar los 40 caracteres.';
    }

    if (!$esEdicion && $telefono === '') {
        $errores[] = 'Debes indicar el teléfono.';
    } elseif (longitudContribuyente($telefono) > 10) {
        $errores[] = 'El teléfono no puede superar los 10 caracteres.';
    }

    if (!$esEdicion && $domicilio === '') {
        $errores[] = 'Debes indicar el domicilio fiscal.';
    } elseif (longitudContribuyente($domicilio) > 100) {
        $errores[] = 'El domicilio fiscal no puede superar los 100 caracteres.';
    }

    if (longitudContribuyente($siglas) > 40) {
        $errores[] = 'Las siglas no pueden superar los 40 caracteres.';
    }

    if (longitudContribuyente($numeroRegistro) > 4) {
        $errores[] = 'El número de registro no puede superar los 4 caracteres.';
    }

    return [
        'errores' => array_values(array_unique($errores)),
        'datos' => [
            'rif_agentes' => $rif,
            'nombre_agente' => $nombre,
            'siglas_agente' => $siglas,
            'representante' => $representante,
            'telefono' => $telefono,
            'domicilio_fiscal' => $domicilio,
            'numero_registro_agente_aduanal' => $numeroRegistro,
        ],
    ];
}

function insertarAgente(mysqli $conn, array $datos): void
{
    $stmt = $conn->prepare(
        'INSERT INTO agentes_aduanales (
            rif_agentes,
            nombre_agente,
            siglas_agente,
            representante,
            telefono,
            domicilio_fiscal,
            numero_registro_agente_aduanal
        ) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    $stmt->bind_param(
        'sssssss',
        $datos['rif_agentes'],
        $datos['nombre_agente'],
        $datos['siglas_agente'],
        $datos['representante'],
        $datos['telefono'],
        $datos['domicilio_fiscal'],
        $datos['numero_registro_agente_aduanal']
    );

    $stmt->execute();
    $stmt->close();
}

function actualizarAgente(mysqli $conn, string $rif, array $datos): void
{
    $stmt = $conn->prepare(
        'UPDATE agentes_aduanales
         SET nombre_agente = ?,
             siglas_agente = ?,
             representante = ?,
             telefono = ?,
             domicilio_fiscal = ?,
             numero_registro_agente_aduanal = ?
         WHERE rif_agentes = ?'
    );

    $stmt->bind_param(
        'sssssss',
        $datos['nombre_agente'],
        $datos['siglas_agente'],
        $datos['representante'],
        $datos['telefono'],
        $datos['domicilio_fiscal'],
        $datos['numero_registro_agente_aduanal'],
        $rif
    );

    $stmt->execute();
    $stmt->close();
}

function eliminarAgente(mysqli $conn, string $rif): void
{
    $stmt = $conn->prepare('DELETE FROM agentes_aduanales WHERE rif_agentes = ?');
    $stmt->bind_param('s', $rif);
    $stmt->execute();
    $stmt->close();
}
