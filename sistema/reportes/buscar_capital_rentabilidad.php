<?php
include "../../conexion.php";
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$fechaDesde = isset($input['fechaDesde']) ? trim($input['fechaDesde']) : date('Y-m-01');
$fechaHasta = isset($input['fechaHasta']) ? trim($input['fechaHasta']) : date('Y-m-d');

if ($fechaDesde > $fechaHasta) {
    $tmp = $fechaDesde;
    $fechaDesde = $fechaHasta;
    $fechaHasta = $tmp;
}

$gastosCols = [];
$resGastos = $conexionDB->query("SHOW COLUMNS FROM gastos");
if ($resGastos) {
    while ($row = $resGastos->fetch_assoc()) {
        $gastosCols[] = strtolower($row['Field']);
    }
}

$tieneGastosMonto = in_array('montogasto', $gastosCols) || in_array('monto', $gastosCols);
$tieneGastosFecha = in_array('fechagasto', $gastosCols) || in_array('fecha', $gastosCols) || in_array('fecharegistro', $gastosCols);

if ($tieneGastosMonto && in_array('montogasto', $gastosCols)) {
    $montoColG = 'montoGasto';
} else if ($tieneGastosMonto && in_array('monto', $gastosCols)) {
    $montoColG = 'Monto';
} else {
    $montoColG = 'montoGasto';
}

if (in_array('fechagasto', $gastosCols)) {
    $fechaColG = 'fechaGasto';
} else if (in_array('fecharegistro', $gastosCols)) {
    $fechaColG = 'FechaRegistro';
} else if (in_array('fecha', $gastosCols)) {
    $fechaColG = 'Fecha';
} else {
    $fechaColG = 'fechaGasto';
}

$sqlIngresos = "SELECT COALESCE(SUM(Total), 0) AS total
    FROM ventas
    WHERE LOWER(COALESCE(Estado, '')) IN ('pagado','saldo','pendiente')
    AND LOWER(COALESCE(Estado, '')) <> 'anulado'
    AND DATE(Fecha) BETWEEN ? AND ?";
$stmtI = $conexionDB->prepare($sqlIngresos);
$stmtI->bind_param('ss', $fechaDesde, $fechaHasta);
$stmtI->execute();
$resI = $stmtI->get_result()->fetch_assoc();
$ingresos = (float)($resI['total'] ?? 0);
$stmtI->close();

$gastos = 0;
$fallbackGastos = false;

if ($tieneGastosMonto && $tieneGastosFecha) {
    $sqlGastos = "SELECT COALESCE(SUM({$montoColG}), 0) AS total
        FROM gastos
        WHERE DATE({$fechaColG}) BETWEEN ? AND ?";
    $stmtG = $conexionDB->prepare($sqlGastos);
    $stmtG->bind_param('ss', $fechaDesde, $fechaHasta);
    $stmtG->execute();
    $resG = $stmtG->get_result()->fetch_assoc();
    $gastos = (float)($resG['total'] ?? 0);
    $stmtG->close();
} else {
    $fallbackGastos = true;
    $checkCaja = $conexionDB->query("SHOW COLUMNS FROM caja LIKE 'Actividad'");
    if ($checkCaja && $checkCaja->num_rows > 0) {
        $sqlCaja = "SELECT COALESCE(SUM(Monto), 0) AS total
            FROM caja
            WHERE LOWER(COALESCE(Actividad, '')) = 'gasto'
            AND DATE(Fecha) BETWEEN ? AND ?";
        $stmtC = $conexionDB->prepare($sqlCaja);
        $stmtC->bind_param('ss', $fechaDesde, $fechaHasta);
        $stmtC->execute();
        $resC = $stmtC->get_result()->fetch_assoc();
        $gastos = (float)($resC['total'] ?? 0);
        $stmtC->close();
    }
}

$utilidad = $ingresos - $gastos;
$rentabilidadPct = $ingresos > 0 ? (($utilidad / $ingresos) * 100) : 0;

$hoy = new DateTime();
$serie30 = [];
for ($i = 29; $i >= 0; $i--) {
    $d = clone $hoy;
    $d->modify("-{$i} day");
    $serie30[] = $d->format('Y-m-d');
}

$mapaIngresos = [];
$sqlSerieI = "SELECT DATE(Fecha) AS dia, COALESCE(SUM(Total), 0) AS monto
    FROM ventas
    WHERE LOWER(COALESCE(Estado, '')) IN ('pagado','saldo','pendiente')
    AND LOWER(COALESCE(Estado, '')) <> 'anulado'
    AND DATE(Fecha) BETWEEN ? AND ?
    GROUP BY DATE(Fecha)";
$stmtSI = $conexionDB->prepare($sqlSerieI);
$ini30 = $serie30[0];
$fin30 = end($serie30);
$stmtSI->bind_param('ss', $ini30, $fin30);
$stmtSI->execute();
$resSI = $stmtSI->get_result();
while ($row = $resSI->fetch_assoc()) {
    $mapaIngresos[$row['dia']] = (float)$row['monto'];
}
$stmtSI->close();

