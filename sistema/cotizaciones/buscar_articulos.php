<?php
session_start();
include "../../conexion.php";
header('Content-Type: application/json; charset=utf8');

$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

if (empty($busqueda)) {
    echo json_encode(['resultado' => true, 'datos' => []]);
    exit;
}

$param = '%' . $busqueda . '%';

$query = $conexionDB->prepare("
    SELECT IdArticulo, Cod_Barra, Nombre, Cantidad, Precio_Unitario, Unidad_Base, Precio_Minimo, Stock_Alerta
    FROM articulos
    WHERE (Nombre LIKE ? OR Cod_Barra LIKE ?) AND Estado = 1
    ORDER BY Nombre ASC
    LIMIT 15
");

if ($query) {
    $query->bind_param("ss", $param, $param);
    if ($query->execute()) {
        $result = $query->get_result();
        $datos = [];
        while ($row = $result->fetch_assoc()) {
            $datos[] = $row;
        }
        echo json_encode(['resultado' => true, 'datos' => $datos]);
    } else {
        echo json_encode(['resultado' => false, 'mensaje' => 'Error en la ejecución de la consulta']);
    }
    $query->close();
} else {
    echo json_encode(['resultado' => false, 'mensaje' => 'Error en la preparación de la consulta']);
}

$conexionDB->close();
?>
