<?php 
    include "../../conexion.php";

    $deleteClienteTemp = $conexionDB->prepare("
        DELETE 
        FROM cliente_temp
    ");
    if($deleteClienteTemp){
        if($deleteClienteTemp->execute()){
            $query = $conexionDB->prepare("
                SELECT * 
                FROM detalle_temp
            ");
            if($query->execute()){ 
                $result = $query->get_result();
                $row = $result->num_rows;
                if($row > 0){
                    while($producto = $result->fetch_assoc()){
                        $codArticulo = $producto['codArticulo'];
                        $factor = $producto['FactorAplicado'];
                        $cantidad = $producto['cantidad'];
                        $cantidadDevolver = $cantidad / $factor;
                        
                        $query_update = $conexionDB->prepare("
                            UPDATE articulos
                            SET Cantidad = Cantidad + ?
                            WHERE IdArticulo = ?
                        ");
                        if($query_update){
                            $query_update->bind_param("di",$cantidadDevolver,$codArticulo);
                            if($query_update->execute()){
                                $deleteDetalleTemp = $conexionDB->prepare("
                                    DELETE
                                    FROM detalle_temp
                                ");
                                if($deleteDetalleTemp){
                                    if(!$deleteDetalleTemp->execute()){
                                        echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo eliminar los productos temporales']);
                                    }
                                }
                            }
                        }
                    }
                    echo json_encode(['resultado' => true, 'mensaje' => 'La venta a sido cancelada']);
                }else{
                    echo json_encode(['resultado' => true, 'mensaje' => 'La venta a sido cancelada']);
                }
            }
        }else{
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo eliminar el cliente temporal']);
        }
    }
?>