$mapaGastos = [];
if ($tieneGastosMonto && $tieneGastosFecha) {
    $sqlSerieG = "SELECT DATE({$fechaColG}) AS dia, COALESCE(SUM({$montoColG}), 0) AS monto
        FROM gastos
        WHERE DATE({$fechaColG}) BETWEEN ? AND ?
        GROUP BY DATE({$fechaColG})";
    $stmtSG = $conexionDB->prepare($sqlSerieG);
    $stmtSG->bind_param('ss', $ini30, $fin30);
    $stmtSG->execute();
    $resSG = $stmtSG->get_result();
    while ($row = $resSG->fetch_assoc()) {
        $mapaGastos[$row['dia']] = (float)$row['monto'];
    }
    $stmtSG->close();
} else if ($fallbackGastos) {
    $checkCaja2 = $conexionDB->query("SHOW COLUMNS FROM caja LIKE 'Actividad'");
    if ($checkCaja2 && $checkCaja2->num_rows > 0) {
        $sqlSerieG = "SELECT DATE(Fecha) AS dia, COALESCE(SUM(Monto), 0) AS monto
            FROM caja
            WHERE LOWER(COALESCE(Actividad, '')) = 'gasto'
            AND DATE(Fecha) BETWEEN ? AND ?
            GROUP BY DATE(Fecha)";
        $stmtSG = $conexionDB->prepare($sqlSerieG);
        $stmtSG->bind_param('ss', $ini30, $fin30);
        $stmtSG->execute();
        $resSG = $stmtSG->get_result();
        while ($row = $resSG->fetch_assoc()) {
            $mapaGastos[$row['dia']] = (float)$row['monto'];
        }
        $stmtSG->close();
    }
}

$serieIngresos = [];
$serieGastos = [];
$serieUtilidad = [];
foreach ($serie30 as $dia) {
    $mI = $mapaIngresos[$dia] ?? 0;
    $mG = $mapaGastos[$dia] ?? 0;
    $mU = $mI - $mG;
    $etq = (new DateTime($dia))->format('d/m');
    $serieIngresos[] = ['fecha' => $etq, 'monto' => round($mI, 2)];
    $serieGastos[] = ['fecha' => $etq, 'monto' => round($mG, 2)];
    $serieUtilidad[] = ['fecha' => $etq, 'monto' => round($mU, 2)];
}

$serie15 = [];
for ($i = 14; $i >= 0; $i--) {
    $d = clone $hoy;
    $d->modify("-{$i} day");
    $serie15[] = $d->format('Y-m-d');
}

$mapa15Ingresos = [];
$sql15I = "SELECT DATE(Fecha) AS dia, COALESCE(SUM(Total), 0) AS monto
    FROM ventas
    WHERE LOWER(COALESCE(Estado, '')) IN ('pagado','saldo','pendiente')
    AND LOWER(COALESCE(Estado, '')) <> 'anulado'
    AND DATE(Fecha) BETWEEN ? AND ?
    GROUP BY DATE(Fecha)";
$stmt15I = $conexionDB->prepare($sql15I);
$ini15 = $serie15[0];
$fin15 = end($serie15);
$stmt15I->bind_param('ss', $ini15, $fin15);
$stmt15I->execute();
$res15I = $stmt15I->get_result();
while ($row = $res15I->fetch_assoc()) {
    $mapa15Ingresos[$row['dia']] = (float)$row['monto'];
}
$stmt15I->close();

$mapa15Gastos = [];
if ($tieneGastosMonto && $tieneGastosFecha) {
    $sql15G = "SELECT DATE({$fechaColG}) AS dia, COALESCE(SUM({$montoColG}), 0) AS monto
        FROM gastos
        WHERE DATE({$fechaColG}) BETWEEN ? AND ?
        GROUP BY DATE({$fechaColG})";
    $stmt15G = $conexionDB->prepare($sql15G);
    $stmt15G->bind_param('ss', $ini15, $fin15);
    $stmt15G->execute();
    $res15G = $stmt15G->get_result();
    while ($row = $res15G->fetch_assoc()) {
        $mapa15Gastos[$row['dia']] = (float)$row['monto'];
    }
    $stmt15G->close();
} else if ($fallbackGastos) {
    $checkCaja3 = $conexionDB->query("SHOW COLUMNS FROM caja LIKE 'Actividad'");
    if ($checkCaja3 && $checkCaja3->num_rows > 0) {
        $sql15G = "SELECT DATE(Fecha) AS dia, COALESCE(SUM(Monto), 0) AS monto
            FROM caja
            WHERE LOWER(COALESCE(Actividad, '')) = 'gasto'
            AND DATE(Fecha) BETWEEN ? AND ?
            GROUP BY DATE(Fecha)";
        $stmt15G = $conexionDB->prepare($sql15G);
        $stmt15G->bind_param('ss', $ini15, $fin15);
        $stmt15G->execute();
        $res15G = $stmt15G->get_result();
        while ($row = $res15G->fetch_assoc()) {
            $mapa15Gastos[$row['dia']] = (float)$row['monto'];
        }
        $stmt15G->close();
    }
}

$tablaDiaria = [];
foreach ($serie15 as $dia) {
    $mI = $mapa15Ingresos[$dia] ?? 0;
    $mG = $mapa15Gastos[$dia] ?? 0;
    $etq = (new DateTime($dia))->format('d/m/Y');
    $tablaDiaria[] = [
        'fecha' => $etq,
        'ingresos' => round($mI, 2),
        'gastos' => round($mG, 2),
        'utilidad' => round($mI - $mG, 2)
    ];
}

echo json_encode([
    'resultado' => true,
    'ingresos' => round($ingresos, 2),
    'gastos' => round($gastos, 2),
    'utilidad' => round($utilidad, 2),
    'rentabilidadPct' => round($rentabilidadPct, 2),
    'serieIngresos' => $serieIngresos,
    'serieGastos' => $serieGastos,
    'serieUtilidad' => $serieUtilidad,
    'tablaDiaria' => $tablaDiaria,
    'totalFilas' => count($tablaDiaria),
    'fallbackGastos' => $fallbackGastos
], JSON_UNESCAPED_UNICODE);

$conexionDB->close();
