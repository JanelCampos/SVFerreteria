<?php
include "../../conexion.php";
header('Content-Type: application/json');

// Obtener los datos POST enviados
$input = json_decode(file_get_contents('php://input'), true);

$nombreArticulo = isset($input['busqueda']) ? '%' . $conexionDB->real_escape_string($input['busqueda']) . '%' : '%';
$proveedor = isset($input['proveedor']) ? $input['proveedor'] : '';
$estadistica = isset($input['estadistica']) ? $input['estadistica'] : 'PMV'; // Valor por defecto PMV
$period = isset($input['period']) ? $input['period'] : 'year'; // Valor por defecto año
$year = isset($input['year']) ? $input['year'] : ''; // Valor por defecto vacío
$month = isset($input['month']) ? $input['month'] : ''; // Valor por defecto vacío
$start_date = isset($input['start_date']) ? $input['start_date'] : ''; // Valor por defecto vacío
$end_date = isset($input['end_date']) ? $input['end_date'] : ''; // Valor por defecto vacío
$current_page = isset($input['page']) ? intval($input['page']) : 1;

$results_per_page = 10;
$start_from = ($current_page - 1) * $results_per_page;

// Base de la consulta
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
if (!empty($nombreArticulo)) {
    $consulta .= " AND a.Nombre LIKE ?";
    $tipos .= "s";
    $params[] = "%". $nombreArticulo. "%";
}

if (!empty($proveedor)) {
    $consulta .= " AND p.IdProveedor = ?";
    $tipos .= "i";
    $params[] = $proveedor;
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
    $consulta .= " ORDER BY cantidadVendida DESC LIMIT ?, ?";
} elseif ($estadistica === 'PCMG') {
    $consulta .= " ORDER BY gananciaGenerada DESC LIMIT ?, ?";
} else {
    // Valor por defecto si el valor proporcionado no es válido
    $consulta .= " ORDER BY cantidadVendida DESC LIMIT ?, ?";
}

$params[] = $start_from;
$params[] = $results_per_page;
$tipos .= "ii";

// Preparar la consulta
$query = $conexionDB->prepare($consulta);

// Vincular parámetros dinámicamente
if (!empty($params)) {
    $query->bind_param($tipos, ...$params);
}

// Ejecutar la consulta y obtener los resultados
$response = [];
if ($query->execute()) {
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }

        $count_query = "
            SELECT COUNT(*) AS total
            FROM (
                SELECT a.IdArticulo
                FROM articulos a
                LEFT JOIN detalle_venta_articulos dva ON a.IdArticulo = dva.Cod_Articulo
                INNER JOIN proveedores p ON a.Cod_Proveedor = p.IdProveedor
                INNER JOIN ventas v ON dva.Cod_Venta = v.IdVenta
                WHERE 1=1
        ";                                                           
        
        $count_tipos = "";
        $count_params = [];

        // Agregar condiciones de búsqueda
        if (!empty($nombreArticulo)) {
            $count_query .= " AND a.Nombre LIKE ?";
            $count_tipos .= "s";
            $count_params[] = "%". $nombreArticulo . "%";
        }

        if (!empty($proveedor)) {
            $count_query .= " AND p.IdProveedor = ?";
            $count_tipos .= "i";
            $count_params[] = $proveedor;
        }

        if($period === 'year') {
            if(!empty($year)) {
                $count_query .= " AND YEAR(v.Fecha) = ?";
                $count_tipos .= "s";
                $count_params[] = $year;
            }
        }

        if($period === 'month') {
            if(!empty($month)) {
                $count_query .= " AND date_format(v.Fecha, '%Y-%m') = ?";
                $count_tipos .= "s";
                $count_params[] = $month;
            }
        }

        if($period === 'custom') {
            if(!empty($start_date) && !empty($end_date)) {
                $count_query .= " AND v.Fecha BETWEEN ? AND ?";
                $count_tipos .= "ss";
                $count_params[] = $start_date;
                $count_params[] = $end_date;
            }
        }        

        $count_query .= " GROUP BY a.IdArticulo) AS sub";

        $count_stmt = $conexionDB->prepare($count_query);
        $count_stmt->bind_param($count_tipos, ...$count_params);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $total_records = $count_result->fetch_assoc()['total'];

        echo json_encode(['resultado' => true, 'datos' => $response, 'total_records' => $total_records]);

    } else {
        echo json_encode(['resultado' => false, 'mensaje' => 'No se encontraron resultados.']);
    }
    $query->close();
} else {
    echo json_encode(['resultado' => false, 'mensaje' => 'Error en la ejecución de la consulta.']);
}

$conexionDB->close();
?>
