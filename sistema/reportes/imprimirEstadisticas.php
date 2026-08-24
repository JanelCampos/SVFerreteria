<?php

    // Inserto el código del archivo conxiondb_agenda que tiene la conexión a la base de datos
    require('../../conexion.php');
    $busqueda = isset($_POST['busqueda']) ? '%'. $conexionDB->real_escape_string($_POST['busqueda']) . '%' : '%';
    $IdProveedor = isset($_POST['IdProveedor']) ? $_POST['IdProveedor'] : '';
    $estadistica = isset($_POST['estadistica']) ? $_POST['estadistica'] : 'PMV'; // Valor por defecto PMV
    $period = isset($_POST['period']) ? $_POST['period'] : 'year'; // Valor por defecto año
    $year = isset($_POST['year']) ? $_POST['year'] : ''; // Valor por defecto vacío
    $month = isset($_POST['month']) ? $_POST['month'] : ''; // Valor por defecto vacío
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : ''; // Valor por defecto vacío
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : ''; // Valor por defecto vacío
    $consulta = "
        SELECT 
            a.IdArticulo AS Cod_Articulo,
            p.IdProveedor,
            a.Nombre,
            a.Cantidad,
            a.Precio_Compra,
            a.Precio_Unitario,
            p.Nombre AS nombreProv,
            IFNULL(SUM(dva.Cantidad), 0) AS cantidadVendida,
            IFNULL(SUM(dva.Ganancias), 0) AS gananciaGenerada,
            v.Fecha
        FROM articulos a
        LEFT JOIN detalle_venta_articulos dva ON a.IdArticulo = dva.Cod_Articulo
        INNER JOIN proveedores p ON a.Cod_Proveedor = p.IdProveedor
        INNER JOIN ventas v ON dva.Cod_Venta = v.IdVenta
        WHERE 1=1
        ";

    $tipos = "";
    $params = [];

    // Agregar condiciones de búsqueda
    if (!empty($busqueda)) {
        $consulta .= " AND a.Nombre LIKE ?";
        $tipos .= "s";
        $params[] = "%". $busqueda. "%";
    }

    if (!empty($IdProveedor)) {
        $consulta .= " AND p.IdProveedor = ?";
        $tipos .= "i";
        $params[] = $IdProveedor;
    }

    if($period === 'year') {
        if(!empty($year)) {
            $consulta .= " AND YEAR(v.Fecha) = ?";
            $tipos .= "s";
            $params[] = $year;
        }
    }

    if($period === 'month') {
        if(!empty($month)) {
            $consulta .= " AND date_format(v.Fecha, '%Y-%m') = ?";
            $tipos .= "s";
            $params[] = $month;
        }
    }

    if($period === 'custom') {
        if(!empty($start_date) && !empty($end_date)) {
            $consulta .= " AND v.Fecha BETWEEN ? AND ?";
            $tipos .= "ss";
            $params[] = $start_date;
            $params[] = $end_date;
        }
    }

    $consulta .= " GROUP BY a.IdArticulo";

    if ($estadistica === 'PMV') {
        $consulta .= " ORDER BY cantidadVendida DESC";
    } elseif ($estadistica === 'PCMG') {
        $consulta .= " ORDER BY gananciaGenerada DESC";
    } else {
        // Valor por defecto si el valor proporcionado no es válido
        $consulta .= " ORDER BY cantidadVendida DESC";
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
            $pdf->Cell(60, 6, 'Nombre', 1, 0, 'C', 1);
            $pdf->Cell(13, 6, 'Stock', 1, 0, 'C', 1);
            $pdf->Cell(13, 6, 'P. C.', 1, 0, 'C', 1);
            $pdf->Cell(13, 6, 'P. v.', 1, 0, 'C', 1);
            $pdf->Cell(55, 6, 'Proveedor', 1, 0, 'C', 1);
            $pdf->Cell(13, 6, 'Vend.', 1, 0, 'C', 1);
            $pdf->Cell(16, 6, 'Ganan.', 1, 1, 'C', 1);
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
                    $pdf->Cell(10, 6, $fila["Cod_Articulo"], 1, 0, 'C', 1);
                    $pdf->Cell(60, 6, mb_convert_encoding($fila["Nombre"], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
                    $pdf->Cell(13, 6, $fila["Cantidad"], 1, 0, 'C', 1);
                    $pdf->Cell(13, 6, $fila["Precio_Compra"], 1, 0, 'C', 1);
                    $pdf->Cell(13, 6, $fila["Precio_Unitario"], 1, 0, 'C', 1);
                    $pdf->Cell(55, 6, mb_convert_encoding($fila["nombreProv"], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
                    $pdf->Cell(13, 6, $fila["cantidadVendida"], 1, 0, 'C', 1);
                    $pdf->Cell(16, 6, $fila["gananciaGenerada"], 1, 1, 'C', 1);
                }
                $pdf->Output('', 'Lista_productos_mas_vendidos.pdf');
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
                    echo "<th style='width:50px; border: 1px solid black;'>Cod.</th>";
                    echo "<th style='width:300px; border: 1px solid black;'>Nombre</th>";
                    echo "<th style='width:120px; border: 1px solid black;'>Stock actual</th>";
                    echo "<th style='width:120px; border: 1px solid black;'>P. compra</th>";
                    echo "<th style='width:120px; border: 1px solid black;'>P. venta</th>";
                    echo "<th style='width:250px; border: 1px solid black;'>Proveedor</th>";
                    echo "<th style='width:100px; border: 1px solid black;'>Cantidad Vendida</th>";
                    echo "<th style='width:100px; border: 1px solid black;'>Ganancia generada</th>";
                    echo "</tr>";

                    while ($fila = $result->fetch_assoc()) {
                        echo "<tr style='text-align: center;'>";
                        echo "<td style='width:50px; border: 1px solid black;'>{$fila['Cod_Articulo']}</td>";
                        echo "<td style='width:300px; border: 1px solid black;'>" . mb_convert_encoding($fila['Nombre'], 'ISO-8859-1', 'UTF-8') . "</td>";
                        echo "<td style='width:120px; border: 1px solid black;'>{$fila['Cantidad']}</td>";
                        echo "<td style='width:120px; border: 1px solid black;'>S/. {$fila['Precio_Compra']}</td>";
                        echo "<td style='width:120px; border: 1px solid black;'>S/. {$fila['Precio_Unitario']}</td>";
                        echo "<td style='width:250px; border: 1px solid black;'>" . mb_convert_encoding($fila['nombreProv'], 'ISO-8859-1', 'UTF-8') . "</td>";
                        echo "<td style='width:100px; border: 1px solid black;'>{$fila['cantidadVendida']}</td>";
                        echo "<td style='width:100px; border: 1px solid black;'> S/. {$fila['gananciaGenerada']}</td>";
                        echo "</tr>";
                    }

                    echo "</table>";
                }
            }
        }    

?>