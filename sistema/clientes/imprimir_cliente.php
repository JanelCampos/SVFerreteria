<?php
require('../../conexion.php');
require_once __DIR__ . "/../includes/analytics.php";

$filters = analyticsGetDateFilters($_GET);
$busqueda = isset($_GET['busqueda']) ? trim((string)$_GET['busqueda']) : '';
$filtrosVarios = isset($_GET['filtrosVarios']) ? trim((string)$_GET['filtrosVarios']) : '';

$clientData = analyticsGetClientsListData(
    $conexionDB,
    $filters,
    $busqueda,
    $filtrosVarios,
    1,
    5000
);

$rows = $clientData['rows'];
$periodLabel = $filters['label'];

if (isset($_GET['nPdf'])) {
    require("../fpdf.php");
    header('Content-Type: text/html; charset=UTF-8');

    $pdf = new MiPDF();
    $pdf->AliasNBPages();
    $pdf->AddPage('L');
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetFillColor(232, 232, 232);
    $pdf->Ln(8);
    $pdf->Cell(0, 8, mb_convert_encoding('Reporte de clientes', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
    $pdf->Cell(0, 8, mb_convert_encoding('Periodo: ' . $periodLabel, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
    $pdf->Ln(4);
    $pdf->Cell(28, 7, 'DNI', 1, 0, 'C', 1);
    $pdf->Cell(58, 7, 'Nombre', 1, 0, 'C', 1);
    $pdf->Cell(30, 7, 'Telefono', 1, 0, 'C', 1);
    $pdf->Cell(40, 7, 'Registro', 1, 0, 'C', 1);
    $pdf->Cell(28, 7, 'Compras', 1, 0, 'C', 1);
    $pdf->Cell(38, 7, 'Monto', 1, 0, 'C', 1);
    $pdf->Cell(38, 7, 'Utilidad', 1, 1, 'C', 1);

    $pdf->SetFont('Arial', '', 8);
    foreach ($rows as $fila) {
        $pdf->Cell(28, 6, $fila["Dni"], 1, 0, 'C', 1);
        $pdf->Cell(58, 6, mb_convert_encoding($fila["Nombre"], 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', 1);
        $pdf->Cell(30, 6, (string)$fila["Telefono"], 1, 0, 'C', 1);
        $pdf->Cell(40, 6, $fila["Fecha_Registro"], 1, 0, 'C', 1);
        $pdf->Cell(28, 6, $fila["cantidadCompras"], 1, 0, 'C', 1);
        $pdf->Cell(38, 6, 'S/. ' . number_format((float)$fila["montoCompras"], 2), 1, 0, 'R', 1);
        $pdf->Cell(38, 6, 'S/. ' . number_format((float)$fila["gananciaGenerada"], 2), 1, 1, 'R', 1);
    }

    $pdf->Output('', 'reporte_clientes.pdf');
    exit;
}

if (isset($_GET['nExcel'])) {
    header('Content-type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename=reporteClientes.xls');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th colspan='7' style='font-size:16px;'>Reporte de clientes</th></tr>";
    echo "<tr><th colspan='7'>Periodo: " . htmlspecialchars($periodLabel) . "</th></tr>";
    echo "<tr style='background-color: #f2f2f2; text-align: center;'>";
    echo "<th>DNI</th>";
    echo "<th>Nombre</th>";
    echo "<th>Telefono</th>";
    echo "<th>Fecha de registro</th>";
    echo "<th>Cantidad de compras</th>";
    echo "<th>Monto de compras</th>";
    echo "<th>Utilidad generada</th>";
    echo "</tr>";

    foreach ($rows as $fila) {
        echo "<tr style='text-align: center;'>";
        echo "<td>{$fila['Dni']}</td>";
        echo "<td>" . mb_convert_encoding($fila['Nombre'], 'ISO-8859-1', 'UTF-8') . "</td>";
        echo "<td>{$fila['Telefono']}</td>";
        echo "<td>{$fila['Fecha_Registro']}</td>";
        echo "<td>{$fila['cantidadCompras']}</td>";
        echo "<td>S/. " . number_format((float)$fila['montoCompras'], 2) . "</td>";
        echo "<td>S/. " . number_format((float)$fila['gananciaGenerada'], 2) . "</td>";
        echo "</tr>";
    }

    echo "</table>";
}
