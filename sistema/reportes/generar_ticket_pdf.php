<?php
require('../fpdf.php');
include 'obtener_datos_venta.php';

// Configuración de impresión para ancho 58mm
$pageWidth = 48;          // ancho físico de la impresora en mm
$leftMargin = 2;          // ajustar si la impresora requiere más margen
$rightMargin = 2;
$printableWidth = $pageWidth - $leftMargin - $rightMargin;

// Calcular altura dinámica aproximada
$approxHeader = 60;
$approxFooter = 120;
$lineHeight = 5;
$linesItems = 0;

if (isset($_GET['idVenta'])) {
    $idVenta = intval($_GET['idVenta']);
    $datosVenta = obtenerDatosVenta($idVenta);
    $saldo = $datosVenta[0]['saldo'];
    $vuelto = $datosVenta[0]['vuelto'];

    if (empty($datosVenta)) {
        echo "No se encontraron datos para la venta.";
        exit;
    }

    $height = $approxHeader + $approxFooter + ($linesItems * $lineHeight);
    $height = MAX(180, $height);

    // Crear PDF
    $pdf = new FPDF('P', 'mm', array($pageWidth, $height));
    $pdf->SetMargins($leftMargin, 2, $rightMargin);
    $pdf->SetAutoPageBreak(false);
    $pdf->AddPage();

    //imagen
    $pdf->Image('../../img/logo_ferreteria.png',2,4,9);

    // Fuentes y tamaños
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($printableWidth, 6, utf8_decode('Ticket de Venta'), 0, 1, 'C');
    $pdf->Cell($printableWidth, 7, 'USOL', 0, 1, 'C');

    $pdf->Image('../../img/logo_ferreteria.png',37,4,9);
    
    $pdf->SetFont('Arial', 'I', 7);
    $pdf->Cell($printableWidth, 5, 'Av. San Martin con Jr. El Manzano', 0, 1, 'C');
    $pdf->Cell($printableWidth, 5, 'Cel: 980349451', 0, 1, 'C');
    $pdf->Ln(1);
    $pdf->Line($leftMargin, $pdf->GetY(), $pageWidth - $rightMargin, $pdf->GetY());
    $pdf->Ln(3);

    // Información de la venta
    $pdf->SetFont('Arial', '', 7);
    $fecha = isset($datosVenta[0]['Fecha']) ? utf8_decode($datosVenta[0]['Fecha']) : '';
    $cliente = isset($datosVenta[0]['cliente']) ? utf8_decode($datosVenta[0]['cliente']) : '';
    $vendedor = isset($datosVenta[0]['vendedor']) ? utf8_decode($datosVenta[0]['vendedor']) : '';

    $pdf->Cell($printableWidth, 4, 'Fecha: ' . $fecha, 0, 1);
    $pdf->Cell($printableWidth, 4, 'Cliente: ' . $cliente, 0, 1);
    $pdf->Cell($printableWidth, 4, 'Vendedor: ' . $vendedor, 0, 1);
    
    $pdf->Ln(4);

    // Encabezados de la tabla de items
    $pdf->SetFont('Arial', 'B', 8);

    // Reservar anchos: cantidad y precio fijos, resto para nombre
    $qtyWidth = 9;
    $dsctWidth = 9;                 // ancho en mm para descuento
    $priceWidth = 9;               // ancho en mm para precio
    $nameWidth = $printableWidth - $qtyWidth - $dsctWidth - $priceWidth;

    if ($nameWidth < 10) {
        // ajuste mínimo si no hay suficiente espacio
        $qtyWidth = 6;
        $dsctWidth = 6;                 // ancho en mm para unidad
        $priceWidth = 6;
        $nameWidth = $printableWidth - $qtyWidth - $priceWidth - $dsctWidth;
    }

    $pdf->Cell($nameWidth, 5, utf8_decode('Articulo'), 0, 0, 'L');
    $pdf->Cell($qtyWidth, 5, 'Cant', 0, 0, 'L');
    $pdf->Cell($dsctWidth, 5, 'Dsct', 0, 0, 'L');
    $pdf->Cell($priceWidth, 5, 'Precio', 0, 1, 'R');
    
    $pdf->SetFont('Arial', '', 7);

    foreach ($datosVenta as $item) {
        $nombre = isset($item['Nombre']) ? utf8_decode($item['Nombre']) : '';
        $cantidad = isset($item['Cantidad']) ? $item['Cantidad'] : '';
        $unidad = isset($item['Unidad']) ? $item['Unidad'] : '';
        $porcentajeDescuento = isset($item['porcentajeDescuento']) ? $item['porcentajeDescuento'] : '';
        $precioVenta = floatval($item['precio_venta']);
        if($cantidad > 0 && $cantidad < 1){
            $precioVenta = $item['totalVentaArticulo'];
        }

        if($porcentajeDescuento > 0){
            $precioVenta = $item['precioConDescuento'];
        }

        // Posición inicial antes del MultiCell
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        $pdf->MultiCell($nameWidth, 4, $nombre, 0, 'L');

        // Posición después del MultiCell
        $yAfter = $pdf->GetY();

        // Colocar cantidad y precio en la primera línea del bloque del nombre
        $pdf->SetXY($x + $nameWidth, $y);
        $pdf->Cell($qtyWidth, 4, number_format($cantidad, 1) . ' ' . $unidad, 0, 0, 'R');
        $pdf->Cell($dsctWidth, 4, number_format($porcentajeDescuento, 1) . '%', 0, 0, 'R');
        $pdf->Cell($priceWidth, 4, number_format($precioVenta, 2), 0, 1,'R');

        // Mover el cursor a la línea más baja usada (si el nombre ocupó más de una línea)
        $pdf->SetY(max($yAfter, $pdf->GetY()));
    }

    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'B', 9);
    $total = number_format($datosVenta[0]['Total'], 2);
    $pdf->Cell($printableWidth, 6, 'Total: S/. ' . number_format($total, 2), 0, 1, 'R');
    
    if($saldo > 0 ){
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($printableWidth, 5, 'Saldo: S/. ' . number_format($saldo, 2), 0, 1, 'R');
    }
    if($vuelto > 0){
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($printableWidth, 5, 'Vuelto: S/. ' . number_format($vuelto, 2), 0, 1, 'R');
    }

    // Mensajes finales
    $pdf->Ln(6);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($printableWidth, 5, utf8_decode('Gracias por su compra!!'), 0, 1, 'C');
    
    $pdf->Output('I', 'ticket.pdf');
} else {
    echo "No se proporcionó un ID de venta.";
}
?>



