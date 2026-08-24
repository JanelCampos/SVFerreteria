<?php
    include "../../conexion.php";
    header('Content-Type: application/json; charset=utf-8');

    $input = json_decode(file_get_contents('php://input'), true);

    $idArticulo = isset($input['idArticulo']) ? intval($input['idArticulo']) : 0;
    $cantidadActual = isset($input['cantidadActual']) ? floatval($input['cantidadActual']) : 0;
    $cantidadSalida = isset($input['cantidadSalida']) ? floatval($input['cantidadSalida']) : 0;
    $descripcion = isset($input['descripcion']) ? trim($input['descripcion']) : '';
    $fecha = isset($input['fecha']) ? $input['fecha'] : date('Y-m-d');

    $unidadSeleccionada = isset($input['unidadSeleccionada']) ? trim($input['unidadSeleccionada']) : '';
    $factorAplicado = isset($input['factorAplicado']) ? floatval($input['factorAplicado']) : 0;
    $cantidadOriginal = isset($input['cantidadOriginal']) ? floatval($input['cantidadOriginal']) : 0;

    if ($idArticulo <= 0) {
        echo json_encode(['resultado' => false, 'mensaje' => 'Artículo inválido']);
        exit;
    }

    $art = $conexionDB->prepare("SELECT IdArticulo, Unidad_Presentacion, Cantidad FROM articulos WHERE IdArticulo = ? LIMIT 1");
    $art->bind_param("i", $idArticulo);
    $art->execute();
    $art->bind_result($IdArt, $UnidadPresentacion, $StockActualDB);
    $art->fetch();
    $art->close();
    if (!$IdArt) {
        echo json_encode(['resultado' => false, 'mensaje' => 'Artículo no encontrado']);
        exit;
    }

    if ($cantidadOriginal > 0 && $factorAplicado > 0) {
        $salidaConvertida = round($cantidadOriginal / $factorAplicado, 4);
        if ($cantidadSalida <= 0) {
            $cantidadSalida = $salidaConvertida;
        }
    } elseif ($cantidadSalida <= 0) {
        echo json_encode(['resultado' => false, 'mensaje' => 'Ingrese un valor válido']);
        exit;
    }

    if ($cantidadSalida <= 0) {
        echo json_encode(['resultado' => false, 'mensaje' => 'Cantidad de salida debe ser mayor que 0']);
        exit;
    }

    if ($cantidadOriginal > 0 && empty($unidadSeleccionada)) {
        $unidadSeleccionada = $UnidadPresentacion;
        if ($factorAplicado <= 0) $factorAplicado = 1;
    }

    if ($cantidadOriginal > 0 && $factorAplicado <= 0) {
        echo json_encode(['resultado' => false, 'mensaje' => 'Factor de conversión inválido para la unidad seleccionada']);
        exit;
    }

    if ($cantidadOriginal > 0 && $unidadSeleccionada !== '' && $unidadSeleccionada !== $UnidadPresentacion) {
        $existe = $conexionDB->prepare("SELECT COUNT(*) FROM articulo_unidades WHERE Cod_Articulo = ? AND Unidad = ? LIMIT 1");
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

    if ($cantidadOriginal <= 0) {
        $cantidadOriginal = $cantidadSalida;
        $unidadSeleccionada = $UnidadPresentacion;
        $factorAplicado = 1;
    }

    if ($cantidadSalida > $StockActualDB + 0.000001) {
        echo json_encode(['resultado' => false, 'mensaje' => "No hay stock suficiente. Stock actual: {$StockActualDB} {$UnidadPresentacion}. Necesario: {$cantidadSalida} {$UnidadPresentacion}."]);
        exit;
    }

    $query_update = $conexionDB->prepare("UPDATE articulos SET Cantidad = Cantidad - ? WHERE IdArticulo = ?");
    $query_update->bind_param("di", $cantidadSalida, $idArticulo);
    if (!$query_update->execute()) {
        echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo actualizar el stock: ' . $conexionDB->error]);
        $query_update->close();
        exit;
    }
    $query_update->close();

    $query_insert = $conexionDB->prepare("
        INSERT detalle_salida_stock(idArticulo, cantidad, CantidadOriginal, UnidadOriginal, FactorAplicado, UnidadPresentacion, fecha, descripcion)
        VALUES(?,?,?,?,?,?,?,?)
    ");
    $query_insert->bind_param("iddsisss", $idArticulo, $cantidadSalida, $cantidadOriginal, $unidadSeleccionada, $factorAplicado, $unidadPresentacion, $fecha, $descripcion);
    if ($query_insert->execute()) {
        echo json_encode([
            'resultado' => true,
            'mensaje' => "Operación realizada: {$cantidadOriginal} {$unidadSeleccionada} = {$cantidadSalida} {$UnidadPresentacion} descontados",
            'cantidadSalidaPresentacion' => $cantidadSalida,
            'unidadPresentacion' => $UnidadPresentacion,
            'cantidadOriginal' => $cantidadOriginal,
            'unidadOriginal' => $unidadSeleccionada,
            'factorAplicado' => $factorAplicado,
            'stockNuevo' => round($StockActualDB - $cantidadSalida, 4),
        ]);
    } else {
        echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo insertar el detalle de la salida: ' . $query_insert->error]);
    }
    $query_insert->close();
    $conexionDB->close();
?>
