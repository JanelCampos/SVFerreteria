<?php
include "../../conexion.php";
session_start();
if (empty($_SESSION['active']) || $_SESSION['rol'] != 1) {
    header('location: ../');
}

$fechaDesde = isset($_GET['fechaDesde']) ? trim($_GET['fechaDesde']) : date('Y-m-01');
$fechaHasta = isset($_GET['fechaHasta']) ? trim($_GET['fechaHasta']) : date('Y-m-d');
$IdVendedor = isset($_GET['IdVendedor']) ? trim($_GET['IdVendedor']) : '';
$nPdf = isset($_GET['nPdf']) ? (int)$_GET['nPdf'] : 0;
$nExcel = isset($_GET['nExcel']) ? (int)$_GET['nExcel'] : 0;

if ($fechaDesde > $fechaHasta) {
    $tmp = $fechaDesde;
    $fechaDesde = $fechaHasta;
    $fechaHasta = $tmp;
}

$columnCheck = $conexionDB->query("SHOW COLUMNS FROM ventas LIKE 'Cod_Vendedor'");
$tieneCodVendedor = $columnCheck && $columnCheck->num_rows > 0;

$utilidadCheck = $conexionDB->query("SHOW COLUMNS FROM ventas LIKE 'utilidad'");
$tieneUtilidad = $utilidadCheck && $utilidadCheck->num_rows > 0;

$resumenPorVendedor = [];
$totalGeneral = 0;
$totalNVentas = 0;
$totalUtilidad = 0;

if ($tieneCodVendedor) {
    if ($tieneUtilidad) {
        $sqlResumen = "SELECT e.Nombre AS NombreEmpleado, e.IdEmpleado,
            COUNT(v.IdVenta) AS NVentas,
            COALESCE(SUM(v.Total), 0) AS TotalVenta,
            COALESCE(SUM(v.utilidad), 0) AS Utilidad
            FROM ventas v
            INNER JOIN empleados e ON e.IdEmpleado = v.Cod_Vendedor
            WHERE DATE(v.Fecha) BETWEEN ? AND ?
            AND LOWER(COALESCE(v.Estado, '')) <> 'anulado'
            AND (? = '' OR v.Cod_Vendedor = ?)
            GROUP BY v.Cod_Vendedor, e.Nombre, e.IdEmpleado
            ORDER BY TotalVenta DESC";
    } else {
        $sqlResumen = "SELECT e.Nombre AS NombreEmpleado, e.IdEmpleado,
            COUNT(DISTINCT v.IdVenta) AS NVentas,
            COALESCE(SUM(v.Total), 0) AS TotalVenta,
            COALESCE(SUM(((CASE WHEN d.PrecioConDescuento > 0 THEN d.PrecioConDescuento ELSE d.Precio_Venta END) - d.Precio_Compra) * d.Cantidad), 0) AS Utilidad
            FROM ventas v
            INNER JOIN empleados e ON e.IdEmpleado = v.Cod_Vendedor
            LEFT JOIN detalle_venta_articulos d ON d.Cod_Venta = v.IdVenta
            WHERE DATE(v.Fecha) BETWEEN ? AND ?
            AND LOWER(COALESCE(v.Estado, '')) <> 'anulado'
            AND (? = '' OR v.Cod_Vendedor = ?)
            GROUP BY v.Cod_Vendedor, e.Nombre, e.IdEmpleado
            ORDER BY TotalVenta DESC";
    }
    $stmt = $conexionDB->prepare($sqlResumen);
    $stmt->bind_param('ssii', $fechaDesde, $fechaHasta, $IdVendedor, $IdVendedor);
} else {
    if ($tieneUtilidad) {
        $sqlResumen = "SELECT e.Nombre AS NombreEmpleado, e.IdEmpleado,
            COUNT(v.IdVenta) AS NVentas,
            COALESCE(SUM(v.Total), 0) AS TotalVenta,
            COALESCE(SUM(v.utilidad), 0) AS Utilidad
            FROM ventas v
            INNER JOIN caja ca ON ca.IdCaja = v.Cod_Caja
            INNER JOIN empleados e ON e.IdEmpleado = ca.Cod_Empleado
            WHERE DATE(v.Fecha) BETWEEN ? AND ?
            AND LOWER(COALESCE(v.Estado, '')) <> 'anulado'
            AND (? = '' OR ca.Cod_Empleado = ?)
            GROUP BY e.IdEmpleado, e.Nombre
            ORDER BY TotalVenta DESC";
    } else {
        $sqlResumen = "SELECT e.Nombre AS NombreEmpleado, e.IdEmpleado,
            COUNT(DISTINCT v.IdVenta) AS NVentas,
            COALESCE(SUM(v.Total), 0) AS TotalVenta,
            COALESCE(SUM(((CASE WHEN d.PrecioConDescuento > 0 THEN d.PrecioConDescuento ELSE d.Precio_Venta END) - d.Precio_Compra) * d.Cantidad), 0) AS Utilidad
            FROM ventas v
            INNER JOIN caja ca ON ca.IdCaja = v.Cod_Caja
            INNER JOIN empleados e ON e.IdEmpleado = ca.Cod_Empleado
            LEFT JOIN detalle_venta_articulos d ON d.Cod_Venta = v.IdVenta
            WHERE DATE(v.Fecha) BETWEEN ? AND ?
            AND LOWER(COALESCE(v.Estado, '')) <> 'anulado'
            AND (? = '' OR ca.Cod_Empleado = ?)
            GROUP BY e.IdEmpleado, e.Nombre
            ORDER BY TotalVenta DESC";
    }
    $stmt = $conexionDB->prepare($sqlResumen);
    $stmt->bind_param('ssii', $fechaDesde, $fechaHasta, $IdVendedor, $IdVendedor);
}

