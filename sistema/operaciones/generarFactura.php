<?php

	require('../../conexion.php');
	require('../plantillaFactura.php');
	header('Content-Type: text/html; charset=UTF-8');
	
	$noFactura = $_REQUEST['f']; 
	$pdf = new facturaPDF();

    // agregar una página
    $pdf->AddPage();

	$pdf->SetFillColor(210,210,210);
	$pdf->SetFont('Arial','B',10);
	
    // son los Títulos de la tabla
    $pdf->Cell(114,8,mb_convert_encoding('Producto', 'ISO-8859-1', 'UTF-8'),1,0,'C',1);
    $pdf->Cell(25,8,'Precio',1,0,'C',1);
    $pdf->Cell(25,8,'Cantidad',1,0,'C',1);
    $pdf->Cell(25,8,'Sub total',1,1,'C',1);


	$sqlp = "SELECT dva.Cod_articulo,dva.nombreArticulo, dva.Cantidad, dva.precio_venta, 
					(dva.Cantidad * dva.precio_venta) as precio_total, v.Total,
                    (v.efectivo + v.tarjeta) as importeTotal, v.saldo, v.vuelto
                    FROM ventas v
                    INNER JOIN detalle_venta_articulos dva ON dva.Cod_Venta = v.IdVenta
					WHERE v.IdVenta = $noFactura";
	$queryprod = $conexionDB->query($sqlp);

    $query_venta = $conexionDB->prepare("
        SELECT saldo,vuelto, (efectivo + tarjeta) as importeTotal
        FROM ventas
        WHERE IdVenta = ?
    ");
    $query_venta->bind_param("i",$noFactura);
    if($query_venta->execute()){
        $resultVenta = $query_venta->get_result();
        $dataVenta = $resultVenta->fetch_assoc();
        $saldo = $dataVenta['saldo'];
        $vuelto = $dataVenta['vuelto'];
        $importeTotal = $dataVenta['importeTotal'];
    }
    
    if ($queryprod->num_rows > 0) {
		// FACTURACIÓN INFERIOR PARA VENTA DE ARTICULOS
        $pdf->SetFont('Arial','',9);
        // fuente para las filas de la tabla
        $pdf->SetFillColor(255,255,255);
        // fondo blanco para las filas de la tabla

        while ($fila = $queryprod->fetch_assoc()) {
            $pdf->Cell(114,6,mb_convert_encoding($fila["nombreArticulo"], 'ISO-8859-1', 'UTF-8'),1,0,'L',1);
            $pdf->Cell(25,6,'S/. '.$fila["precio_venta"],1,0,'C',1);
            $pdf->Cell(25,6,$fila["Cantidad"],1,0,'C',1);
            $pdf->Cell(25,6,'S/. '.$fila["precio_total"],1,1,'C',1);
            $total = $fila["Total"];
        }
        $pdf->Ln(105);
        $pdf->SetFont('Arial','B',10);
		$pdf->Cell(136);
        $pdf->SetFillColor(210,210,210);
        $pdf->Cell(25,6,'Total',1,0,'C',1);
		$pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(25,6,'S/.'.$total,1,1,'C',1);
        if($vuelto > 0){
            $pdf->Cell(136);
            $pdf->SetFillColor(210,210,210);
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(25,6,'vuelto',1,0,'C',1);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(25,6,'S/.'.$vuelto,1,0,'C',1);
        }
        if($saldo > 0){
            $pdf->Cell(136);
            $pdf->SetFillColor(210,210,210);
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(25,6,'Saldo',1,0,'C',1);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(25,6,'S/.'.$saldo,1,0,'C',1);
        }
        
        $pdf->Output('', 'Ticket de venta Nro '.$noFactura.'.pdf');
    } 
    
?>