<?php
session_start();
require '../../conexion.php';

if (empty($_SESSION['active'])) {
    header('Location: ../../index.php');
    exit;
}

$busqueda = isset($_GET['busqueda']) ? trim((string)$_GET['busqueda']) : '';
$IdEmpleado = isset($_GET['IdEmpleado']) && $_GET['IdEmpleado'] !== '' ? intval($_GET['IdEmpleado']) : 0;
$FechaDesde = isset($_GET['FechaDesde']) ? trim((string)$_GET['FechaDesde']) : '';
$FechaHasta = isset($_GET['FechaHasta']) ? trim((string)$_GET['FechaHasta']) : '';

$where = [];
$params = [];
$tipos = '';

if ($FechaDesde !== '') {
    $where[] = "DATE(c.Fecha) >= ?";
    $params[] = $FechaDesde;
    $tipos .= 's';
}
if ($FechaHasta !== '') {
    $where[] = "DATE(c.Fecha) <= ?";
    $params[] = $FechaHasta;
    $tipos .= 's';
}
if ($IdEmpleado > 0) {
    $where[] = "c.Cod_Empleado = ?";
    $params[] = $IdEmpleado;
    $tipos .= 'i';
}
if ($busqueda !== '') {
    $where[] = "(c.IdCotizacion LIKE ? OR cl.Nombre LIKE ? OR cl.Dni LIKE ? OR c.Observaciones LIKE ?)";
    $like = '%' . $busqueda . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $tipos .= 'ssss';
}

$sqlWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT c.IdCotizacion, 
               DATE(c.Fecha) as Fecha, 
               cl.Nombre as NombreCliente, 
               cl.Dni as DniCliente, 
               e.Nombre as NombreEmpleado, 
               c.Total,  
               c.VigenciaHasta 
        FROM cotizaciones c 
        LEFT JOIN clientes cl ON cl.Id_Cliente = c.Cod_Cliente 
        LEFT JOIN empleados e ON e.IdEmpleado = c.Cod_Empleado 
        " . $sqlWhere . " 
        ORDER BY c.Fecha DESC, c.IdCotizacion DESC";

$stmt = $conexionDB->prepare($sql);
$rows = [];
if ($stmt) {
    if ($params) {
        $stmt->bind_param($tipos, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($fila = $res->fetch_assoc()) $rows[] = $fila;
    $stmt->close();
}

$totalGeneral = 0;
foreach ($rows as $r) {
    $totalGeneral += floatval($r['Total']);
}

if (isset($_GET['nPdf'])) {
    require("../fpdf.php");
    header('Content-Type: text/html; charset=UTF-8');

    $pdf = new MiPDF();
    $pdf->AliasNBPages();
    $pdf->AddPage('L');
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetFillColor(232, 232, 232);
    $pdf->Ln(4);
    $pdf->Cell(0, 8, mb_convert_encoding('LISTADO DE COTIZACIONES', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
    if ($FechaDesde || $FechaHasta) {
        $pdf->SetFont('Arial', '', 9);
        $rango = 'Rango: ' . ($FechaDesde ? $FechaDesde : '—') . ' hasta ' . ($FechaHasta ? $FechaHasta : '—');
        $pdf->Cell(0, 6, mb_convert_encoding($rango, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
    }
    $pdf->Ln(4);

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(18, 7, mb_convert_encoding('N°', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->Cell(29, 7, 'Fecha', 1, 0, 'C', 1);
    $pdf->Cell(80, 7, 'Cliente', 1, 0, 'C', 1);
    $pdf->Cell(65, 7, 'Vendedor', 1, 0, 'C', 1);
    $pdf->Cell(28, 7, 'Total', 1, 0, 'C', 1);
    $pdf->Cell(28, 7, 'Vigencia', 1, 1, 'C', 1);

    $pdf->SetFont('Arial', '', 8);
    foreach ($rows as $fila) {
        $nomCli = $fila['NombreCliente'] ? $fila['NombreCliente'] : '—';
        if ($fila['DniCliente']) $nomCli .= ' (' . $fila['DniCliente'] . ')';
        $nomEmp = $fila['NombreEmpleado'] ? $fila['NombreEmpleado'] : '—';
        $vigencia = $fila['VigenciaHasta'] ? $fila['VigenciaHasta'] : '—';

        $pdf->Cell(18, 6, '#' . $fila['IdCotizacion'], 1, 0, 'C', 1);
        $pdf->Cell(29, 6, $fila['Fecha'], 1, 0, 'C', 1);
        $pdf->Cell(80, 6, mb_convert_encoding($nomCli, 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', 1);
        $pdf->Cell(65, 6, mb_convert_encoding($nomEmp, 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', 1);
        $pdf->Cell(28, 6, 'S/. ' . number_format((float)$fila['Total'], 2), 1, 0, 'R', 1);
        $pdf->Cell(28, 6, $vigencia, 1, 1, 'C', 1);
    }

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(210, 230, 255);
    $pdf->Cell(166, 8, 'TOTAL GENERAL', 1, 0, 'R', 1);
    $pdf->Cell(82, 8, 'S/. ' . number_format($totalGeneral, 2), 1, 1, 'R', 1);

    $pdf->Output('', 'lista_cotizaciones.pdf');
    $conexionDB->close();
    exit;
}

if (isset($_GET['nExcel'])) {
    header('Content-type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename=lista_cotizaciones.xls');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th colspan='7' style='font-size:16px; background-color:#e6f3ff;'>LISTADO DE COTIZACIONES</th></tr>";
    if ($FechaDesde || $FechaHasta) {
        echo "<tr><th colspan='7' style='background-color:#f5f5f5;'>Rango: " . htmlspecialchars(($FechaDesde ?: '—') . ' hasta ' . ($FechaHasta ?: '—')) . "</th></tr>";
    }
    echo "<tr style='background-color: #d9e2f3; text-align: center; font-weight:bold;'>";
    echo "<th>N°</th>";
    echo "<th>Fecha</th>";
    echo "<th>Cliente</th>";
    echo "<th>Vendedor</th>";
    echo "<th>Total S/.</th>";
    echo "<th>Vigencia</th>";
    echo "</tr>";

    foreach ($rows as $fila) {
        $nomCli = $fila['NombreCliente'] ? htmlspecialchars($fila['NombreCliente']) : '—';
        if ($fila['DniCliente']) $nomCli .= ' (' . htmlspecialchars($fila['DniCliente']) . ')';
        $nomEmp = $fila['NombreEmpleado'] ? htmlspecialchars($fila['NombreEmpleado']) : '—';
        $vigencia = $fila['VigenciaHasta'] ? htmlspecialchars($fila['VigenciaHasta']) : '—';

        echo "<tr style='text-align: center;'>";
        echo "<td>#" . $fila['IdCotizacion'] . "</td>";
        echo "<td>" . htmlspecialchars($fila['Fecha']) . "</td>";
        echo "<td style='text-align:left;'>" . $nomCli . "</td>";
        echo "<td style='text-align:left;'>" . $nomEmp . "</td>";
        echo "<td style='text-align:right;'>S/. " . number_format((float)$fila['Total'], 2) . "</td>";
        echo "<td>" . $vigencia . "</td>";
        echo "</tr>";
    }

    echo "<tr style='background-color: #d9e2f3; font-weight:bold;'>";
    echo "<td colspan='4' style='text-align:right;'>TOTAL GENERAL</td>";
    echo "<td style='text-align:right;'>S/. " . number_format($totalGeneral, 2) . "</td>";
    echo "<td colspan='2'></td>";
    echo "</tr>";
    echo "</table>";
}

$conexionDB->close();
