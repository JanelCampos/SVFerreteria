<?php
require('../../conexion.php');
session_start();

$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$IdProveedor = isset($_GET['IdProveedor']) ? $_GET['IdProveedor'] : '';
$IdCategoria = isset($_GET['IdCategoria']) ? $_GET['IdCategoria'] : '';
$stock = isset($_GET['stock']) ? $_GET['stock'] : '';

$nPdf = isset($_GET['nPdf']) ? $_GET['nPdf'] : 0;
$nExcel = isset($_GET['nExcel']) ? $_GET['nExcel'] : 0;

$tipos = "";
$params = [];
$where = " WHERE 1=1";
if (!empty($busqueda)) {
    $where .= " AND (a.codigoBarras LIKE ? OR a.Nombre LIKE ?)";
    $tipos .= "ss";
    $params[] = "%" . $busqueda . "%";
    $params[] = "%" . $busqueda . "%";
}
if (!empty($IdProveedor)) {
    $where .= " AND a.Cod_Proveedor = ?";
    $tipos .= "i";
    $params[] = $IdProveedor;
}
if (!empty($IdCategoria)) {
    $where .= " AND a.Cod_Categoria = ?";
    $tipos .= "i";
    $params[] = $IdCategoria;
}
if (!empty($stock)) {
    if($stock == 'sinStock'){
        $where .= " AND a.Cantidad = 0";
    }else if($stock == 'pocoStock'){
        $where .= " AND a.Cantidad > 0 AND a.Cantidad <= a.Stock_Alerta";
    }else if($stock == 'conStock'){
        $where .= " AND a.Cantidad > a.Stock_Alerta";
    }
}

$sql = "
    SELECT a.IdArticulo, a.codigoBarras, a.Nombre, a.Cantidad, a.Stock_Alerta, a.Precio_Compra, a.Precio_Unitario,
           a.Precio_Minimo, a.Unidad_Presentacion, p.Nombre as nombreProveedor, c.Nombre as nombreCategoria
    FROM articulos a
    INNER JOIN proveedores p ON p.IdProveedor = a.Cod_Proveedor
    LEFT JOIN categorias c ON c.IdCategoria = a.Cod_Categoria
    $where
    ORDER BY a.IdArticulo DESC
";
$query = $conexionDB->prepare($sql);
if (!empty($params)) {
    $query->bind_param($tipos, ...$params);
}
$query->execute();
$res = $query->get_result();

$totalCompra = 0;
$totalVenta = 0;
$totalMinimo = 0;
$filas = [];
while ($row = $res->fetch_assoc()) {
    $totalCompra += $row['Cantidad'] * $row['Precio_Compra'];
    $totalVenta += $row['Cantidad'] * $row['Precio_Unitario'];
    $totalMinimo += $row['Cantidad'] * $row['Precio_Minimo'];
    $filas[] = $row;
}

if ($nExcel) {
    $filename = "Reporte_Articulos_" . date('YmdHis') . ".xls";
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo '<table border="1" cellspacing="0" cellpadding="4">
        <tr>
            <th>Codigo</th>
            <th>Codigo Barras</th>
            <th>Nombre</th>
            <th>Categoria</th>
            <th>Cantidad</th>
            <th>Stock Alerta</th>
            <th>Unidad</th>
            <th>Precio Compra</th>
            <th>Precio Venta</th>
            <th>Precio Minimo</th>
            <th>Proveedor</th>
        </tr>';
    foreach ($filas as $f) {
        echo '<tr>
            <td>' . $f['IdArticulo'] . '</td>
            <td>' . $f['codigoBarras'] . '</td>
            <td>' . $f['Nombre'] . '</td>
            <td>' . ($f['nombreCategoria'] ?: 'Sin asignar') . '</td>
            <td>' . $f['Cantidad'] . '</td>
            <td>' . $f['Stock_Alerta'] . '</td>
            <td>' . $f['Unidad_Presentacion'] . '</td>
            <td>' . $f['Precio_Compra'] . '</td>
            <td>' . $f['Precio_Unitario'] . '</td>
            <td>' . $f['Precio_Minimo'] . '</td>
            <td>' . $f['nombreProveedor'] . '</td>
        </tr>';
    }
    echo "</table>";
    exit;
}

require('../../fpdf/fpdf.php');
$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,10, iconv('UTF-8','ISO-8859-1','REPORTE DE ARTICULOS FERRETERIA'),0,1,'C');
$pdf->SetFont('Arial','',8);
$pdf->Cell(30,6,'Fecha de generacion: '.date('d/m/Y h:i A'),0,1,'L');
$pdf->Ln(6);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(15,7,'Codigo',1,0,'C');
$pdf->Cell(25,7,'Cod. Barras',1,0,'C');
$pdf->Cell(60,7,'Nombre',1,0,'C');
$pdf->Cell(30,7,'Categoria',1,0,'C');
$pdf->Cell(15,7,'Cant.',1,0,'C');
$pdf->Cell(15,7,'Alerta',1,0,'C');
$pdf->Cell(18,7,'Unidad',1,0,'C');
$pdf->Cell(20,7,'P. Compra',1,0,'C');
$pdf->Cell(20,7,'P. Venta',1,0,'C');
$pdf->Cell(20,7,'P. Minimo',1,0,'C');
$pdf->Cell(40,7,'Proveedor',1,1,'C');
$pdf->SetFont('Arial','',7);
foreach ($filas as $f) {
    $pdf->Cell(15,6,$f['IdArticulo'],1,0,'C');
    $pdf->Cell(25,6,iconv('UTF-8','ISO-8859-1',$f['codigoBarras']),1,0,'C');
    $pdf->Cell(60,6,iconv('UTF-8','ISO-8859-1',$f['Nombre']),1,0,'L');
    $pdf->Cell(30,6,iconv('UTF-8','ISO-8859-1',$f['nombreCategoria'] ?: 'Sin asignar'),1,0,'L');
    $pdf->Cell(15,6,$f['Cantidad'],1,0,'C');
    $pdf->Cell(15,6,$f['Stock_Alerta'],1,0,'C');
    $pdf->Cell(18,6,iconv('UTF-8','ISO-8859-1',$f['Unidad_Presentacion']),1,0,'C');
    $pdf->Cell(20,6,number_format($f['Precio_Compra'],2),1,0,'R');
    $pdf->Cell(20,6,number_format($f['Precio_Unitario'],2),1,0,'R');
    $pdf->Cell(20,6,number_format($f['Precio_Minimo'],2),1,0,'R');
    $pdf->Cell(40,6,iconv('UTF-8','ISO-8859-1',$f['nombreProveedor']),1,1,'L');
}
$pdf->Ln(4);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(178,6,'TOTALES (inventario):',1,0,'R');
$pdf->Cell(20,6,number_format($totalCompra,2),1,0,'R');
$pdf->Cell(20,6,number_format($totalVenta,2),1,0,'R');
$pdf->Cell(20,6,number_format($totalMinimo,2),1,1,'R');
$pdf->Output('I','Reporte_Articulos.pdf');
?>
