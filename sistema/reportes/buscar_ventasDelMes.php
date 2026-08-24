<?php
include "../../conexion.php";
header('Content-Type: application/json');

// Obtener los datos POST enviados
$input = json_decode(file_get_contents('php://input'), true);

$busqueda = isset($input['busqueda']) ? '%' . $conexionDB->real_escape_string($input['busqueda']) . '%' : '%';
$nombreProducto = isset($input['nombreProducto']) ? $input['nombreProducto'] : '';
$medioPago = isset($input['medioPago']) ? $input['medioPago'] : '';
$estado = isset($input['estado']) ? $input['estado'] : '';
$current_page = isset($input['page']) ? intval($input['page']) : 1;

$results_per_page = 10;
$start_from = ($current_page - 1) * $results_per_page;

// Base de la consulta
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

$consulta .= " AND YEAR(v.Fecha) = YEAR(CURDATE()) AND MONTH(v.Fecha) = MONTH(CURDATE())";
$consulta .= " ORDER BY IdVenta DESC LIMIT ?, ?";
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
            FROM ventas v 
            INNER JOIN caja c ON v.Cod_Caja = c.IdCaja
            INNER JOIN empleados e ON c.Cod_Empleado = e.IdEmpleado
            INNER JOIN clientes cl ON cl.Dni = v.dniCliente
            INNER JOIN detalle_venta_articulos dva ON dva.Cod_Venta = v.IdVenta
            WHERE 1=1";

        $count_tipos = "";
        $count_params = [];

        // Agregar condiciones de búsqueda
        if (!empty($busqueda)) {
            $count_query .= " AND (v.IdVenta LIKE ? OR v.dniCliente LIKE ? OR cl.Nombre LIKE ?)";
            $count_tipos .= "sss";
            $count_params[] = "%" . $busqueda . "%";
            $count_params[] = "%" . $busqueda . "%";
            $count_params[] = $busqueda;
        }

        if (!empty($nombreProducto)) {
            $count_query .= " AND dva.nombreArticulo LIKE ?";
            $count_tipos .= "s";
            $count_params[] = $nombreProducto;
        }

        if (!empty($medioPago)) {
            $count_query .= " AND v.Medio_Pago = ?";
            $count_tipos .= "s";
            $count_params[] = $medioPago;
        }

        if (!empty($estado)) {
            $count_query .= " AND v.Estado = ?";
            $count_tipos .= "s";
            $count_params[] = $estado;
        }
        
        $count_query .= " AND YEAR(v.Fecha) = YEAR(CURDATE()) AND MONTH(v.Fecha) = MONTH(CURDATE())";
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