$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $resumenPorVendedor[] = [
        'NombreEmpleado' => $row['NombreEmpleado'],
        'IdEmpleado' => $row['IdEmpleado'],
        'NVentas' => (int)$row['NVentas'],
        'TotalVenta' => (float)$row['TotalVenta'],
        'Utilidad' => (float)$row['Utilidad']
    ];
    $totalGeneral += (float)$row['TotalVenta'];
    $totalNVentas += (int)$row['NVentas'];
    $totalUtilidad += (float)$row['Utilidad'];
}
$stmt->close();

$topProductos = [];

if ($tieneCodVendedor) {
    $sqlTop = "SELECT a.Nombre,
        COALESCE(c.Nombre, '-') AS NombreCategoria,
        COALESCE(SUM(d.Cantidad), 0) AS Cantidad,
        COALESCE(SUM((CASE WHEN d.PrecioConDescuento > 0 THEN d.PrecioConDescuento ELSE d.Precio_Venta END) * d.Cantidad), 0) AS Importe
        FROM detalle_venta_articulos d
        INNER JOIN articulos a ON a.IdArticulo = d.Cod_Articulo
        LEFT JOIN categorias c ON c.IdCategoria = a.Cod_Categoria
        INNER JOIN ventas v ON v.IdVenta = d.Cod_Venta
        WHERE DATE(v.Fecha) BETWEEN ? AND ?
        AND LOWER(COALESCE(v.Estado, '')) <> 'anulado'
        AND (? = '' OR v.Cod_Vendedor = ?)
        GROUP BY d.Cod_Articulo, a.Nombre, c.Nombre
        ORDER BY SUM(d.Cantidad) DESC
        LIMIT 10";
    $stmtTop = $conexionDB->prepare($sqlTop);
    if ($IdVendedor === '') {
        $stmtTop->bind_param('ssii', $fechaDesde, $fechaHasta, $IdVendedor, $IdVendedor);
    } else {
        $vInt = (int)$IdVendedor;
        $stmtTop->bind_param('ssii', $fechaDesde, $fechaHasta, $IdVendedor, $vInt);
    }
} else {
    $sqlTop = "SELECT a.Nombre,
        COALESCE(c.Nombre, '-') AS NombreCategoria,
        COALESCE(SUM(d.Cantidad), 0) AS Cantidad,
        COALESCE(SUM((CASE WHEN d.PrecioConDescuento > 0 THEN d.PrecioConDescuento ELSE d.Precio_Venta END) * d.Cantidad), 0) AS Importe
        FROM detalle_venta_articulos d
        INNER JOIN articulos a ON a.IdArticulo = d.Cod_Articulo
        LEFT JOIN categorias c ON c.IdCategoria = a.Cod_Categoria
        INNER JOIN ventas v ON v.IdVenta = d.Cod_Venta
        INNER JOIN caja ca ON ca.IdCaja = v.Cod_Caja
        WHERE DATE(v.Fecha) BETWEEN ? AND ?
        AND LOWER(COALESCE(v.Estado, '')) <> 'anulado'
        AND (? = '' OR ca.Cod_Empleado = ?)
        GROUP BY d.Cod_Articulo, a.Nombre, c.Nombre
        ORDER BY SUM(d.Cantidad) DESC
        LIMIT 10";
    $stmtTop = $conexionDB->prepare($sqlTop);
    if ($IdVendedor === '') {
        $stmtTop->bind_param('ssii', $fechaDesde, $fechaHasta, $IdVendedor, $IdVendedor);
    } else {
        $vInt = (int)$IdVendedor;
        $stmtTop->bind_param('ssii', $fechaDesde, $fechaHasta, $IdVendedor, $vInt);
    }
}

