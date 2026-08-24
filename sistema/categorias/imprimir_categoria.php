<?php
require('../../conexion.php');
session_start();

$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$nPdf = isset($_GET['nPdf']) ? $_GET['nPdf'] : 0;
$nExcel = isset($_GET['nExcel']) ? $_GET['nExcel'] : 0;

$tipos = "";
$params = [];
$where = " WHERE Estado = 1";
if (!empty($busqueda)) {
    $where .= " AND (Nombre LIKE ? OR Descripcion LIKE ?)";
    $tipos .= "ss";
    $params[] = "%" . $busqueda . "%";
    $params[] = "%" . $busqueda . "%";
}

$sql = "SELECT c.IdCategoria, c.Nombre, c.Descripcion, c.FechaCreacion,
               (SELECT COUNT(*) FROM articulos a WHERE a.Cod_Categoria = c.IdCategoria) as CantArticulos
        FROM categorias c
        $where
        ORDER BY c.Nombre ASC";
$query = $conexionDB->prepare($sql);
if (!empty($params)) {
    $query->bind_param($tipos, ...$params);
}
$query->execute();
$res = $query->get_result();
$filas = [];
while ($row = $res->fetch_assoc()) {
    $filas[] = $row;
}

if ($nExcel) {
    $filename = "Reporte_Categorias_" . date('YmdHis') . ".xls";
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo '<table border="1" cellspacing="0" cellpadding="4">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripcion</th>
            <th>Cantidad articulos</th>
            <th>Fecha de creacion</th>
        </tr>';
    foreach ($filas as $f) {
        echo '<tr>
            <td>' . $f['IdCategoria'] . '</td>
            <td>' . $f['Nombre'] . '</td>
            <td>' . ($f['Descripcion'] ?: '-') . '</td>
            <td>' . $f['CantArticulos'] . '</td>
            <td>' . date('d/m/Y', strtotime($f['FechaCreacion'])) . '</td>
        </tr>';
    }
    echo "</table>";
    exit;
}

require('../../fpdf/fpdf.php');
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10, iconv('UTF-8','ISO-8859-1','REPORTE DE CATEGORIAS'),0,1,'C');
$pdf->SetFont('Arial','',9);
$pdf->Cell(0,8,'Fecha: '.date('d/m/Y h:i A'),0,1,'L');
$pdf->Ln(4);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(15,8,'ID',1,0,'C');
$pdf->Cell(60,8,'Nombre',1,0,'C');
$pdf->Cell(70,8,'Descripcion',1,0,'C');
$pdf->Cell(25,8,'Articulos',1,0,'C');
$pdf->Cell(20,8,'Creada',1,1,'C');
$pdf->SetFont('Arial','',8);
foreach ($filas as $f) {
    $pdf->Cell(15,7,$f['IdCategoria'],1,0,'C');
    $pdf->Cell(60,7,iconv('UTF-8','ISO-8859-1',$f['Nombre']),1,0,'L');
    $pdf->Cell(70,7,iconv('UTF-8','ISO-8859-1',$f['Descripcion'] ?: '-'),1,0,'L');
    $pdf->Cell(25,7,$f['CantArticulos'],1,0,'C');
    $pdf->Cell(20,7,date('d/m/Y', strtotime($f['FechaCreacion'])),1,1,'C');
}
$pdf->Output('I','Reporte_Categorias.pdf');
?>
