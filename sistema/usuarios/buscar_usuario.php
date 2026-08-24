<?php
include "../../conexion.php";
header('Content-Type: application/json');

// Obtener los datos POST enviados
$input = json_decode(file_get_contents('php://input'), true);
$busqueda = isset($input['busqueda']) ? '%' . $conexionDB->real_escape_string($input['busqueda']) . '%' : '%';
$current_page = isset($input['page']) ? intval($input['page']) : 1;

$results_per_page = 10;
$start_from = ($current_page - 1) * $results_per_page;

// Construir la consulta SQL con filtro de búsqueda
$consulta = "
    SELECT u.IdEmpleado, u.Nombre, u.Dni, u.Direccion, u.Telefono, u.Email, u.Usuario, u.Rol, r.rol
    FROM empleados u
    INNER JOIN rol r ON u.Rol = r.IdRol
    WHERE 1=1
";
$tipos = "";
$params = [];

if (!empty($busqueda)) {
    $consulta .= " AND (u.Nombre LIKE ? OR u.Dni LIKE ?)";
    $tipos .= "ss";
    $params[] = "%" . $busqueda . "%";
    $params[] = "%" . $busqueda . "%";
}

$consulta .= " ORDER BY u.IdEmpleado DESC LIMIT ?, ?";
$params[] = $start_from;
$params[] = $results_per_page;
$tipos .= "ii";

$query = $conexionDB->prepare($consulta);

// Vincular parámetros dinámicamente
if (!empty($params)) {
    $query->bind_param($tipos, ...$params);
}
$query->execute();
$result = $query->get_result();
$count = $result->num_rows;

// Preparar el arreglo de resultados para JSON
$response = array();

if ($count > 0) {
    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }

    // Consulta de conteo total
    $count_query = "
        SELECT COUNT(*) as total
        FROM empleados u
        INNER JOIN rol r ON u.Rol = r.IdRol
        WHERE 1=1
    ";
    $count_tipos = "";
    $count_params = [];

    if (!empty($busqueda)) {
        $count_query .= " AND (u.Nombre LIKE ? OR u.Dni LIKE ?)";
        $count_tipos .= "ss";
        $count_params[] = "%" . $busqueda . "%";
        $count_params[] = "%" . $busqueda . "%";
    }

    $count_stmt = $conexionDB->prepare($count_query);
    $count_stmt->bind_param($count_tipos, ...$count_params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_records = $count_result->fetch_assoc()['total'];

    echo json_encode(['resultado' => true, 'datos' => $response, 'total_records' => $total_records, 'mensaje' => 'Busqueda exitosa.']);
} else {
    echo json_encode(['resultado' => false, 'mensaje' => 'No se encontraron resultados.']);
}

$conexionDB->close();
?>
