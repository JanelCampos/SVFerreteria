<?php
include "../../conexion.php";
session_start();
if (empty($_SESSION['active']) || $_SESSION['rol'] != 1) {
    header('location: ../');
}

$fechaDesde = isset($_GET['fechaDesde']) ? trim($_GET['fechaDesde']) : date('Y-m-01');
$fechaHasta = isset($_GET['fechaHasta']) ? trim($_GET['fechaHasta']) : date('Y-m-d');
$nPdf = isset($_GET['nPdf']) ? (int)$_GET['nPdf'] : 0;
$nExcel = isset($_GET['nExcel']) ? (int)$_GET['nExcel'] : 0;

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
$serie15 = [];
for ($i = 14; $i >= 0; $i--) {
    $d = clone $hoy;
    $d->modify("-{$i} day");
    $serie15[] = $d->format('Y-m-d');
}
$ini15 = $serie15[0];
$fin15 = end($serie15);

$mapa15Ingresos = [];
$sql15I = "SELECT DATE(Fecha) AS dia, COALESCE(SUM(Total), 0) AS monto
    FROM ventas
    WHERE LOWER(COALESCE(Estado, '')) IN ('pagado','saldo','pendiente')
    AND LOWER(COALESCE(Estado, '')) <> 'anulado'
    AND DATE(Fecha) BETWEEN ? AND ?
    GROUP BY DATE(Fecha)";
