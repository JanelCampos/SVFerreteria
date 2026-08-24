<?php
include "../../conexion.php";
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$fechaDesde = isset($input['fechaDesde']) ? trim($input['fechaDesde']) : date('Y-m-01');
$fechaHasta = isset($input['fechaHasta']) ? trim($input['fechaHasta']) : date('Y-m-d');
$IdVendedor = isset($input['IdVendedor']) ? trim($input['IdVendedor']) : '';

if ($fechaDesde > $fechaHasta) {
    $tmp = $fechaDesde;
    $fechaDesde = $fechaHasta;
    $fechaHasta = $tmp;
}

$advertencia = '';

$columnCheck = $conexionDB->query("SHOW COLUMNS FROM ventas LIKE 'Cod_Vendedor'");
$tieneCodVendedor = $columnCheck && $columnCheck->num_rows > 0;

$utilidadCheck = $conexionDB->query("SHOW COLUMNS FROM ventas LIKE 'utilidad'");
$tieneUtilidad = $utilidadCheck && $utilidadCheck->num_rows > 0;

$resumenPorVendedor = [];
$totalGeneral = 0;

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
    $advertencia = 'Columna Cod_Vendedor no encontrada en ventas, usando relación por Caja.';
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
    $vendedorInt = $IdVendedor === '' ? '' : (int)$IdVendedor;
    if ($IdVendedor === '') {
        $stmtTop->bind_param('ssii', $fechaDesde, $fechaHasta, $IdVendedor, $IdVendedor);
    } else {
        $stmtTop->bind_param('ssii', $fechaDesde, $fechaHasta, $IdVendedor, $vendedorInt);
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
    $vendedorInt = $IdVendedor === '' ? '' : (int)$IdVendedor;
    if ($IdVendedor === '') {
        $stmtTop->bind_param('ssii', $fechaDesde, $fechaHasta, $IdVendedor, $IdVendedor);
    } else {
        $stmtTop->bind_param('ssii', $fechaDesde, $fechaHasta, $IdVendedor, $vendedorInt);
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

$chartLabels = [];
$chartData = [];
foreach ($resumenPorVendedor as $r) {
    $chartLabels[] = $r['NombreEmpleado'];
    $chartData[] = round((float)$r['TotalVenta'], 2);
}

echo json_encode([
    'resultado' => true,
    'resumenPorVendedor' => $resumenPorVendedor,
    'topProductos' => $topProductos,
    'chartLabels' => $chartLabels,
    'chartData' => $chartData,
    'totalGeneral' => round($totalGeneral, 2),
    'advertencia' => $advertencia
], JSON_UNESCAPED_UNICODE);

$conexionDB->close();
