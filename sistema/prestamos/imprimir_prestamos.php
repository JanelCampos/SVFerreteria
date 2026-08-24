<?php

    // Inserto el código del archivo conxiondb_agenda que tiene la conexión a la base de datos
    require('../../conexion.php');
    $busqueda = isset($_GET['busqueda']) ? '%'. $conexionDB->real_escape_string($_GET['busqueda']) . '%' : '%';

    $consulta = "
        SELECT * 
        FROM prestamos
        WHERE 1=1";

    $tipos = "";
    $params = [];

    if (!empty($busqueda)) {
        $consulta .= " AND nombre LIKE ?";
        $tipos .= "s";
        $params[] = "%" . $busqueda . "%";
    }

    $consulta .= " ORDER BY idPrestamo DESC";

    $query = $conexionDB->prepare($consulta);

    // Vincular parámetros dinámicamente
    if (!empty($params)) {
        $query->bind_param($tipos, ...$params);
    }

    $query->execute();
    $result = $query->get_result();
    $row = $result->num_rows;

    // si el formulario ha sido enviado procesa los datos del formulario                        
    
    if (isset($_GET['nPdf'])) 
        {
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
            $pdf->Cell(10, 6, mb_convert_encoding('Id', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
            $pdf->Cell(40, 6, 'Nombre', 1, 0, 'C', 1);
            $pdf->Cell(25, 6, 'Monto', 1, 0, 'C', 1);
            $pdf->Cell(20, 6, 'Cuotas', 1, 0, 'C', 1);
            $pdf->Cell(25, 6, 'M. Cuota', 1, 0, 'C', 1);
            $pdf->Cell(25, 6, 'T. a pagar', 1, 0, 'C', 1);
            $pdf->Cell(27, 6, 'F. prestamo', 1, 0, 'C', 1);
            $pdf->Cell(18, 6, 'Estado', 1, 1, 'C', 1);

                // son los Títulos de la tabla
                //     ancho/ alto/ texto/ borde/ salto de línea/ Centrado

            if ($row > 0)
            {
                $pdf->SetFont('Arial','',10);
                    // fuente para las filas de la tabla
                $pdf->SetFillColor(255,255,255);
                    // fondo blanco para las filas de la tabla

                while ($fila = $result->fetch_assoc())
                {
                    // recorro el query imprimiendo los campos
                    $pdf->Cell(10, 6, $fila["idPrestamo"], 1, 0, 'C', 1);
                    $pdf->Cell(40, 6, mb_convert_encoding($fila["nombre"], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
                    $pdf->Cell(25, 6, 'S/. '.$fila["monto"], 1, 0, 'C', 1);
                    $pdf->Cell(20, 6, $fila["cuotas"], 1, 0, 'C', 1);
                    $pdf->Cell(25, 6, 'S/. '.$fila["montoCuota"], 1, 0, 'C', 1);
                    $pdf->Cell(25, 6, 'S/. '.$fila["montoPagar"], 1, 0, 'C', 1);
                    $pdf->Cell(27, 6, $fila["fechaPrestamo"], 1, 0, 'C', 1);
                    if($fila["estado"]){
                        $pdf->Cell(18, 6, 'Pagado', 1, 1, 'C', 1);
                    }else{
                        $pdf->Cell(18, 6, 'Pendiente', 1, 1, 'C', 1);
                    }
                }
                $pdf->Output('', 'articulos_completo.pdf');
                // acá mando la salida y con nombre por defecto como "articulos_completo.pdf"
                // primer parámetro: nada: muestra el archivo, D muestra para descargarlo
            }
            else    
                echo 'No hay artículos para mostrar';
        }else
        {
            if (isset($_GET['nExcel'])) 
            {
                header('Content-type: application/vnd.ms-excel; charset=UTF-8');
                header('Content-Disposition: attachment; filename=reportePrestamos.xls');
                header('Pragma: no-cache');
                header('Expires: 0');

                if ($row > 0) {
                    echo "<table border='1' style='border-collapse: collapse;'>";
                    echo "<tr style='background-color: #f2f2f2; text-align: center;'>";
                    echo "<th style='width:50px; border: 1px solid black;'>Id</th>";
                    echo "<th style='width:300px; border: 1px solid black;'>Nombre</th>";
                    echo "<th style='width:150px; border: 1px solid black;'>Monto</th>";
                    echo "<th style='width:50px; border: 1px solid black;'>Cuotas</th>";
                    echo "<th style='width:150px; border: 1px solid black;'>M. Cuota</th>";
                    echo "<th style='width:150px; border: 1px solid black;'>T. a pagar</th>";
                    echo "<th style='width:150px; border: 1px solid black;'>F. prestamo</th>";
                    echo "<th style='width:100px; border: 1px solid black;'>Estado</th>";
                    echo "</tr>";

                    while ($fila = $result->fetch_assoc()) {
                        echo "<tr style='text-align: center;'>";
                        echo "<td style='width:50px; border: 1px solid black;'>{$fila['idPrestamo']}</td>";
                        echo "<td style='width:300px; border: 1px solid black;'>" . mb_convert_encoding($fila['nombre'], 'ISO-8859-1', 'UTF-8') . "</td>";
                        echo "<td style='width:150px; border: 1px solid black;'>S/. {$fila['monto']}</td>";
                        echo "<td style='width:50px; border: 1px solid black;'>{$fila['cuotas']}</td>";
                        echo "<td style='width:150px; border: 1px solid black;'>S/. {$fila['montoCuota']}</td>";
                        echo "<td style='width:150px; border: 1px solid black;'>S/. {$fila['montoPagar']}</td>";
                        echo "<td style='width:150px; border: 1px solid black;'>{$fila['fechaPrestamo']}</td>";
                        if($fila['estado']){
                            echo "<td style='width:100px; border: 1px solid black;'>Pagado</td>";
                        }else{
                            echo "<td style='width:100px; border: 1px solid black;'>Pendiente</td>";
                        }
                        echo "</tr>";
                    }

                    echo "</table>";
                }
            }
        }       

?>