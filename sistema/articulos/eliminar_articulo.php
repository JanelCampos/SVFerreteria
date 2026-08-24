<?php
    session_start();
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idArticulo = isset($input['idArticulo']) ? $input['idArticulo'] : '';
    $cantidad = isset($input['cantidad']) ? $input['cantidad'] : '';

    if(empty($idArticulo)){
        echo json_encode(['resultado' => false, 'mensaje' => 'El id está vacío']);
    }else{
        if($cantidad > 0){
            echo json_encode(['resultado' => false, 'mensaje' => 'Para eliminar, el stock tiene que ser 0']);
        }else {
            $query_delete = $conexionDB->prepare("
                DELETE 
                FROM articulos
                WHERE IdArticulo = ?
            ");
            $query_delete->bind_param("i",$idArticulo);
            if($query_delete->execute()){
                $query_delete_unidades = $conexionDB->prepare("
                    DELETE 
                    FROM articulo_unidades
                    WHERE Cod_Articulo = ?
                ");
                $query_delete_unidades->bind_param("i",$idArticulo);
                $query_delete_unidades->execute();
                $query_delete_unidades->close();

                $query_delete_desceuntos = $conexionDB->prepare("
                    DELETE 
                    FROM articulo_descuentos_cantidad
                    WHERE Cod_Articulo = ?
                ");
                $query_delete_desceuntos->bind_param("i",$idArticulo);
                $query_delete_desceuntos->execute();
                $query_delete_desceuntos->close();

                echo json_encode(['resultado' => true, 'mensaje' => 'Se eliminó el producto']);
            }else{
                echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo eliminar el producto']);
            }
        }
    }
?>