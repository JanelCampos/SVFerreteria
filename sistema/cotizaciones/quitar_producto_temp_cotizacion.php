<?php
session_start();
include "../../conexion.php";
header('Content-Type: application/json; charset=utf8');

$correlativo = isset($_GET['id']) ? intval($_GET['id']) : 0;
$Cod_Empleado = isset($_SESSION['idUser']) ? intval($_SESSION['idUser']) : 0;

if ($correlativo <= 0) {
    echo json_encode(['resultado' => false, 'mensaje' => 'Error: id no especificado']);
    exit;
}

$query_delete = $conexionDB->prepare("
    DELETE FROM detalle_cotizacion_temp
    WHERE correlativo = ? AND Cod_Empleado = ?
");

if ($query_delete) {
    $query_delete->bind_param("ii", $correlativo, $Cod_Empleado);
    if ($query_delete->execute()) {
        $query_delete->close();

        $query = $conexionDB->prepare("
            SELECT correlativo, codArticulo, nombreArticulo, cantidad, Precio_Compra, precio_venta, Ganancias, Unidad, FactorAplicado, PorcentajeDescuento, PrecioConDescuento
            FROM detalle_cotizacion_temp
            WHERE Cod_Empleado = ?
            ORDER BY correlativo
        ");

        if ($query) {
            $query->bind_param("i", $Cod_Empleado);
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
    } else {
        echo json_encode(['resultado' => false, 'mensaje' => 'Error al eliminar: ' . $conexionDB->error]);
    }
} else {
    echo json_encode(['resultado' => false, 'mensaje' => 'Error en la preparación del delete: ' . $conexionDB->error]);
}

$conexionDB->close();
?>
