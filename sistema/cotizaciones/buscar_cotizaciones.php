<?php
session_start();
require '../../conexion.php';
header('Content-Type: application/json; charset=UTF-8');

if (empty($_SESSION['active'])) {
    echo json_encode(['resultado' => false, 'mensaje' => 'Sesión no válida']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$busqueda = isset($input['busqueda']) ? trim((string)$input['busqueda']) : '';
$IdEmpleado = isset($input['IdEmpleado']) && $input['IdEmpleado'] !== '' ? intval($input['IdEmpleado']) : 0;
$FechaDesde = isset($input['FechaDesde']) ? trim((string)$input['FechaDesde']) : '';
$FechaHasta = isset($input['FechaHasta']) ? trim((string)$input['FechaHasta']) : '';
$page = isset($input['page']) ? max(1, intval($input['page'])) : 1;
$porPagina = 15;
$offset = ($page - 1) * $porPagina;

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

$sqlCount = "SELECT COUNT(*) as total 
             FROM cotizaciones c 
             LEFT JOIN clientes cl ON cl.Id_Cliente = c.Cod_Cliente 
             LEFT JOIN empleados e ON e.IdEmpleado = c.Cod_Empleado 
             " . $sqlWhere;

$stmtCount = $conexionDB->prepare($sqlCount);
$total = 0;
if ($stmtCount) {
    if ($params) {
        $stmtCount->bind_param($tipos, ...$params);
    }
    $stmtCount->execute();
    $resCount = $stmtCount->get_result();
    $rowCount = $resCount->fetch_assoc();
    $total = intval($rowCount['total']);
    $stmtCount->close();
}
$totalPaginas = max(1, ceil($total / $porPagina));

$sql = "SELECT c.IdCotizacion, 
               DATE(c.Fecha) as Fecha, 
               cl.Nombre as NombreCliente, 
               cl.Dni as DniCliente, 
               e.Nombre as NombreEmpleado, 
               c.Total,  
               c.VigenciaHasta, 
               c.Observaciones 
        FROM cotizaciones c 
        LEFT JOIN clientes cl ON cl.Id_Cliente = c.Cod_Cliente 
        LEFT JOIN empleados e ON e.IdEmpleado = c.Cod_Empleado 
        " . $sqlWhere . " 
        ORDER BY c.Fecha DESC, c.IdCotizacion DESC 
        LIMIT ?, ?";

$stmt = $conexionDB->prepare($sql);
$datos = [];
if ($stmt) {
    $paramsFull = $params;
    $paramsFull[] = $offset;
    $paramsFull[] = $porPagina;
    $tiposFull = $tipos . 'ii';
    $stmt->bind_param($tiposFull, ...$paramsFull);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($fila = $res->fetch_assoc()) {
        $datos[] = [
            'IdCotizacion' => intval($fila['IdCotizacion']),
            'Fecha' => $fila['Fecha'],
            'NombreCliente' => $fila['NombreCliente'],
            'DniCliente' => $fila['DniCliente'],
            'NombreEmpleado' => $fila['NombreEmpleado'],
            'Total' => floatval($fila['Total']),
            'VigenciaHasta' => $fila['VigenciaHasta'],
            'Observaciones' => $fila['Observaciones']
        ];
    }
    $stmt->close();
}

$conexionDB->close();

echo json_encode([
    'resultado' => true,
    'datos' => $datos,
    'total' => $total,
    'totalPaginas' => $totalPaginas,
    'paginaActual' => $page,
    'porPagina' => $porPagina
], JSON_UNESCAPED_UNICODE);