$stmtTop->execute();
$resultTop = $stmtTop->get_result();
while ($row = $resultTop->fetch_assoc()) {
    $topProductos[] = [
        'Nombre' => $row['Nombre'],
        'NombreCategoria' => $row['NombreCategoria'],
        'Cantidad' => (int)$row['Cantidad'],
        'Importe' => (float)$row['Importe']
    ];
}
$stmtTop->close();
$conexionDB->close();

if ($nPdf == 1) {
    require("../../fpdf/fpdf.php");

    class MiPDF extends FPDF
    {
        function Header()
        {
            $this->Image('../../img/pachacutec.png', 15, 5, 25);
            $this->SetFont('Arial', 'B', 14);
            $this->Cell(30);
            $this->Cell(230, 10, mb_convert_encoding('FERRETERIA PACHACUTEC', 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
            $this->Ln(8);
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(30);
            $this->Cell(230, 8, mb_convert_encoding('REPORTE DE VENTAS POR VENDEDOR', 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
            $this->Ln(6);
            $this->SetFont('Arial', '', 9);
            $this->Cell(30);
            $this->Cell(230, 6, mb_convert_encoding('Periodo: ' . $GLOBALS['fechaDesde'] . ' al ' . $GLOBALS['fechaHasta'], 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
            $this->Ln(12);
        }

        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, mb_convert_encoding('Pagina ', 'ISO-8859-1', 'UTF-8') . $this->PageNo() . ' / {nb}', 0, 0, 'C');
        }
    }

    $pdf = new MiPDF('L', 'mm', 'A4');
    $pdf->AliasNBPages();
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(80, 8, mb_convert_encoding('Vendedor', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->Cell(30, 8, mb_convert_encoding('N° Ventas', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->Cell(40, 8, mb_convert_encoding('Total S/.', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->Cell(40, 8, mb_convert_encoding('Utilidad S/.', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->Cell(40, 8, mb_convert_encoding('% Particip', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', 1);

    $pdf->SetFont('Arial', '', 9);
    foreach ($resumenPorVendedor as $r) {
        $particip = $totalGeneral > 0 ? round((($r['TotalVenta'] / $totalGeneral) * 100), 2) : 0;
        $pdf->Cell(80, 7, mb_convert_encoding($r['NombreEmpleado'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
        $pdf->Cell(30, 7, $r['NVentas'], 1, 0, 'C');
        $pdf->Cell(40, 7, 'S/. ' . number_format($r['TotalVenta'], 2), 1, 0, 'R');
        $pdf->Cell(40, 7, 'S/. ' . number_format($r['Utilidad'], 2), 1, 0, 'R');
        $pdf->Cell(40, 7, $particip . '%', 1, 1, 'C');
    }

    if (count($resumenPorVendedor) == 0) {
        $pdf->Cell(230, 7, mb_convert_encoding('No hay datos para el periodo seleccionado', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
    }

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(80, 8, mb_convert_encoding('TOTALES', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->Cell(30, 8, $totalNVentas, 1, 0, 'C', 1);
    $pdf->Cell(40, 8, 'S/. ' . number_format($totalGeneral, 2), 1, 0, 'R', 1);
    $pdf->Cell(40, 8, 'S/. ' . number_format($totalUtilidad, 2), 1, 0, 'R', 1);
    $pdf->Cell(40, 8, '100.00%', 1, 1, 'C', 1);

    $pdf->Ln(8);
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, mb_convert_encoding('TOP 10 PRODUCTOS MAS VENDIDOS', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
    $pdf->Ln(4);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(18, 8, mb_convert_encoding('Pos', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->Cell(100, 8, mb_convert_encoding('Producto', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->Cell(52, 8, mb_convert_encoding('Categoria', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->Cell(30, 8, mb_convert_encoding('Cantidad', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->Cell(40, 8, mb_convert_encoding('Importe S/.', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', 1);

    $pdf->SetFont('Arial', '', 9);
    $pos = 1;
    $totCant = 0;
    $totImp = 0;
    foreach ($topProductos as $p) {
        $pdf->Cell(18, 7, $pos, 1, 0, 'C');
        $pdf->Cell(100, 7, mb_convert_encoding($p['Nombre'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
        $pdf->Cell(52, 7, mb_convert_encoding($p['NombreCategoria'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
        $pdf->Cell(30, 7, $p['Cantidad'], 1, 0, 'C');
        $pdf->Cell(40, 7, 'S/. ' . number_format($p['Importe'], 2), 1, 1, 'R');
        $totCant += $p['Cantidad'];
        $totImp += $p['Importe'];
        $pos++;
    }

    if (count($topProductos) == 0) {
        $pdf->Cell(240, 7, mb_convert_encoding('No hay productos vendidos en el periodo', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
    }

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(18, 8, '', 1, 0, 'C', 1);
    $pdf->Cell(100, 8, mb_convert_encoding('TOTALES', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
    $pdf->Cell(52, 8, '', 1, 0, 'C', 1);
    $pdf->Cell(30, 8, $totCant, 1, 0, 'C', 1);
    $pdf->Cell(40, 8, 'S/. ' . number_format($totImp, 2), 1, 1, 'R', 1);

    $pdf->Output('I', 'Reporte_ventas_por_vendedor.pdf');
    exit;
}

if ($nExcel == 1) {
    header('Content-type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename=Reporte_ventas_por_vendedor.xls');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>';

    echo '<table border="1" style="border-collapse:collapse;">';
    echo '<tr><td colspan="5" style="text-align:center; font-size:16px; font-weight:bold;">FERRETERIA PACHACUTEC</td></tr>';
    echo '<tr><td colspan="5" style="text-align:center; font-size:14px; font-weight:bold;">REPORTE DE VENTAS POR VENDEDOR</td></tr>';
    echo '<tr><td colspan="5" style="text-align:center;">Periodo: ' . $fechaDesde . ' al ' . $fechaHasta . '</td></tr>';
    echo '</table><br>';

    echo '<table border="1" style="border-collapse:collapse;">';
    echo '<tr style="background-color:#DDDDDD; font-weight:bold;">';
    echo '<th style="width:200px; text-align:center;">Vendedor</th>';
    echo '<th style="width:80px; text-align:center;">N° Ventas</th>';
    echo '<th style="width:100px; text-align:center;">Total S/.</th>';
    echo '<th style="width:100px; text-align:center;">Utilidad S/.</th>';
    echo '<th style="width:80px; text-align:center;">% Particip.</th>';
    echo '</tr>';

    foreach ($resumenPorVendedor as $r) {
        $particip = $totalGeneral > 0 ? round((($r['TotalVenta'] / $totalGeneral) * 100), 2) : 0;
        echo '<tr>';
        echo '<td>' . htmlspecialchars($r['NombreEmpleado']) . '</td>';
        echo '<td style="text-align:center;">' . $r['NVentas'] . '</td>';
        echo '<td style="text-align:right;">S/. ' . number_format($r['TotalVenta'], 2) . '</td>';
        echo '<td style="text-align:right;">S/. ' . number_format($r['Utilidad'], 2) . '</td>';
        echo '<td style="text-align:center;">' . $particip . '%</td>';
        echo '</tr>';
    }

    if (count($resumenPorVendedor) == 0) {
        echo '<tr><td colspan="5" style="text-align:center;">No hay datos para el periodo seleccionado</td></tr>';
    }

    echo '<tr style="background-color:#EEEEEE; font-weight:bold;">';
    echo '<td>TOTALES</td>';
    echo '<td style="text-align:center;">' . $totalNVentas . '</td>';
    echo '<td style="text-align:right;">S/. ' . number_format($totalGeneral, 2) . '</td>';
    echo '<td style="text-align:right;">S/. ' . number_format($totalUtilidad, 2) . '</td>';
    echo '<td style="text-align:center;">100.00%</td>';
    echo '</tr>';
    echo '</table><br><br>';

    echo '<table border="1" style="border-collapse:collapse;">';
    echo '<tr><td colspan="5" style="text-align:center; font-size:14px; font-weight:bold;">TOP 10 PRODUCTOS MAS VENDIDOS</td></tr>';
    echo '</table><br>';

    echo '<table border="1" style="border-collapse:collapse;">';
    echo '<tr style="background-color:#DDDDDD; font-weight:bold;">';
    echo '<th style="width:40px; text-align:center;">Pos</th>';
    echo '<th style="width:300px; text-align:center;">Producto</th>';
    echo '<th style="width:150px; text-align:center;">Categoria</th>';
    echo '<th style="width:80px; text-align:center;">Cantidad</th>';
    echo '<th style="width:100px; text-align:center;">Importe S/.</th>';
    echo '</tr>';

    $pos = 1;
    $totCant = 0;
    $totImp = 0;
    foreach ($topProductos as $p) {
        echo '<tr>';
        echo '<td style="text-align:center;">' . $pos . '</td>';
        echo '<td>' . htmlspecialchars($p['Nombre']) . '</td>';
        echo '<td>' . htmlspecialchars($p['NombreCategoria']) . '</td>';
        echo '<td style="text-align:center;">' . $p['Cantidad'] . '</td>';
        echo '<td style="text-align:right;">S/. ' . number_format($p['Importe'], 2) . '</td>';
        echo '</tr>';
        $totCant += $p['Cantidad'];
        $totImp += $p['Importe'];
        $pos++;
    }

    if (count($topProductos) == 0) {
        echo '<tr><td colspan="5" style="text-align:center;">No hay productos vendidos en el periodo</td></tr>';
    }

    echo '<tr style="background-color:#EEEEEE; font-weight:bold;">';
    echo '<td colspan="3" style="text-align:center;">TOTALES</td>';
    echo '<td style="text-align:center;">' . $totCant . '</td>';
    echo '<td style="text-align:right;">S/. ' . number_format($totImp, 2) . '</td>';
    echo '</tr>';
    echo '</table>';
    echo '</body></html>';
    exit;
}
