<?php
require('../../conexion.php');
session_start();

if (empty($_SESSION['active']) || $_SESSION['rol'] != 1) {
    header('Location: ../../index.php');
    exit;
}

$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$idEmpleado = isset($_GET['IdEmpleado']) ? intval($_GET['IdEmpleado']) : 0;
$exito = isset($_GET['Exito']) && $_GET['Exito'] !== '' ? intval($_GET['Exito']) : null;
$dispositivo = isset($_GET['Dispositivo']) ? trim($_GET['Dispositivo']) : '';
$fechaDesde = isset($_GET['FechaDesde']) ? trim($_GET['FechaDesde']) : '';
$fechaHasta = isset($_GET['FechaHasta']) ? trim($_GET['FechaHasta']) : '';
$nPdf = isset($_GET['nPdf']) ? $_GET['nPdf'] : 0;
$nExcel = isset($_GET['nExcel']) ? $_GET['nExcel'] : 0;

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

$sql = "SELECT a.IdAuditoria, a.FechaHora, a.IP, a.Dispositivo, a.Exito, a.MotivoFallo, a.Cod_Empleado,
               e.Nombre as NombreEmpleado, e.Usuario as UsuarioEmpleado
        FROM auditoria_login a
        LEFT JOIN empleados e ON e.IdEmpleado = a.Cod_Empleado
        " . $sqlWhere . "
        ORDER BY a.FechaHora DESC";
$query = $conexionDB->prepare($sql);
if (!empty($params)) {
    $query->bind_param($tipos, ...$params);
}
$query->execute();
$res = $query->get_result();
$filas = [];
while ($row = $res->fetch_assoc()) $filas[] = $row;

$totalExitos = 0;
$totalFallos = 0;
foreach ($filas as $f) {
    if ($f['Exito']) $totalExitos++; else $totalFallos++;
}

if ($nExcel) {
    $filename = "Auditoria_Logins_" . date('YmdHis') . ".xls";
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo '<table border="1" cellspacing="0" cellpadding="4">
        <tr>
            <th>ID</th>
            <th>Fecha y Hora</th>
            <th>Empleado</th>
            <th>Usuario</th>
            <th>IP</th>
            <th>Dispositivo</th>
            <th>Resultado</th>
            <th>Motivo / Detalle</th>
        </tr>';
    foreach ($filas as $f) {
        $nombreEmp = $f['NombreEmpleado'] ?: '-';
        $usuarioEmp = $f['UsuarioEmpleado'] ?: '-';
        $resTxt = $f['Exito'] ? 'Exito' : 'Fallo';
        $motivo = $f['MotivoFallo'] ?: '-';
        echo '<tr>
            <td>' . $f['IdAuditoria'] . '</td>
            <td>' . date('d/m/Y H:i:s', strtotime($f['FechaHora'])) . '</td>
            <td>' . $nombreEmp . '</td>
            <td>' . $usuarioEmp . '</td>
            <td>' . $f['IP'] . '</td>
            <td>' . $f['Dispositivo'] . '</td>
            <td>' . $resTxt . '</td>
            <td>' . $motivo . '</td>
        </tr>';
    }
    echo '</table>';
    echo '<br><strong>Total ingresos: ' . count($filas) . ' | Exitos: ' . $totalExitos . ' | Fallos: ' . $totalFallos . '</strong>';
    exit;
}

require('../../fpdf/fpdf.php');
$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10, iconv('UTF-8','ISO-8859-1','REPORTE DE AUDITORIA DE ACCESOS'),0,1,'C');
$pdf->SetFont('Arial','',9);
$pdf->Cell(0,8,'Fecha: '.date('d/m/Y h:i A'),0,1,'L');
$pdf->Cell(0,8, iconv('UTF-8','ISO-8859-1','Total ingresos: ' . count($filas) . ' | Exitos: ' . $totalExitos . ' | Fallos: ' . $totalFallos),0,1,'L');
$pdf->Ln(4);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(12,7,'ID',1,0,'C');
$pdf->Cell(32,7,'FechaHora',1,0,'C');
$pdf->Cell(55,7,'Empleado',1,0,'C');
$pdf->Cell(25,7,'Usuario',1,0,'C');
$pdf->Cell(30,7,'IP',1,0,'C');
$pdf->Cell(25,7,'Dispositivo',1,0,'C');
$pdf->Cell(18,7,'Resultado',1,0,'C');
$pdf->Cell(63,7,'Motivo',1,1,'C');
$pdf->SetFont('Arial','',7);
foreach ($filas as $f) {
    $nombreEmp = $f['NombreEmpleado'] ?: '-';
    $usuarioEmp = $f['UsuarioEmpleado'] ?: '-';
    $resTxt = $f['Exito'] ? 'Exito' : 'Fallo';
    $motivo = $f['MotivoFallo'] ?: '-';
    $pdf->Cell(12,6,$f['IdAuditoria'],1,0,'C');
    $pdf->Cell(32,6,date('d/m/y H:i', strtotime($f['FechaHora'])),1,0,'C');
    $pdf->Cell(55,6, iconv('UTF-8','ISO-8859-1', substr($nombreEmp,0,35)),1,0,'L');
    $pdf->Cell(25,6, iconv('UTF-8','ISO-8859-1', substr($usuarioEmp,0,15)),1,0,'L');
    $pdf->Cell(30,6,$f['IP'],1,0,'L');
    $pdf->Cell(25,6, iconv('UTF-8','ISO-8859-1', $f['Dispositivo']),1,0,'C');
    $pdf->Cell(18,6,$resTxt,1,0,'C');
    $pdf->Cell(63,6, iconv('UTF-8','ISO-8859-1', substr($motivo,0,45)),1,1,'L');
}
$pdf->Output('I','Auditoria_Logins.pdf');
?>
