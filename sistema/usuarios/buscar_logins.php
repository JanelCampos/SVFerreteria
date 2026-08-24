<?php
session_start();
include "../../conexion.php";
header('Content-Type: application/json');

if (empty($_SESSION['active']) || $_SESSION['rol'] != 1) {
    echo json_encode(['resultado' => false, 'mensaje' => 'Acceso no autorizado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$busqueda = isset($input['busqueda']) ? trim($input['busqueda']) : '';
$idEmpleado = isset($input['IdEmpleado']) ? intval($input['IdEmpleado']) : 0;
$exito = isset($input['Exito']) && $input['Exito'] !== '' ? intval($input['Exito']) : null;
$dispositivo = isset($input['Dispositivo']) ? trim($input['Dispositivo']) : '';
$fechaDesde = isset($input['FechaDesde']) ? trim($input['FechaDesde']) : '';
$fechaHasta = isset($input['FechaHasta']) ? trim($input['FechaHasta']) : '';
$page = isset($input['page']) ? max(1, intval($input['page'])) : 1;
$porPagina = 10;
$offset = ($page - 1) * $porPagina;

$where = [];
$params = [];
$tipos = '';

if ($busqueda !== '') {
    $where[] = "(a.IP LIKE ? OR a.MotivoFallo LIKE ? OR e.Nombre LIKE ? OR e.Usuario LIKE ?)";
    $like = '%' . $busqueda . '%';
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $tipos .= 'ssss';
}
if ($idEmpleado > 0) {
    $where[] = "a.Cod_Empleado = ?";
    $params[] = $idEmpleado;
    $tipos .= 'i';
}
if ($exito !== null) {
    $where[] = "a.Exito = ?";
    $params[] = $exito;
    $tipos .= 'i';
}
if ($dispositivo !== '') {
    $where[] = "a.Dispositivo = ?";
    $params[] = $dispositivo;
    $tipos .= 's';
}
if ($fechaDesde !== '') {
    $where[] = "DATE(a.FechaHora) >= ?";
    $params[] = $fechaDesde;
    $tipos .= 's';
}
if ($fechaHasta !== '') {
    $where[] = "DATE(a.FechaHora) <= ?";
    $params[] = $fechaHasta;
    $tipos .= 's';
}

$sqlWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$sqlCount = "SELECT COUNT(*) as total FROM auditoria_login a LEFT JOIN empleados e ON e.IdEmpleado = a.Cod_Empleado " . $sqlWhere;
$stmtCount = $conexionDB->prepare($sqlCount);
if ($stmtCount && $params) {
    $stmtCount->bind_param($tipos, ...$params);
}
$total = 0;
if ($stmtCount) {
    $stmtCount->execute();
    $resCount = $stmtCount->get_result();
    $rowCount = $resCount->fetch_assoc();
    $total = intval($rowCount['total']);
    $stmtCount->close();
}
$totalPaginas = max(1, ceil($total / $porPagina));

$sql = "SELECT a.IdAuditoria, a.FechaHora, a.IP, a.Dispositivo, a.Exito, a.MotivoFallo, a.Cod_Empleado,
               e.Nombre as NombreEmpleado, e.Usuario as UsuarioEmpleado
        FROM auditoria_login a
        LEFT JOIN empleados e ON e.IdEmpleado = a.Cod_Empleado
        " . $sqlWhere . "
        ORDER BY a.FechaHora DESC
        LIMIT ?, ?";

$stmt = $conexionDB->prepare($sql);
if ($stmt) {
    $paramsFull = $params;
    $paramsFull[] = $offset;
    $paramsFull[] = $porPagina;
    $tiposFull = $tipos . 'ii';
    $stmt->bind_param($tiposFull, ...$paramsFull);
    $stmt->execute();
    $res = $stmt->get_result();
    $datos = [];
    while ($fila = $res->fetch_assoc()) $datos[] = $fila;
    $stmt->close();
} else {
    $datos = [];
}

$conexionDB->close();

echo json_encode([
    'resultado' => true,
    'datos' => $datos,
    'total' => $total,
    'totalPaginas' => $totalPaginas,
    'paginaActual' => $page,
    'porPagina' => $porPagina
]);
