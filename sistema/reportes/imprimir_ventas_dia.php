<?php

    // Inserto el código del archivo conxiondb_agenda que tiene la conexión a la base de datos
    require('../../conexion.php');
    $busqueda = isset($_POST['busqueda']) ? '%'. $conexionDB->real_escape_string($_POST['busqueda']) . '%' : '%';
    $medioPago = isset($_POST['medioPago']) ? $_POST['medioPago'] : '';
    $estado = isset($_POST['estado']) ? $_POST['estado'] : '';
    $nombreProducto = isset($_POST['nombreProducto']) ? '%'. $conexionDB->real_escape_string($_POST['nombreProducto']) . '%' : '';

    $consulta = "
        SELECT v.IdVenta, v.Fecha, v.Cod_Caja, v.dniCliente, cl.Nombre, v.Total, v.Estado, v.Medio_Pago, 
                v.saldo, c.Cod_Empleado as empl, e.Nombre as nempl, v.utilidad, dva.nombreArticulo
        FROM ventas v 
        INNER JOIN caja c ON v.Cod_Caja = c.IdCaja
        INNER JOIN empleados e ON c.Cod_Empleado = e.IdEmpleado
        INNER JOIN clientes cl ON cl.Dni = v.dniCliente
        INNER JOIN detalle_venta_articulos dva ON dva.Cod_Venta = v.IdVenta
        WHERE 1=1";

    $tipos = "";
    $params = [];

    // Agregar condiciones de búsqueda
    if (!empty($busqueda)) {
        $consulta .= " AND (v.IdVenta LIKE ? OR v.dniCliente LIKE ? OR cl.Nombre LIKE ?)";
        $tipos .= "sss";
        $params[] = "%" . $busqueda . "%";
        $params[] = "%" . $busqueda . "%";
        $params[] = $busqueda;
    }

    if (!empty($nombreProducto)) {
        $consulta .= " AND dva.nombreArticulo LIKE ?";
        $tipos .= "s";
        $params[] = "%" . $nombreProducto . "%";
    }

    if (!empty($medioPago)) {
        $consulta .= " AND v.Medio_Pago = ?";
        $tipos .= "s";
        $params[] = $medioPago;
    }

    if (!empty($estado)) {
        $consulta .= " AND v.Estado = ?";
        $tipos .= "s";
        $params[] = $estado;
    }

    $consulta .= " AND DATE(v.Fecha) = DATE(NOW())";
    $consulta .= " ORDER BY IdVenta DESC";

    $query = $conexionDB->prepare($consulta);

    // Vincular parámetros dinámicamente
    if (!empty($params)) {
        $query->bind_param($tipos, ...$params);
    }

    $query->execute();
    $result = $query->get_result();
    $row = $result->num_rows;

    // si el formulario ha sido enviado procesa los datos del formulario                        
    
    if (isset($_POST['nPdf'])) 
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
            $pdf->Cell(40, 6, ' Fecha/Hora: Venta ', 1, 0, 'C', 1);
            $pdf->Cell(37, 6, 'ID-Vendedor', 1, 0, 'C', 1);
            $pdf->Cell(25, 6, 'DNI Cliente', 1, 0, 'C', 1);
            $pdf->Cell(20, 6, 'Total', 1, 0, 'C', 1);
            $pdf->Cell(20, 6, 'Estado ', 1, 0, 'C', 1);
            $pdf->Cell(20, 6, 'Pago ', 1, 0, 'C', 1);
            $pdf->Cell(20, 6, 'Saldo', 1, 1, 'C', 1);
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
                    $pdf->Cell(10, 6, $fila["IdVenta"], 1, 0, 'C', 1);
                    $pdf->Cell(40, 6, mb_convert_encoding($fila["Fecha"], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
                    $pdf->Cell(37, 6, $fila["Cod_Caja"].'-'.$fila['nempl'], 1, 0, 'C', 1);
                    $pdf->Cell(25, 6, $fila["dniCliente"], 1, 0, 'C', 1);
                    $pdf->Cell(20, 6, 'S/. '.$fila["Total"], 1, 0, 'C', 1);
                    $pdf->Cell(20, 6, $fila["Estado"], 1, 0, 'C', 1);
                    $pdf->Cell(20, 6, $fila["Medio_Pago"], 1, 0, 'C', 1);
                    $pdf->Cell(20, 6, 'S/. '.$fila["saldo"], 1, 1, 'C', 1);
                }
                $pdf->Output('', 'articulos_completo.pdf');
                // acá mando la salida y con nombre por defecto como "articulos_completo.pdf"
                // primer parámetro: nada: muestra el archivo, D muestra para descargarlo
            }
            else    
                echo 'No hay artículos para mostrar';
        }else
        {
            if (isset($_POST['nExcel'])) 
            {
                header('Content-type: application/vnd.ms-excel; charset=UTF-8');
                header('Content-Disposition: attachment; filename=reporteVentas.xls');
                header('Pragma: no-cache');
                header('Expires: 0');

                if ($row > 0) {
                    echo "<table border='1' style='border-collapse: collapse;'>";
                    echo "<tr style='background-color: #f2f2f2; text-align: center;'>";
                    echo "<th style='width:50px; border: 1px solid black;'>Id.</th>";
                    echo "<th style='width:200px; border: 1px solid black;'>Fecha/Hora: Venta</th>";
                    echo "<th style='width:150px; border: 1px solid black;'>ID-Vendedor</th>";
                    echo "<th style='width:150px; border: 1px solid black;'>DNI Cliente</th>";
                    echo "<th style='width:100px; border: 1px solid black;'>Total</th>";
                    echo "<th style='width:100px; border: 1px solid black;'>Estado</th>";
                    echo "<th style='width:100px; border: 1px solid black;'>Pago</th>";
                    echo "<th style='width:100px; border: 1px solid black;'>saldo</th>";
                    echo "</tr>";

                    while ($fila = $result->fetch_assoc()) {
                        echo "<tr style='text-align: center;'>";
                        echo "<td style='width:50px; border: 1px solid black;'>{$fila['IdVenta']}</td>";
                        echo "<td style='width:200px; border: 1px solid black;'>" . mb_convert_encoding($fila['Fecha'], 'ISO-8859-1', 'UTF-8') . "</td>";
                        echo "<td style='width:150px; border: 1px solid black;'>{$fila['Cod_Caja']}-{$fila['nempl']}</td>";
                        echo "<td style='width:150px; border: 1px solid black;'>{$fila['dniCliente']}</td>";
                        echo "<td style='width:100px; border: 1px solid black;'>S/. {$fila['Total']}</td>";
                        echo "<td style='width:100px; border: 1px solid black;'>{$fila['Estado']}</td>";
                        echo "<td style='width:100px; border: 1px solid black;'>{$fila['Medio_Pago']}</td>";
                        echo "<td style='width:100px; border: 1px solid black;'>S/. {$fila['saldo']}</td>";
                        echo "</tr>";
                    }

                    echo "</table>";
                }
            }
        }    

?>