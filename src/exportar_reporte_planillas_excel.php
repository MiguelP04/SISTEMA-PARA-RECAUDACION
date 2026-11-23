<?php

require_once __DIR__ . '/reportes.php';
require_once __DIR__ . '/reportes_diario.php';

function exportarReportePlanillasExcel(
    mysqli_result $resultado,
    string $fechaDesde,
    string $fechaHasta,
    string $nombreArchivo,
    string $titulo
): void {
    $ramos = obtenerRamosPlanillasPagadas();
    $totalColumnas = 15 + count($ramos) + 1;

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.xls"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    echo "\xEF\xBB\xBF";
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="utf-8"><style>';
    echo 'table{border-collapse:collapse;font-family:Arial,sans-serif;font-size:10pt;}';
    echo 'th{background:#1a1f53;color:#ffffff;border:1px solid #8f8f8f;padding:5px;}';
    echo 'td{border:1px solid #8f8f8f;padding:4px;}';
    echo '.texto{mso-number-format:"\@";}';
    echo '</style></head><body>';
    echo '<h2>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</h2>';
    echo '<p><strong>Desde:</strong> ' . htmlspecialchars(formatearFechaReporte($fechaDesde), ENT_QUOTES, 'UTF-8');
    echo ' &nbsp;&nbsp; <strong>Hasta:</strong> ' . htmlspecialchars(formatearFechaReporte($fechaHasta), ENT_QUOTES, 'UTF-8') . '</p>';

    echo '<table>';
    echo '<thead><tr>';
    echo '<th>Cód. Aduana</th>';
    echo '<th>RIF Consignatario</th>';
    echo '<th>Nombre Consignatario</th>';
    echo '<th>RIF Agente</th>';
    echo '<th>Nombre Agente</th>';
    echo '<th>Nro. Formulario</th>';
    echo '<th>Nro. Resolución</th>';
    echo '<th>Nro. Declaración</th>';
    echo '<th>Fecha Cancelación</th>';
    echo '<th>Fecha Contabilidad</th>';
    echo '<th>Fecha Notificación</th>';
    echo '<th>Cód. Forma</th>';
    echo '<th>Cód. Banco</th>';
    echo '<th>Cód. Agencia</th>';
    echo '<th>Monto 2</th>';

    foreach ($ramos as $ramo) {
        echo '<th>' . htmlspecialchars($ramo['etiqueta'], ENT_QUOTES, 'UTF-8') . '</th>';
    }

    echo '<th>TOTAL</th>';
    echo '</tr></thead><tbody>';

    $hayFilas = false;

    while ($fila = $resultado->fetch_assoc()) {
        $hayFilas = true;
        $totalRamos = 0.0;

        echo '<tr>';
        echo '<td class="texto">' . htmlspecialchars((string) ($fila['codigo_aduana'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="texto">' . htmlspecialchars((string) ($fila['rif_consignatario'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars((string) ($fila['nombre_consignatario'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="texto">' . htmlspecialchars((string) ($fila['rif_agente'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars((string) ($fila['nombre_agente'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="texto">' . htmlspecialchars((string) ($fila['nro_formulario'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="texto">' . htmlspecialchars((string) ($fila['nro_resolucion'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="texto">' . htmlspecialchars((string) ($fila['nro_declaracion'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="texto">' . htmlspecialchars(formatearFechaReporte($fila['fecha_cancelacion'] ?? null), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="texto">' . htmlspecialchars(formatearFechaReporte($fila['fecha_contabilidad_liq'] ?? null), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="texto">' . htmlspecialchars(formatearFechaReporte($fila['fecha_de_notificacion'] ?? null), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="texto">' . htmlspecialchars((string) ($fila['cod_de_forma'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="texto">' . htmlspecialchars((string) ($fila['cod_banco'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="texto">' . htmlspecialchars((string) ($fila['cod_agencia'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="texto">' . htmlspecialchars(formatearMontoReporte($fila['monto2'] ?? 0), ENT_QUOTES, 'UTF-8') . '</td>';

        foreach ($ramos as $ramo) {
            $valor = (float) ($fila[$ramo['clave']] ?? 0);
            $totalRamos += $valor;
            echo '<td class="texto">' . htmlspecialchars(formatearMontoReporte($valor), ENT_QUOTES, 'UTF-8') . '</td>';
        }

        echo '<td class="texto">' . htmlspecialchars(formatearMontoReporte($totalRamos), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '</tr>';
    }

    if (!$hayFilas) {
        echo '<tr><td colspan="' . $totalColumnas . '">No se encontraron registros para el rango de fechas seleccionado.</td></tr>';
    }

    echo '</tbody></table>';
    echo '</body></html>';

    $resultado->free();
}
