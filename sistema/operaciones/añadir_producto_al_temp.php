<?php 
    include "../../conexion.php";

    $idArticulo = isset($_GET['id']) ? intval($_GET['id']) : null;
    $precioVenta = isset($_GET['precioVenta']) ? floatval($_GET['precioVenta']) : null;
    $stockVenta = isset($_GET['stockVenta']) ? floatval($_GET['stockVenta']) : null;
    $unidad = isset($_GET['unidad']) ? trim($_GET['unidad']) : '';
    $factorAplicado = isset($_GET['factorAplicado']) ? floatval($_GET['factorAplicado']) : 1.00;
    $porcentajeDescuento = isset($_GET['porcentajeDescuento']) ? floatval($_GET['porcentajeDescuento']) : 0.00;
    $precioConDescuento = isset($_GET['precioConDescuento']) ? floatval($_GET['precioConDescuento']) : null;
    $precioMinimo = isset($_GET['precioMinimo']) ? floatval($_GET['precioMinimo']) : null;
    $aplicarDescuento = isset($_GET['aplicarDescuento']) && $_GET['aplicarDescuento'] === 'true';

    if($aplicarDescuento){
        if ($precioConDescuento === null) {
            $precioConDescuento = $precioVenta;
        }
    }else {
        $precioConDescuento = $precioVenta;
        $porcentajeDescuento = 0.00;
    }

    if($stockVenta > 0){
        if ($idArticulo !== null) {
            $query = $conexionDB->prepare("
                SELECT * 
                FROM articulos 
                WHERE IdArticulo = ?
            ");

            if ($query) {
                $query->bind_param("i", $idArticulo);

                if ($query->execute()) {
                    $result = $query->get_result();
                    $data = $result->fetch_assoc();
                    $nombreArticulo = $data['Nombre'];
                    $precioCompra = $data['Precio_Compra'];
                    $cantidadActual = $data['Cantidad'];
                    // $precioMinimo = isset($data['Precio_Minimo']) ? floatval($data['Precio_Minimo']) : 0.00;

                    $stockPorFactor = $cantidadActual * $factorAplicado;

                    if ($stockPorFactor < $stockVenta) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            "resultado" => false,
                            "mensaje" => "Stock insuficiente. La cantidad en unidad base ({$stockPorFactor}) supera el stock disponible ({$cantidadActual})."
                        ]);
                        exit;
                    }

                    if ($precioMinimo > 0 && $precioConDescuento < $precioMinimo) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            "resultado" => false,
                            "mensaje" => "El precio S/. {$precioConDescuento} está por debajo del precio mínimo permitido (S/. {$precioMinimo})."
                        ]);
                        exit;
                    }

                    $precioCompraUnitarioConFactor = $precioCompra / $factorAplicado;
                    $subTotalConDescuento = $precioConDescuento * $stockVenta;
                    $subTotalCompra = $precioCompraUnitarioConFactor * $stockVenta;
                    $ganancias = $subTotalConDescuento - $subTotalCompra;

                    if (empty($unidad)) {
                        $unidad = !empty($data['Unidad_Presentacion']) ? $data['Unidad_Presentacion'] : 'unidad';
                    }
                    $unidadBase = !empty($data['Unidad_Presentacion']) ? $data['Unidad_Presentacion'] : 'unidad';
                    
                    $query_insert = $conexionDB->prepare("
                        INSERT INTO detalle_temp(codArticulo, nombreArticulo, cantidad, CantidadOriginal, UnidadOriginal, Precio_Compra, precio_venta, Ganancias, Unidad, FactorAplicado, UnidadBase, PorcentajeDescuento, PrecioConDescuento)
                        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)
                    ");

                    if($query_insert){
                        $query_insert->bind_param("isdddssdsdsdd",
                            $idArticulo,
                            $nombreArticulo,
                            $stockVenta,
                            $stockPorFactor,
                            $unidad,
                            $precioCompraUnitarioConFactor,
                            $precioVenta,
                            $ganancias,
                            $unidad,
                            $factorAplicado,
                            $unidadBase,
                            $porcentajeDescuento,
                            $precioConDescuento
                        );

                        if($query_insert->execute()){
                            $cantidadActualizada = ($stockPorFactor - $stockVenta)/$factorAplicado;
                            $query_update = $conexionDB->prepare("
                                UPDATE articulos
                                SET Cantidad = ?
                                WHERE IdArticulo = ?
                            ");
                            if($query_update){
                                $query_update->bind_param("di",$cantidadActualizada,$idArticulo);
                                if($query_update->execute()){

                                    $query = $conexionDB->prepare("
                                        SELECT *
                                        FROM detalle_temp
                                    "); 

                                    if($query){
                                        if($query->execute()){
                                            $result = $query->get_result();
                                            $data = [];
                                            while($row = $result->fetch_assoc()){
                                                $data[] = $row;
                                            }

                                            header('Content-Type: application/json');
                                            echo json_encode(["resultado" => true, "mensaje" => "Producto añadido correctamente", "datos" => $data]);

                                            $query->close();

                                        }
                                    }else{
                                        header('Content-Type: application/json');
                                        echo json_encode(["resultado" => false, "mensaje" => "Error en la ejecución de la consulta"]);
                                    }
                                }
                            }
                        }else{
                            header('Content-Type: application/json');
                            echo json_encode(["resultado" => false, "mensaje" => "Error al insertar producto: " . $conexionDB->error]);
                        }
                    }else{
                        header('Content-Type: application/json');
                        echo json_encode(["resultado" => false, "mensaje" => "Error en la preparación de la inserción: " . $conexionDB->error]);
                    }

                } else {
                    header('Content-Type: application/json');
                    echo json_encode(["resultado" => false, "mensaje" => "Error en la ejecución de la consulta"]);
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode(["resultado" => false, "mensaje" => "Error en la preparación de la consulta"]);
            }

            $conexionDB->close();
        } else {
            header('Content-Type: application/json');
            echo json_encode(["resultado" => false, "mensaje" => "idArticulo no especificado"]);
        }
    }else {
        header('Content-Type: application/json');
        echo json_encode([
            "resultado" => false,
            "mensaje" => "Debe ingresar una cantidad válida"
        ]);
    }
?>