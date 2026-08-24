<?php
include "../../conexion.php";
header('Content-Type: application/json');

// Obtener los datos POST enviados
$input = json_decode(file_get_contents('php://input'), true);
$busqueda = isset($input['busqueda']) ? '%' . $conexionDB->real_escape_string($input['busqueda']) . '%' : '%';
$period = isset($input['period']) ? $input['period'] : '';
$year = isset($input['year']) ? $input['year'] : '';
$month = isset($input['month']) ? $input['month'] : '';
$startDate = isset($input['startDate']) ? $input['startDate'] : '';
$endDate = isset($input['endDate']) ? $input['endDate'] : '';

$current_page = isset($input['page']) ? intval($input['page']) : 1;
$results_per_page = 10;
$start_from = ($current_page - 1) * $results_per_page;

// Preparar la consulta con el filtro de actividad y la paginación
$consulta = "
    SELECT c.IdCaja, c.FechaApertura, c.Actividad, c.Monto_inicial, c.Monto_salida, c.Total_caja, c.Cod_Empleado, e.Nombre
    FROM caja c
    INNER JOIN empleados e ON c.Cod_Empleado = e.IdEmpleado
    WHERE 1=1
";

$types = "";
$params = [];

// Filtros de búsqueda
if (!empty($busqueda)) {
    $consulta .= " AND c.Actividad LIKE ?";
    $params[] = $busqueda;
    $types .= "s";
}

// Filtro de período
if ($period == 'year') {
    if (!empty($year)) {
        $consulta .= " AND YEAR(c.FechaApertura) = ?";
        $params[] = $year;
        $types .= "s";
    }
}

if($period === 'month') {
    if (!empty($month)) {
        $consulta .= " AND date_format(c.FechaApertura, '%Y-%m') = ?";
        $params[] = date('Y-m', strtotime($month));
        $types .= "s";
    }
}

if($period === 'custom') {
    if (!empty($startDate)) {
        $consulta .= " AND c.FechaApertura >= ?";
        $params[] = $startDate;
        $types .= "s";
    }
    
    if (!empty($endDate)) {
        $consulta .= " AND c.FechaApertura <= ?";
        $params[] = $endDate;
        $types .= "s";
    }
}

$consulta .= " ORDER BY c.FechaApertura DESC LIMIT ?, ?";
$params[] = $start_from;
$params[] = $results_per_page;
$types .= "ii";

// Preparar la consulta
$query = $conexionDB->prepare($consulta);
$query->bind_param($types, ...$params);

// Ejecutar la consulta
$response = [];
if ($query->execute()) {
    $result = $query->get_result();
    if($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }

        // Consulta para contar el total de registros sin LIMIT
        $count_query = "
            SELECT COUNT(*) AS total
            FROM caja c
            INNER JOIN empleados e ON c.Cod_Empleado = e.IdEmpleado
            WHERE 1=1
        ";

        $count_types = "";
        $count_params = [];

        if (!empty($busqueda)) {
            $count_query .= " AND c.Actividad LIKE ?";
            $count_params[] = $busqueda;
            $count_types .= "s";
        }

        // Filtro de período
        if ($period == 'year') {
            if (!empty($year)) {
                $count_query .= " AND YEAR(c.FechaApertura) = ?";
                $count_params[] = $year;
                $count_types .= "s";
            }
        }

        if($period === 'month') {
            if (!empty($month)) {
                $count_query .= " AND date_format(c.FechaApertura, '%Y-%m') = ?";
                $count_params[] = date('Y-m', strtotime($month));
                $count_types .= "s";
            }
        }

        if($period === 'custom') {
            if (!empty($startDate)) {
                $count_query .= " AND c.FechaApertura >= ?";
                $count_params[] = $startDate;
                $count_types .= "s";
            }
            
            if (!empty($endDate)) {
                $count_query .= " AND c.FechaApertura <= ?";
                $count_params[] = $endDate;
                $count_types .= "s";
            }
        }

        // Preparar la consulta de conteo
        $count_stmt = $conexionDB->prepare($count_query);
        $count_stmt->bind_param($count_types, ...$count_params);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $total_records = $count_result->fetch_assoc()['total'];

        echo json_encode(['resultado' => true, 'datos' => $response, 'total_records' => $total_records]);
    }else{
        echo json_encode(['resultado' => false, 'mensaje' => 'No se encontraron resultados']);
    }
} else {
    echo json_encode(['resultado' => false, 'mensaje' => 'Error en la ejecución de la consulta.']);
}

$query->close();
$conexionDB->close();
?>
