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
        SELECT *
        FROM prestamos
        WHERE nombre LIKE '%$busqueda%'
        ORDER BY idPrestamo DESC LIMIT $start_from, $results_per_page;
    ";

    $result = $conexionDB->query($consulta);

    // Preparar el arreglo de resultados para JSON
    $response = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        $count_query = "
            SELECT COUNT(*) as total
            FROM prestamos
            WHERE nombre LIKE '%$busqueda%'
        ";
        $count_result = $conexionDB->query($count_query);
        $total_records = $count_result->fetch_assoc()['total'];

        echo json_encode(['resultado' => true, 'datos' => $response, 'total_records' => $total_records]);
    } else {
        echo json_encode(['resultado' => false, 'mensaje' => 'No se encontraron resultados.']);
    }

    $conexionDB->close();
?>
