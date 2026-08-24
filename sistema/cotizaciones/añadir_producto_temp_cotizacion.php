<?php
session_start();
include "../../conexion.php";
header('Content-Type: application/json; charset=utf8');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$cantidad = isset($_GET['cantidad']) ? floatval($_GET['cantidad']) : 0;
$precioCompra = isset($_GET['precioCompra']) ? floatval($_GET['precioCompra']) : 0.00;
$precioVenta = isset($_GET['precioVenta']) ? floatval($_GET['precioVenta']) : 0.00;
$unidad = isset($_GET['unidad']) ? trim($_GET['unidad']) : '';
$factorAplicado = isset($_GET['factorAplicado']) ? floatval($_GET['factorAplicado']) : 1.00;
$porcentajeDescuento = isset($_GET['porcentajeDescuento']) ? floatval($_GET['porcentajeDescuento']) : 0.00;
$precioConDescuento = isset($_GET['precioConDescuento']) ? floatval($_GET['precioConDescuento']) : null;

$Cod_Empleado = isset($_SESSION['idUser']) ? intval($_SESSION['idUser']) : 0;

if ($id <= 0 || $cantidad <= 0) {
    echo json_encode(['resultado' => false, 'mensaje' => 'Error: id y cantidad deben ser mayores a 0']);
    exit;
}

if ($precioConDescuento === null || $precioConDescuento == 0) {
    $precioConDescuento = $precioVenta;
}

if (empty($unidad)) {
    $unidad = 'unidad';
}

$ganancias = ($precioConDescuento * $cantidad) - ($precioCompra * $cantidad);

$query_articulo = $conexionDB->prepare("
    SELECT Nombre
    FROM articulos
    WHERE IdArticulo = ?
");

$nombreArticulo = '';
if ($query_articulo) {
    $query_articulo->bind_param("i", $id);
    if ($query_articulo->execute()) {
        $result = $query_articulo->get_result();
        $data = $result->fetch_assoc();
        $nombreArticulo = isset($data['Nombre']) ? $data['Nombre'] : '';
    }
    $query_articulo->close();
}

$query_insert = $conexionDB->prepare("
    INSERT INTO detalle_cotizacion_temp (Cod_SesionCotizacion, Cod_Empleado, codArticulo, nombreArticulo, cantidad, Precio_Compra, precio_venta, Ganancias, Unidad, FactorAplicado, PorcentajeDescuento, PrecioConDescuento)
    VALUES (0,?,?,?,?,?,?,?,?,?,?,?)
");

if ($query_insert) {
    $query_insert->bind_param("iisddddsddd",
        $Cod_Empleado,
        $id,
        $nombreArticulo,
        $cantidad,
        $precioCompra,
        $precioVenta,
        $ganancias,
        $unidad,
        $factorAplicado,
        $porcentajeDescuento,
        $precioConDescuento
    );

    if ($query_insert->execute()) {
        $query_insert->close();

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
                echo json_encode(['resultado' => true, 'datos' => $datos, 'mensaje' => 'Artículo agregado']);
            } else {
                echo json_encode(['resultado' => false, 'mensaje' => 'Error en la ejecución de la consulta']);
            }
            $query->close();
        } else {
            echo json_encode(['resultado' => false, 'mensaje' => 'Error en la preparación de la consulta']);
        }
    } else {
        echo json_encode(['resultado' => false, 'mensaje' => 'Error al insertar producto: ' . $conexionDB->error]);
    }
} else {
    echo json_encode(['resultado' => false, 'mensaje' => 'Error en la preparación de la inserción: ' . $conexionDB->error]);
}

$conexionDB->close();
?>
