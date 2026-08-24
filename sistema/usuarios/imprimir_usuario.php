<?php

    // Inserto el código del archivo conxiondb_agenda que tiene la conexión a la base de datos
    require('../../conexion.php');

    $busqueda = isset($_GET['busqueda']) ? '%'. $conexionDB->real_escape_string($_GET['busqueda']) . '%' : '%';

    $consulta = "
        SELECT *
        FROM empleados
        WHERE 1=1";
    
    $tipos = "";
    $params = [];

    if (!empty($busqueda)) {
        $consulta .= " AND (Nombre LIKE ? OR Dni LIKE ?)";
        $tipos .= "ss";
        $params[] = "%" . $busqueda . "%";
        $params[] = "%" . $busqueda . "%";
    }
    
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
            $pdf= new MiPDF();
            $pdf->AliasNBPages();
                // para que tome el número de página y salga abajo en el footer
            $pdf->AddPage();
            $pdf->SetFont('Arial','B',11);
            $pdf->SetFillColor(232,232,232);
                // color del encabezado
            $pdf->Ln(10);
                // dejo un espacio de 10
            $pdf->Cell(10, 6, 'Id', 1, 0, 'C', 1);
            $pdf->Cell(40, 6, 'Nombre', 1, 0, 'C', 1);
            $pdf->Cell(20, 6, 'Dni', 1, 0, 'C', 1);
            $pdf->Cell(30, 6, 'Direccion', 1, 0, 'C', 1);
            $pdf->Cell(20, 6, 'Telefono', 1, 0, 'C', 1);
            $pdf->Cell(50, 6, 'Email', 1, 0, 'C', 1);
            $pdf->Cell(20, 6, 'Usuario', 1, 1, 'C', 1);
                // son los Títulos de la tabla
                //     ancho/ alto/ texto/ borde/ salto de línea/ Centrado

            
            if ($row > 0)
            {
                $pdf->SetFont('Arial','',8);
                    // fuente para las filas de la tabla
                $pdf->SetFillColor(255,255,255);
                    // fondo blanco para las filas de la tabla

                while ($fila = $result->fetch_assoc())
                {
                    // recorro el query imprimiendo los campos
                    $pdf->Cell(10, 6, $fila["IdEmpleado"], 1, 0, 'C', 1);
                    $pdf->Cell(40, 6, mb_convert_encoding($fila["Nombre"], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
                    $pdf->Cell(20, 6, $fila["Dni"], 1, 0, 'C', 1);
                    $pdf->Cell(30, 6, mb_convert_encoding($fila["Direccion"], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
                    $pdf->Cell(20, 6, $fila["Telefono"], 1, 0, 'C', 1);
                    $pdf->Cell(50, 6, $fila["Email"], 1, 0, 'C', 1);
                    $pdf->Cell(20, 6, $fila["Usuario"], 1, 1, 'C', 1);
                }
                $pdf->Output('', 'articulos_completo.pdf');
                // acá mando la salida y con nombre por defecto como "articulos_completo.pdf"
                // primer parámetro: nada: muestra el archivo, D muestra para descargarlo
            }
            else    
                echo 'No hay artículos para mostrar';
        }
        else
        {
            if (isset($_GET['nExcel'])) 
            {
                header('Content-type: application/vnd.ms-excel; charset=UTF-8');
                header('Content-Disposition: attachment; filename=reporteUsuarios.xls');
                header('Pragma: no-cache');
                header('Expires: 0');

                if ($row > 0) {
                    echo "<table border='1' style='border-collapse: collapse;'>";
                    echo "<tr style='background-color: #f2f2f2; text-align: center;'>";
                    echo "<th style='width:50px; border: 1px solid black;'>Id.</th>";
                    echo "<th style='width:200px; border: 1px solid black;'>Nombre</th>";
                    echo "<th style='width:100px; border: 1px solid black;'>Dni</th>";
                    echo "<th style='width:200px; border: 1px solid black;'>Direccion</th>";
                    echo "<th style='width:100px; border: 1px solid black;'>Telefono</th>";
                    echo "<th style='width:300px; border: 1px solid black;'>Email</th>";
                    echo "<th style='width:100px; border: 1px solid black;'>Usuario</th>";
                    echo "</tr>";

                    while ($fila = $result->fetch_assoc()) {
                        echo "<tr style='text-align: center;'>";
                        echo "<td style='width:50px; border: 1px solid black;'>{$fila['IdEmpleado']}</td>";
                        echo "<td style='width:200px; border: 1px solid black;'>" . mb_convert_encoding($fila['Nombre'], 'ISO-8859-1', 'UTF-8') . "</td>";
                        echo "<td style='width:100px; border: 1px solid black;'>{$fila['Dni']}</td>";
                        echo "<td style='width:200px; border: 1px solid black;'>{$fila['Direccion']}</td>";
                        echo "<td style='width:100px; border: 1px solid black;'>{$fila['Telefono']}</td>";
                        echo "<td style='width:300px; border: 1px solid black;'>{$fila['Email']}</td>";
                        echo "<td style='width:100px; border: 1px solid black;'>{$fila['Usuario']}</td>";
                        echo "</tr>";
                    }

                    echo "</table>";
                }
            }
        }  
            

?>