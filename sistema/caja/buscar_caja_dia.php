<?php
include "../../conexion.php";
header('Content-Type: application/json');

// Obtener los datos POST enviados
$input = json_decode(file_get_contents('php://input'), true);
$busqueda = isset($input['busqueda']) ? '%' . $conexionDB->real_escape_string($input['busqueda']) . '%' : '%';
$current_page = isset($input['page']) ? intval($input['page']) : 1;
$results_per_page = 10;
$start_from = ($current_page - 1) * $results_per_page;

// Preparar la consulta
$consulta = "
    SELECT c.IdCaja, c.FechaApertura, c.Actividad, c.Monto_inicial, c.Monto_salida, c.totalCajaDia, c.Cod_Empleado, e.Nombre
    FROM caja c
    INNER JOIN empleados e ON c.Cod_Empleado = e.IdEmpleado
    WHERE c.IdCaja > (
        SELECT MAX(c1.IdCaja)
        FROM caja c1
        WHERE c1.Estado = 'Cerrado'
    )
    AND c.Estado = 'Abierto' AND c.Actividad LIKE ?
    ORDER BY c.FechaApertura DESC
    LIMIT ?, ?";

// Preparar la consulta
$query = $conexionDB->prepare($consulta);

// Vincular el parámetro
$query->bind_param("sii", $busqueda, $start_from, $results_per_page);

// Ejecutar la consulta
$response = [];  // Inicializar la variable $response
if($query->execute()){
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        // Obtener el número total de registros sin la cláusula LIMIT para la paginación
    $count_query = $conexionDB->prepare("
        SELECT COUNT(*) AS total
        FROM caja c
        INNER JOIN empleados e ON c.Cod_Empleado = e.IdEmpleado
        WHERE c.IdCaja > (
            SELECT MAX(c1.IdCaja)
            FROM caja c1
            WHERE c1.Estado = 'Cerrado'
        )
        AND c.Estado = 'Abierto' AND c.Actividad LIKE ?
    ");

    $count_query->bind_param("s", $busqueda);
    $count_query->execute();
    $count_result = $count_query->get_result();
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
