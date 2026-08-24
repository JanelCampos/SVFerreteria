<?php 
    include "../../conexion.php";
    header('Content-Type: application/json');

    $correlativo = isset($_GET['id']) ? intval($_GET['id']) : null;

    if ($correlativo !== null) {
        $query = $conexionDB->prepare("
            SELECT codArticulo, cantidad, FactorAplicado
            FROM detalle_temp
            WHERE correlativo = ?
        ");
        if($query){
            $query->bind_param("i", $correlativo);
            if($query->execute()){
                $result = $query->get_result();
                $data = $result->fetch_assoc();
                $codArticulo = $data['codArticulo'];
                $cantidadDevolver = floatval($data['cantidad']);
                $factorAplicado = floatval(isset($data['FactorAplicado']) ? $data['FactorAplicado'] : 1);

                $queryArticulo = $conexionDB->prepare("
                    SELECT Cantidad
                    FROM articulos
                    WHERE IdArticulo = ?
                ");
                $queryArticulo->bind_param("i", $codArticulo);
                if($queryArticulo->execute()){
                    $resultArticulo = $queryArticulo->get_result();
                    $dataArticulo = $resultArticulo->fetch_assoc();
                    $cantidadActual = floatval($dataArticulo['Cantidad']);
                    $cantidadActualizada = $cantidadActual + ($cantidadDevolver/$factorAplicado);

                    $query_update = $conexionDB->prepare("
                        UPDATE articulos
                        SET Cantidad = ?
                        WHERE IdArticulo = ?
                    ");
                    if($query_update){
                        $query_update->bind_param("di", $cantidadActualizada, $codArticulo);
                        if($query_update->execute()){
                            $query_delete = $conexionDB->prepare("
                                DELETE 
                                FROM detalle_temp
                                WHERE correlativo = ?
                            ");
                            if ($query_delete) {
                                $query_delete->bind_param("i", $correlativo);

                                if ($query_delete->execute()) {
                                    $query = $conexionDB->prepare("
                                        SELECT * 
                                        FROM detalle_temp
                                    ");

                                    if($query){
                                        if($query->execute()){
                                            $result = $query->get_result();
                                            $filas = $result->num_rows;
                                            $data = [];
                                            if($filas > 0){
                                                while($row = $result->fetch_assoc()){
                                                    $data[] = $row;
                                                }

                                                echo json_encode(['resultado' => true, 'datos' => $data, 'mensaje' => 'Producto quitado correctamente']);
                                            }else{
                                                echo json_encode(['resultado' => false, 'datos' => $data, 'mensaje' => 'Producto quitado correctamente']);
                                            }
                                        }else{
                                            echo json_encode(["error" => "Error en la ejecución de la consulta"]);
                                        }
                                    }else{
                                        echo json_encode(["error" => "Error en la ejecución de la consulta"]);
                                    }

                                } else {
                                    echo json_encode(["error" => "Error en la ejecución de la consulta"]);
                                }
                            } else {
                                echo json_encode(["error" => "Error en la preparación de la consulta"]);
                            }
                        }
                    }
                }
            }
        }

        $conexionDB->close();
    } else {
        header('Content-Type: application/json');
        echo json_encode(["error" => "idCorrelativo no especificado"]);
    }
?>