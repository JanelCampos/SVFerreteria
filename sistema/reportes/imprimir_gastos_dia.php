<?php 
require('../../conexion.php');
    $busqueda = isset($_POST['busqueda']) ? '%'. $conexionDB->real_escape_string($_POST['busqueda']) . '%' : '%';
    $medioPago = isset($_POST['medioPago']) ? $_POST['medioPago'] : '';
    $tipoGasto = isset($_POST['tipoGasto']) ? $_POST['tipoGasto'] : '';

    $consulta = "
        SELECT *
        FROM gastos 
        WHERE 1=1";
    
    $tipos = "";
    $params = [];
    // Agregar condiciones de búsqueda
    if(!empty($busqueda)) {
        $consulta .= " AND (descripcion LIKE ?)";
        $tipos .= "s";
        $params[] = "%$busqueda%";
    }   

    if (!empty($medioPago)) {
        $consulta .= " AND medioPago = ?";
        $tipos .= "s";
        $params[] = $medioPago;
    }

    if (!empty($tipoGasto)) {
        $consulta .= " AND tipoGasto = ?";
        $tipos .= "s";
        $params[] = $tipoGasto;
    }
    
    $consulta .= " AND fechaGasto = CURDATE()";
    $consulta .= " ORDER BY idGastos DESC";

    $query = $conexionDB->prepare($consulta);
    $query->bind_param($tipos, ...$params);
    $query->execute();
    $result = $query->get_result();
    $row = $result->num_rows;

    if(isset($_POST['nPdf']) && $_POST['nPdf'] == 1) {
        require("../fpdf.php"); 
            header('Content-Type: text/html; charset=UTF-8');  
            $pdf= new MiPDF();
            $pdf->AliasNBPages();
                // para que tome el número de página y salga abajo en el footer
            $pdf->AddPage();
            $pdf->SetFont('Arial','B',12);
            $pdf->SetFillColor(232,232,232);
                // color del encabezado
            $pdf->Ln(10);
                // dejo un espacio de 10
            $pdf->Cell(15,6,'ID',1,0,'C',1);
            $pdf->Cell(60,6,'Descripcion',1,0,'C',1);
            $pdf->Cell(20,6,'Monto',1,0,'C',1);
            $pdf->Cell(30,6,'Fecha',1,0,'C',1);
            $pdf->Cell(30,6,'Medio Pago',1,0,'C',1);
            $pdf->Cell(30,6,'Tipo Gasto',1,1,'C',1);
            $pdf->SetFont('Arial','',10);
            while($row = $result->fetch_assoc())
            {
                $pdf->Cell(15,6,$row['idGastos'],1,0,'C');
                $pdf->Cell(60,6,$row['descripcion'],1,0,'C');
                $pdf->Cell(20,6,$row['montoGasto'],1,0,'C');
                $pdf->Cell(30,6,$row['fechaGasto'],1,0,'C');
                $pdf->Cell(30,6,$row['medioPago'],1,0,'C');
                $pdf->Cell(30,6,$row['tipoGasto'],1,1,'C');
            }
            $pdf->Output('', 'articulos_completo.pdf');
    } else if(isset($_POST['nExcel']) && $_POST['nExcel'] == 1) {
        if (isset($_POST['nExcel'])) 
        {
            header('Content-type: application/vnd.ms-excel; charset=UTF-8');
            header('Content-Disposition: attachment; filename=reporteGastos.xls');
            header('Pragma: no-cache');
            header('Expires: 0');

            if ($row > 0) {
                echo "<table border='1' style='border-collapse: collapse;'>";
                echo "<tr style='background-color: #f2f2f2; text-align: center;'>";
                echo "<th style='width:50px; border: 1px solid black;'>ID</th>";
                echo "<th style='width:400px; border: 1px solid black;'>Descripcion</th>";
                echo "<th style='width:150px; border: 1px solid black;'>Monto</th>";
                echo "<th style='width:150px; border: 1px solid black;'>Fecha</th>";
                echo "<th style='width:100px; border: 1px solid black;'>Medio Pago</th>";
                echo "<th style='width:100px; border: 1px solid black;'>Tipo Gasto</th>";
                echo "</tr>";

                while ($fila = $result->fetch_assoc()) {
                    echo "<tr style='text-align: center;'>";
                    echo "<td style='width:50px; border: 1px solid black;'>{$fila['idGastos']}</td>";
                    echo "<td style='width:400px; border: 1px solid black;'>" . mb_convert_encoding($fila['descripcion'], 'ISO-8859-1', 'UTF-8') . "</td>";
                    echo "<td style='width:150px; border: 1px solid black;'>{$fila['montoGasto']}</td>";
                    echo "<td style='width:150px; border: 1px solid black;'>{$fila['fechaGasto']}</td>";
                    echo "<td style='width:100px; border: 1px solid black;'>{$fila['medioPago']}</td>";
                    echo "<td style='width:100px; border: 1px solid black;'>{$fila['tipoGasto']}</td>";
                    echo "</tr>";
                }

                echo "</table>";
            }
        }
    }

?>