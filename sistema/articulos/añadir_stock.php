<?php
session_start();
include "../../conexion.php";
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);

$idArticulo = isset($input['idArticulo']) ? intval($input['idArticulo']) : 0;
$nombre = isset($input['nombre']) ? $input['nombre'] : '';
$precioCompraIngresada = isset($input['precioCompra']) ? floatval($input['precioCompra']) : 0;
$cantidadActual = isset($input['cantidad']) ? floatval($input['cantidad']) : 0;

$unidadSeleccionada = isset($input['unidadSeleccionada']) ? trim($input['unidadSeleccionada']) : '';
$factorSeleccionado = isset($input['factorAplicado']) ? floatval($input['factorAplicado']) : 0;
$CantidadOriginal = isset($input['CantidadOriginal']) ? floatval($input['CantidadOriginal']) : 0;
$CantidadAñadir = isset($input['CantidadAñadir']) ? floatval($input['CantidadAñadir']) : 0;

if ($idArticulo <= 0) {
    echo json_encode(['resultado' => false, 'mensaje' => 'Artículo inválido']);
    exit;
}

$art = $conexionDB->prepare("
    SELECT IdArticulo, Unidad_Presentacion, Precio_Compra, Cantidad 
    FROM articulos 
    WHERE IdArticulo = ? LIMIT 1");
$art->bind_param("i", $idArticulo);
$art->execute();
$art->bind_result($IdArt, $UnidadPresentacion, $PrecioCompraActual, $StockActualDB);
$art->fetch();
$art->close();
if (!$IdArt) {
    echo json_encode(['resultado' => false, 'mensaje' => 'Artículo no encontrado']);
    exit;
}

if ($CantidadOriginal > 0 && $factorSeleccionado > 0) {
    $CantidadCalculada = round($CantidadOriginal * $factorSeleccionado, 4);
    if ($CantidadAñadir <= 0) {
        $CantidadAñadir = $CantidadCalculada;
    }
} elseif ($CantidadAñadir <= 0) {
    echo json_encode(['resultado' => false, 'mensaje' => 'Ingrese una cantidad válida']);
    exit;
}

if ($CantidadAñadir <= 0) {
    echo json_encode(['resultado' => false, 'mensaje' => 'Cantidad a añadir debe ser mayor que 0']);
    exit;
}

if ($CantidadOriginal > 0 && empty($unidadSeleccionada)) {
    $unidadSeleccionada = $UnidadPresentacion;
    if ($factorSeleccionado <= 0) $factorSeleccionado = 1;
}

if ($CantidadOriginal > 0 && $factorSeleccionado <= 0) {
    echo json_encode(['resultado' => false, 'mensaje' => 'Factor de conversión inválido para la unidad seleccionada']);
    exit;
}

if ($CantidadOriginal > 0 && $unidadSeleccionada !== '' && $unidadSeleccionada !== $UnidadPresentacion) {
    $existe = $conexionDB->prepare("
        SELECT COUNT(*) 
        FROM articulo_unidades 
        WHERE Cod_Articulo = ? AND Unidad = ? LIMIT 1");
    $existe->bind_param("is", $idArticulo, $unidadSeleccionada);
    $existe->execute();
    $existe->bind_result($c);
    $existe->fetch();
    $existe->close();
    if ($c <= 0) {
        echo json_encode(['resultado' => false, 'mensaje' => "El artículo no tiene configurada la equivalencia para '{$unidadSeleccionada}'. Edite el artículo y agregue la equivalencia."]);
        exit;
    }
}

if ($CantidadOriginal <= 0) {
    $CantidadOriginal = $CantidadAñadir;
    $unidadSeleccionada = $UnidadPresentacion;
    $factorSeleccionado = 1;
}

$precioCompraActual = floatval($PrecioCompraActual);
if ($precioCompraIngresada > 0 && $precioCompraIngresada != $precioCompraActual) {
    if ($StockActualDB <= 0) {
        $query_update = $conexionDB->prepare("
            UPDATE articulos
            SET Precio_Compra = ?, Cantidad = Cantidad + ?
            WHERE IdArticulo = ?
        ");
        $query_update->bind_param("ddi", $precioCompraIngresada, $CantidadAñadir, $idArticulo);
    } else {
        echo json_encode(['resultado' => false, 'mensaje' => 'Si el stock actual es mayor que 0, no puede cambiar el precio de compra']);
        exit;
    }
} else {
    $query_update = $conexionDB->prepare("
        UPDATE articulos
        SET Cantidad = Cantidad + ?
        WHERE IdArticulo = ?
    ");
    $query_update->bind_param("di", $CantidadAñadir, $idArticulo);
}

if (!$query_update->execute()) {
    echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo añadir el stock: ' . $conexionDB->error]);
    $query_update->close();
    exit;
}
$query_update->close();

$msg = "Stock añadido: {$CantidadOriginal} {$unidadSeleccionada} = {$CantidadAñadir} {$UnidadPresentacion}";
echo json_encode([
    'resultado' => true,
    'mensaje' => $msg,
    'equivalenteUnidadPresentacion' => round($CantidadAñadir, 2),
    'unidadPresentacion' => $UnidadPresentacion,
    'cantidadOriginal' => round($CantidadOriginal, 4),
    'unidadOriginal' => $unidadSeleccionada,
    'factorAplicado' => round($factorSeleccionado, 4)
]);
$conexionDB->close();
?>