$stmt15I = $conexionDB->prepare($sql15I);
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
} else {
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
$totI = 0;
$totG = 0;
$totU = 0;
foreach ($serie15 as $dia) {
    $mI = $mapa15Ingresos[$dia] ?? 0;
    $mG = $mapa15Gastos[$dia] ?? 0;
    $mU = $mI - $mG;
    $etq = (new DateTime($dia))->format('d/m/Y');
    $tablaDiaria[] = [
        'fecha' => $etq,
        'ingresos' => round($mI, 2),
        'gastos' => round($mG, 2),
        'utilidad' => round($mU, 2)
    ];
    $totI += $mI;
    $totG += $mG;
    $totU += $mU;
}
$conexionDB->close();

if ($nPdf == 1) {
    require("../../fpdf/fpdf.php");

    class MiPDF extends FPDF
    {
        function Header()
        {
            $this->Image('../../img/pachacutec.png', 15, 5, 25);
            $this->SetFont('Arial', 'B', 14);
            $this->Cell(25);
            $this->Cell(140, 10, mb_convert_encoding('FERRETERIA PACHACUTEC', 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
            $this->Ln(8);
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(25);
            $this->Cell(140, 8, mb_convert_encoding('CAPITAL Y RENTABILIDAD', 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
            $this->Ln(6);
            $this->SetFont('Arial', '', 9);
            $this->Cell(25);
            $this->Cell(140, 6, mb_convert_encoding('Periodo: ' . $GLOBALS['fechaDesde'] . ' al ' . $GLOBALS['fechaHasta'], 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
            $this->Ln(12);
        }

        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, mb_convert_encoding('Pagina ', 'ISO-8859-1', 'UTF-8') . $this->PageNo() . ' / {nb}', 0, 0, 'C');
        }
    }

    $pdf = new MiPDF('P', 'mm', 'A4');
    $pdf->AliasNBPages();
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetFillColor(230, 245, 230);
    $pdf->Cell(47, 12, mb_convert_encoding('INGRESOS', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->SetFillColor(245, 230, 230);
    $pdf->Cell(47, 12, mb_convert_encoding('GASTOS', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->SetFillColor(230, 235, 250);
    $pdf->Cell(47, 12, mb_convert_encoding('UTILIDAD NETA', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->SetFillColor(250, 245, 210);
    $pdf->Cell(47, 12, mb_convert_encoding('% RENTABILIDAD', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', 1);

    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(47, 14, 'S/. ' . number_format($ingresos, 2), 1, 0, 'C', 1);
    $pdf->Cell(47, 14, 'S/. ' . number_format($gastos, 2), 1, 0, 'C', 1);
    $pdf->Cell(47, 14, 'S/. ' . number_format($utilidad, 2), 1, 0, 'C', 1);
    $pdf->Cell(47, 14, number_format($rentabilidadPct, 2) . '%', 1, 1, 'C', 1);

    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, mb_convert_encoding('HISTORIAL ULTIMOS 15 DIAS', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
    $pdf->Ln(2);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(55, 8, mb_convert_encoding('Dia', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->Cell(45, 8, mb_convert_encoding('Ingresos S/.', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->Cell(45, 8, mb_convert_encoding('Gastos S/.', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->Cell(45, 8, mb_convert_encoding('Utilidad S/.', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', 1);

    $pdf->SetFont('Arial', '', 9);
    foreach ($tablaDiaria as $row) {
        $pdf->Cell(55, 7, $row['fecha'], 1, 0, 'C');
        $pdf->Cell(45, 7, 'S/. ' . number_format($row['ingresos'], 2), 1, 0, 'R');
        $pdf->Cell(45, 7, 'S/. ' . number_format($row['gastos'], 2), 1, 0, 'R');
        $pdf->Cell(45, 7, 'S/. ' . number_format($row['utilidad'], 2), 1, 1, 'R');
    }

    if (count($tablaDiaria) == 0) {
        $pdf->Cell(190, 7, mb_convert_encoding('No hay datos para el periodo', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
    }

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(55, 8, mb_convert_encoding('TOTALES', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->Cell(45, 8, 'S/. ' . number_format($totI, 2), 1, 0, 'R', 1);
    $pdf->Cell(45, 8, 'S/. ' . number_format($totG, 2), 1, 0, 'R', 1);
    $pdf->Cell(45, 8, 'S/. ' . number_format($totU, 2), 1, 1, 'R', 1);

    $pdf->Output('I', 'Reporte_capital_rentabilidad.pdf');
    exit;
}

if ($nExcel == 1) {
    header('Content-type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename=Reporte_capital_rentabilidad.xls');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>';

    echo '<table border="1" style="border-collapse:collapse;">';
    echo '<tr><td colspan="4" style="text-align:center; font-size:16px; font-weight:bold;">FERRETERIA PACHACUTEC</td></tr>';
    echo '<tr><td colspan="4" style="text-align:center; font-size:14px; font-weight:bold;">CAPITAL Y RENTABILIDAD</td></tr>';
    echo '<tr><td colspan="4" style="text-align:center;">Periodo: ' . $fechaDesde . ' al ' . $fechaHasta . '</td></tr>';
    echo '</table><br>';

    echo '<table border="1" style="border-collapse:collapse;">';
    echo '<tr style="background-color:#DDDDDD; font-weight:bold;">';
    echo '<th style="width:140px; text-align:center;">INGRESOS</th>';
    echo '<th style="width:140px; text-align:center;">GASTOS</th>';
    echo '<th style="width:140px; text-align:center;">UTILIDAD NETA</th>';
    echo '<th style="width:140px; text-align:center;">% RENTABILIDAD</th>';
    echo '</tr>';
    echo '<tr style="font-size:14px; font-weight:bold;">';
    echo '<td style="text-align:center; color:green;">S/. ' . number_format($ingresos, 2) . '</td>';
    echo '<td style="text-align:center; color:red;">S/. ' . number_format($gastos, 2) . '</td>';
    echo '<td style="text-align:center; color:blue;">S/. ' . number_format($utilidad, 2) . '</td>';
    echo '<td style="text-align:center; color:orange;">' . number_format($rentabilidadPct, 2) . '%</td>';
    echo '</tr>';
    echo '</table><br><br>';

    echo '<table border="1" style="border-collapse:collapse;">';
    echo '<tr><td colspan="4" style="text-align:center; font-size:14px; font-weight:bold;">HISTORIAL ULTIMOS 15 DIAS</td></tr>';
    echo '</table><br>';

    echo '<table border="1" style="border-collapse:collapse;">';
    echo '<tr style="background-color:#DDDDDD; font-weight:bold;">';
    echo '<th style="width:140px; text-align:center;">Dia</th>';
    echo '<th style="width:140px; text-align:center;">Ingresos S/.</th>';
    echo '<th style="width:140px; text-align:center;">Gastos S/.</th>';
    echo '<th style="width:140px; text-align:center;">Utilidad S/.</th>';
    echo '</tr>';

    foreach ($tablaDiaria as $row) {
        echo '<tr>';
        echo '<td style="text-align:center;">' . $row['fecha'] . '</td>';
        echo '<td style="text-align:right; color:green;">S/. ' . number_format($row['ingresos'], 2) . '</td>';
        echo '<td style="text-align:right; color:red;">S/. ' . number_format($row['gastos'], 2) . '</td>';
        echo '<td style="text-align:right; ' . ($row['utilidad'] < 0 ? 'color:red;' : 'color:green;') . '">S/. ' . number_format($row['utilidad'], 2) . '</td>';
        echo '</tr>';
    }

    if (count($tablaDiaria) == 0) {
        echo '<tr><td colspan="4" style="text-align:center;">No hay datos para el periodo seleccionado</td></tr>';
    }

    echo '<tr style="background-color:#EEEEEE; font-weight:bold;">';
    echo '<td style="text-align:center;">TOTALES</td>';
    echo '<td style="text-align:right;">S/. ' . number_format($totI, 2) . '</td>';
    echo '<td style="text-align:right;">S/. ' . number_format($totG, 2) . '</td>';
    echo '<td style="text-align:right; ' . ($totU < 0 ? 'color:red;' : 'color:green;') . '">S/. ' . number_format($totU, 2) . '</td>';
    echo '</tr>';
    echo '</table>';
    echo '</body></html>';
    exit;
}
