<?php
session_start();
include "../../conexion.php";
header('Content-Type: application/json; charset=utf8');

$idUser = isset($_SESSION['idUser']) ? intval($_SESSION['idUser']) : 0;

$query_delete = $conexionDB->prepare("
    DELETE FROM detalle_cotizacion_temp
    WHERE Cod_Empleado != ? OR FechaCreacion < DATE_SUB(NOW(), INTERVAL 2 HOUR)
");

if ($query_delete) {
    $query_delete->bind_param("i", $idUser);
    $query_delete->execute();
    $query_delete->close();
}

$query = $conexionDB->prepare("
    SELECT correlativo, codArticulo, nombreArticulo, cantidad, Precio_Compra, precio_venta, Ganancias, Unidad, FactorAplicado, PorcentajeDescuento, PrecioConDescuento
    FROM detalle_cotizacion_temp
    WHERE Cod_Empleado = ?
    ORDER BY correlativo
");

if ($query) {
    $query->bind_param("i", $idUser);
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
