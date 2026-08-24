<?php
include "../../conexion.php";
header('Content-Type: application/json');

// Obtener los datos POST enviados
$input = json_decode(file_get_contents('php://input'), true);
$busqueda = isset($input['busqueda']) ? '%' . $conexionDB->real_escape_string($input['busqueda']) . '%' : '%';
$medioPago = isset($input['medioPago']) ? $input['medioPago'] : '';
$tipoGasto = isset($input['tipoGasto']) ? $input['tipoGasto'] : '';
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
    $params[] = "$busqueda";
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

$consulta .= " AND MONTH(fechaGasto) = MONTH(CURDATE())";
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
            $count_params[] = "$busqueda";
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

        $count_query .= " AND MONTH(fechaGasto) = MONTH(CURDATE())";
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
