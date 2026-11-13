<?php

function consultarFilaDashboard(mysqli $conn, string $sql): array
{
    $resultado = $conn->query($sql);

    if ($resultado === false) {
        throw new RuntimeException('No se pudo consultar el resumen del inicio.');
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return $fila ?: [];
}

function obtenerResumenDashboard(mysqli $conn): array
{
    $planillasMes = consultarFilaDashboard(
        $conn,
        "SELECT COUNT(*) AS total, COALESCE(SUM(monto2), 0) AS monto
         FROM planillas_emitidas
         WHERE COALESCE(anulada, 0) <> 1
           AND YEAR(fecha_emision) = YEAR(CURDATE())
           AND MONTH(fecha_emision) = MONTH(CURDATE())"
    );

    $ultimaPlanilla = consultarFilaDashboard(
        $conn,
        "SELECT nro_formulario, fecha_emision, monto2
         FROM planillas_emitidas
         WHERE COALESCE(anulada, 0) <> 1
         ORDER BY system_date DESC, nro_formulario DESC
         LIMIT 1"
    );

    $recaudadoMes = consultarFilaDashboard(
        $conn,
        "SELECT COUNT(*) AS total, COALESCE(SUM(monto2), 0) AS monto
         FROM planillas_emitidas
         WHERE COALESCE(anulada, 0) <> 1
           AND fecha_cancelacion > '0000-00-00'
           AND YEAR(fecha_cancelacion) = YEAR(CURDATE())
           AND MONTH(fecha_cancelacion) = MONTH(CURDATE())"
    );

    $ultimoPago = consultarFilaDashboard(
        $conn,
        "SELECT fecha_cancelacion, monto2
         FROM planillas_emitidas
         WHERE COALESCE(anulada, 0) <> 1
           AND fecha_cancelacion > '0000-00-00'
         ORDER BY fecha_cancelacion DESC, system_date DESC
         LIMIT 1"
    );

    $pendientes = consultarFilaDashboard(
        $conn,
        "SELECT COUNT(*) AS total, COALESCE(SUM(monto2), 0) AS monto
         FROM planillas_emitidas
         WHERE COALESCE(anulada, 0) <> 1
           AND (fecha_cancelacion IS NULL OR fecha_cancelacion = '0000-00-00')"
    );

    $vencidas = consultarFilaDashboard(
        $conn,
        "SELECT COUNT(*) AS total, COALESCE(SUM(monto2), 0) AS monto
         FROM planillas_emitidas
         WHERE COALESCE(anulada, 0) <> 1
           AND (fecha_cancelacion IS NULL OR fecha_cancelacion = '0000-00-00')
           AND fecha_vencimiento > '0000-00-00'
           AND fecha_vencimiento < CURDATE()"
    );

    return [
        'planillas_mes' => [
            'cantidad' => (int) ($planillasMes['total'] ?? 0),
            'monto' => (float) ($planillasMes['monto'] ?? 0),
        ],
        'ultima_planilla' => $ultimaPlanilla !== [] ? [
            'nro_formulario' => trim((string) ($ultimaPlanilla['nro_formulario'] ?? '')),
            'fecha_emision' => (string) ($ultimaPlanilla['fecha_emision'] ?? ''),
            'monto' => (float) ($ultimaPlanilla['monto2'] ?? 0),
        ] : null,
        'recaudado_mes' => [
            'cantidad' => (int) ($recaudadoMes['total'] ?? 0),
            'monto' => (float) ($recaudadoMes['monto'] ?? 0),
        ],
        'ultimo_pago' => $ultimoPago !== [] ? [
            'fecha_cancelacion' => (string) ($ultimoPago['fecha_cancelacion'] ?? ''),
            'monto' => (float) ($ultimoPago['monto2'] ?? 0),
        ] : null,
        'pendientes' => [
            'cantidad' => (int) ($pendientes['total'] ?? 0),
            'monto' => (float) ($pendientes['monto'] ?? 0),
        ],
        'vencidas' => [
            'cantidad' => (int) ($vencidas['total'] ?? 0),
            'monto' => (float) ($vencidas['monto'] ?? 0),
        ],
    ];
}

function formatearMontoDashboard(mixed $monto): string
{
    return number_format((float) $monto, 2, ',', '.');
}

function formatearFechaDashboard(?string $fecha): string
{
    if ($fecha === null || $fecha === '' || $fecha === '0000-00-00') {
        return '—';
    }

    $marcaTiempo = strtotime($fecha);

    return $marcaTiempo === false ? '—' : date('d/m/Y', $marcaTiempo);
}
