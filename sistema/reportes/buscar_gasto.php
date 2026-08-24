<?php
include "../../conexion.php";
header('Content-Type: application/json');

// Obtener los datos POST enviados
$input = json_decode(file_get_contents('php://input'), true);
$busqueda = isset($input['busqueda']) ? '%' . $conexionDB->real_escape_string($input['busqueda']) . '%' : '%';
$medioPago = isset($input['medioPago']) ? $input['medioPago'] : '';
$tipoGasto = isset($input['tipoGasto']) ? $input['tipoGasto'] : '';
$period = isset($input['period']) ? $input['period'] : '';
$year = isset($input['year']) ? $input['year'] : '';
$month = isset($input['month']) ? $input['month'] : '';
$start_date = isset($input['start_date']) ? $input['start_date'] : '';
$end_date = isset($input['end_date']) ? $input['end_date'] : '';

$current_page = isset($input['page']) ? intval($input['page']) : 1;

$results_per_page = 10;
$start_from = ($current_page - 1) * $results_per_page;

// Base de la consulta
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

if($period === 'year') {
    if(!empty($year)) {
        $consulta .= " AND YEAR(fechaGasto) = ?";
        $tipos .= "s";
        $params[] = $year;
    }
}

if($period === 'month') {
    if(!empty($month)) {
        $consulta .= " AND date_format(fechaGasto, '%Y-%m') = ?";
        $tipos .= "s";
        $params[] = $month;
    }
}

if($period === 'custom') {
    if(!empty($start_date) && !empty($end_date)) {
        $consulta .= " AND fechaGasto BETWEEN ? AND ?";
        $tipos .= "ss";
        $params[] = $start_date;
        $params[] = $end_date;
    }
}

$consulta .= " ORDER BY idGastos DESC LIMIT ?, ?";
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
            FROM gastos 
            WHERE 1=1";

        $count_tipos = "";
        $count_params = [];

        // Agregar condiciones de búsqueda
        if(!empty($busqueda)) {
            $count_query .= " AND (descripcion LIKE ?)";
            $count_tipos .= "s";
            $count_params[] = "%$busqueda%";
        }

        if (!empty($medioPago)) {
            $count_query .= " AND medioPago = ?";
            $count_tipos .= "s";
            $count_params[] = $medioPago;
        }

        if (!empty($tipoGasto)) {
            $count_query .= " AND tipoGasto = ?";
            $count_tipos .= "s";
            $count_params[] = $tipoGasto;
        }

        if($period === 'year') {
            if(!empty($year)) {
                $count_query .= " AND YEAR(fechaGasto) = ?";
                $count_tipos .= "s";
                $count_params[] = $year;
            }
        }

        if($period === 'month') {
            if(!empty($month)) {
                $count_query .= " AND date_format(fechaGasto, '%Y-%m') = ?";
                $count_tipos .= "s";
                $count_params[] = $month;
            }
        }

        if($period === 'custom') {
            if(!empty($start_date) && !empty($end_date)) {
                $count_query .= " AND fechaGasto BETWEEN ? AND ?";
                $count_tipos .= "ss";
                $count_params[] = $start_date;
                $count_params[] = $end_date;
            }
        }

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
