<?php
    session_start();
    include "../../conexion.php";
    date_default_timezone_set('America/Lima');

    if (empty($_SESSION['active'])) {
        header('location: ../');
        exit;
    }

    $idCotizacion = isset($_GET['idCotizacion']) ? intval($_GET['idCotizacion']) : 0;
    $nPdf = isset($_GET['nPdf']) ? intval($_GET['nPdf']) : 0;
    $nExcel = isset($_GET['nExcel']) ? intval($_GET['nExcel']) : 0;

    if ($idCotizacion <= 0) {
        die("Cotización no encontrada");
    }

    $cotizacion = null;
    $cliente = null;
    $empleado = null;
    $detalle = [];

    $tablaClientes = 'clientes';
    $colCliId = 'Id_Cliente';
    $colCliDni = 'Dni';
    $colCliNombre = 'Nombre';
    $colCliDir = 'direccion';
    $colCliTel = 'Telefono';

    $qCheck = $conexionDB->prepare("SELECT 1 FROM clientes LIMIT 1");
    if (!$qCheck) {
        $tablaClientes = 'cliente';
        $colCliId = 'IdCliente';
        $colCliDni = 'dniCliente';
        $colCliNombre = 'nombreCliente';
        $colCliDir = 'direccionCliente';
        $colCliTel = 'telefonoCliente';
    } else {
        $qCheck->close();
    }

    $sqlCot = "SELECT c.*, 
                    cl.$colCliDni AS DniCliente, cl.$colCliNombre AS NombreCliente, 
                    cl.$colCliDir AS DireccionCliente, cl.$colCliTel AS TelefonoCliente,
                    e.Nombre AS NombreEmpleado
               FROM cotizaciones c
               LEFT JOIN $tablaClientes cl ON c.Cod_Cliente = cl.$colCliId
               LEFT JOIN empleados e ON c.Cod_Empleado = e.IdEmpleado
               WHERE c.IdCotizacion = ? LIMIT 1";
    $queryCot = $conexionDB->prepare($sqlCot);
    if ($queryCot) {
        $queryCot->bind_param("i", $idCotizacion);
        if ($queryCot->execute()) {
            $res = $queryCot->get_result();
            if ($res->num_rows > 0) {
                $cotizacion = $res->fetch_assoc();
            }
        }
        $queryCot->close();
    }

    if (!$cotizacion) {
        die("Cotización no encontrada");
    }

    $sqlDet = "SELECT * FROM detalle_cotizacion WHERE Cod_Cotizacion = ? ORDER BY IdDetalle ASC";
    $queryDet = $conexionDB->prepare($sqlDet);
    if ($queryDet) {
        $queryDet->bind_param("i", $idCotizacion);
        if ($queryDet->execute()) {
            $resDet = $queryDet->get_result();
            while ($row = $resDet->fetch_assoc()) {
                $detalle[] = $row;
            }
        }
        $queryDet->close();
    }

    if ($nExcel == 1) {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=cotizacion_' . str_pad($idCotizacion, 4, '0', STR_PAD_LEFT) . '.xls');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        </head>
        <body>';

        echo '<table border="1" cellpadding="3" cellspacing="0">';
        echo '<tr><td colspan="9" align="center" style="font-size:16pt;font-weight:bold;">' . 'Ferretería USOL' . '</td></tr>';
        echo '<tr><td colspan="9" align="center">' . 'Av. San Martin con Jr. El Manzano | Tel: 980349451' . '</td></tr>';
        echo '<tr><td colspan="9" align="center" style="font-size:14pt;font-weight:bold;">' . 'COTIZACIÓN N° ' . str_pad($idCotizacion, 4, '0', STR_PAD_LEFT) . '</td></tr>';
        echo '<tr><td colspan="9">&nbsp;</td></tr>';

        echo '<tr><td colspan="4"><strong>Fecha emisión:</strong></td><td colspan="5">' . date('d/m/Y H:i', strtotime($cotizacion['Fecha'])) . '</td></tr>';
        echo '<tr><td colspan="4"><strong>Cliente:</strong></td><td colspan="5">' . htmlspecialchars($cotizacion['NombreCliente']) . '</td></tr>';
        echo '<tr><td colspan="4"><strong>DNI:</strong></td><td colspan="5">' . htmlspecialchars($cotizacion['DniCliente']) . '</td></tr>';
        echo '<tr><td colspan="4"><strong>Dirección:</strong></td><td colspan="5">' . htmlspecialchars($cotizacion['DireccionCliente']) . '</td></tr>';
        echo '<tr><td colspan="4"><strong>Teléfono:</strong></td><td colspan="5">' . htmlspecialchars($cotizacion['TelefonoCliente']) . '</td></tr>';
        if (!empty($cotizacion['VigenciaHasta'])) {
            echo '<tr><td colspan="4"><strong>Vigencia hasta:</strong></td><td colspan="5">' . date('d/m/Y', strtotime($cotizacion['VigenciaHasta'])) . '</td></tr>';
        }
        echo '<tr><td colspan="4"><strong>Vendedor:</strong></td><td colspan="5">' . htmlspecialchars($cotizacion['NombreEmpleado']) . '</td></tr>';
        echo '<tr><td colspan="9">&nbsp;</td></tr>';

        echo '<tr style="background-color:#f0f0f0;font-weight:bold;">
                <th>Item</th>
                <th>Código</th>
                <th>Artículo</th>
                <th>Cant</th>
                <th>UdM</th>
                <th>P.Unit</th>
                <th>Dto%</th>
                <th>P.C/Des</th>
                <th>SubTotal</th>
              </tr>';

        $item = 1;
        $total = 0;
        foreach ($detalle as $d) {
            $cant = floatval($d['Cantidad']);
            $pUnit = floatval($d['PrecioUnitario']);
            $pDto = floatval($d['PorcentajeDescuento']);
            $pCDes = floatval($d['PrecioConDescuento']);
            $sub = floatval($d['SubTotal']);
            $total += $sub;

            echo '<tr>';
            echo '<td align="center">' . $item . '</td>';
            echo '<td align="center">' . htmlspecialchars($d['Cod_Articulo']) . '</td>';
            echo '<td>' . htmlspecialchars($d['NombreArticulo']) . '</td>';
            echo '<td align="right">' . number_format($cant, 2) . '</td>';
            echo '<td>' . htmlspecialchars($d['Unidad']) . '</td>';
            echo '<td align="right">S/. ' . number_format($pUnit, 2) . '</td>';
            echo '<td align="right">' . number_format($pDto, 2) . '%</td>';
            echo '<td align="right">S/. ' . number_format($pCDes, 2) . '</td>';
            echo '<td align="right">S/. ' . number_format($sub, 2) . '</td>';
            echo '</tr>';
            $item++;
        }

        echo '<tr><td colspan="8" align="right" style="font-weight:bold;">SubTotal:</td><td align="right" style="font-weight:bold;">S/. ' . number_format(floatval($cotizacion['SubTotal']), 2) . '</td></tr>';
        echo '<tr><td colspan="8" align="right" style="font-weight:bold;font-size:12pt;">Total:</td><td align="right" style="font-weight:bold;font-size:12pt;">S/. ' . number_format(floatval($cotizacion['Total']), 2) . '</td></tr>';

        if (!empty($cotizacion['Observaciones'])) {
            echo '<tr><td colspan="9">&nbsp;</td></tr>';
            echo '<tr><td colspan="9"><strong>Observaciones:</strong><br>' . nl2br(htmlspecialchars($cotizacion['Observaciones'])) . '</td></tr>';
        }

        echo '</table>';
        echo '</body></html>';
        exit;
    }

    if ($nPdf == 1) {
        $fpdfPath = '../../fpdf/fpdf.php';
        if (!file_exists($fpdfPath)) {
            $fpdfPath = '../fpdf.php';
        }
        if (!file_exists($fpdfPath)) {
            die("Librería FPDF no encontrada en: " . $fpdfPath);
        }
        require $fpdfPath;

        class PDF extends FPDF
        {
            function Header()
            {
                $this->SetFont('Arial', 'B', 16);
                $this->Cell(0, 10, utf8_decode('Ferretería USOL'), 0, 1, 'C');
                $this->SetFont('Arial', '', 9);
                $this->Cell(0, 5, utf8_decode('Av. San Martin con Jr. El Manzano'), 0, 1, 'C');
                $this->Cell(0, 5, 'Tel: 980349451', 0, 1, 'C');
                $this->SetDrawColor(0, 0, 0);
                $this->Line(10, $this->GetY() + 2, $this->GetPageWidth() - 10, $this->GetY() + 2);
                $this->Ln(6);
            }

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
                $this->Ln(4);
                $this->Cell(0, 10, 'Generado: ' . date('d/m/Y H:i:s'), 0, 0, 'R');
            }

            function TableHeader($headers, $widths, $aligns)
            {
                $this->SetFont('Arial', 'B', 9);
                $this->SetFillColor(220, 220, 220);
                for ($i = 0; $i < count($headers); $i++) {
                    $this->Cell($widths[$i], 7, utf8_decode($headers[$i]), 1, 0, $aligns[$i], true);
                }
                $this->Ln();
            }

            function TableRow($data, $widths, $aligns, $fill = false)
            {
                $this->SetFont('Arial', '', 9);
                if ($fill) $this->SetFillColor(245, 245, 245);
                else $this->SetFillColor(255, 255, 255);
                for ($i = 0; $i < count($data); $i++) {
                    $this->Cell($widths[$i], 6, utf8_decode($data[$i]), 1, 0, $aligns[$i], true);
                }
                $this->Ln();
            }
        }

        $pdf = new PDF('L', 'mm', 'A4');
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetMargins(10, 10, 10);

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetFillColor(0, 51, 102);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, utf8_decode('COTIZACION N° ' . str_pad($idCotizacion, 4, '0', STR_PAD_LEFT)), 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(3);

        $pdf->SetFont('Arial', '', 9);
        $wColLbl = 30;
        $wColVal = 65;
        $y0 = $pdf->GetY();

        $pdf->Cell($wColLbl, 6, 'Fecha emision:', 0, 0);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($wColVal, 6, date('d/m/Y H:i', strtotime($cotizacion['Fecha'])), 0, 1);
        $pdf->SetFont('Arial', '', 9);

        $pdf->Cell($wColLbl, 6, 'Cliente:', 0, 0);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($wColVal, 6, utf8_decode($cotizacion['NombreCliente']), 0, 1);
        $pdf->SetFont('Arial', '', 9);

        $pdf->Cell($wColLbl, 6, 'DNI:', 0, 0);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($wColVal, 6, $cotizacion['DniCliente'], 0, 1);
        $pdf->SetFont('Arial', '', 9);

        $pdf->Cell($wColLbl, 6, utf8_decode('Dirección:'), 0, 0);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($wColVal, 6, utf8_decode($cotizacion['DireccionCliente']), 0, 1);
        $pdf->SetFont('Arial', '', 9);

        $pdf->Cell($wColLbl, 6, utf8_decode('Teléfono:'), 0, 0);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($wColVal, 6, $cotizacion['TelefonoCliente'], 0, 1);
        $pdf->SetFont('Arial', '', 9);

        $xRight = 140;
        $y1 = $pdf->GetY();
        $pdf->SetXY($xRight, $y0);

        $pdf->Cell($wColLbl, 6, 'Vigencia hasta:', 0, 0);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($wColVal, 6, !empty($cotizacion['VigenciaHasta']) ? date('d/m/Y', strtotime($cotizacion['VigenciaHasta'])) : '-', 0, 1);
        $pdf->SetX($xRight);
        $pdf->SetFont('Arial', '', 9);

        $pdf->Cell($wColLbl, 6, 'Vendedor:', 0, 0);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($wColVal, 6, utf8_decode($cotizacion['NombreEmpleado']), 0, 1);

        if ($pdf->GetY() < $y1) $pdf->SetY($y1);
        $pdf->Ln(4);

        $headers = ['Item', 'Codigo', 'Articulo', 'Cant', 'UdM', 'P.Unit', 'Dto%', 'P.C/Des', 'SubTotal'];
        $widths = [13, 19, 110, 19, 25, 23, 17, 25, 25];
        $aligns = ['C', 'C', 'L', 'R', 'C', 'R', 'R', 'R', 'R'];
        $pdf->TableHeader($headers, $widths, $aligns);

        $item = 1;
        $fill = false;
        foreach ($detalle as $d) {
            $cant = floatval($d['Cantidad']);
            $pUnit = floatval($d['PrecioUnitario']);
            $pDto = floatval($d['PorcentajeDescuento']);
            $pCDes = floatval($d['PrecioConDescuento']);
            $sub = floatval($d['SubTotal']);

            $pdf->TableRow([
                $item,
                $d['Cod_Articulo'],
                $d['NombreArticulo'],
                number_format($cant, 2),
                $d['Unidad'],
                'S/. ' . number_format($pUnit, 2),
                number_format($pDto, 2) . '%',
                'S/. ' . number_format($pCDes, 2),
                'S/. ' . number_format($sub, 2)
            ], $widths, $aligns, $fill);
            $fill = !$fill;
            $item++;
        }

        $pdf->Ln(2);
        $wTotalLabel = $widths[0] + $widths[1] + $widths[2] + $widths[3] + $widths[4] + $widths[5] + $widths[6] + $widths[7];
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($wTotalLabel, 7, 'SUBTOTAL:', 1, 0, 'R');
        $pdf->Cell($widths[8], 7, 'S/. ' . number_format(floatval($cotizacion['SubTotal']), 2), 1, 1, 'R');

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetFillColor(0, 51, 102);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell($wTotalLabel, 8, 'TOTAL:', 1, 0, 'R', true);
        $pdf->Cell($widths[8], 8, 'S/. ' . number_format(floatval($cotizacion['Total']), 2), 1, 1, 'R', true);
        $pdf->SetTextColor(0, 0, 0);

        if (!empty($cotizacion['Observaciones'])) {
            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 7, 'OBSERVACIONES:', 0, 1);
            $pdf->SetFont('Arial', '', 9);
            $pdf->MultiCell(0, 5, utf8_decode($cotizacion['Observaciones']), 1);
        }

        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 5, utf8_decode('Documento generado por sistema SVPachacutec - Válido con firma del autorizado'), 0, 1, 'C');

        $pdf->Output('I', 'cotizacion_' . str_pad($idCotizacion, 4, '0', STR_PAD_LEFT) . '.pdf');
        exit;
    }

    die("Cotización no encontrada");
?>
