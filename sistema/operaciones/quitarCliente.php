<?php 
    include "../../conexion.php";

    if($_SERVER['REQUEST_METHOD'] === 'DELETE'){
        $query = $conexionDB->prepare("
            SELECT *
            FROM cliente_temp
        ");
        if($query){
            if($query->execute()){
                $result = $query->get_result();
                $row = $result->num_rows;
                if($row > 0){
                    $query_delete = $conexionDB->prepare("
                        DELETE FROM cliente_temp
                    ");
                    if($query_delete){
                        if($query_delete->execute()){
                            echo json_encode(['resultado' => true, 'message' => 'cliente quitado correctameente']);
                        }else{
                            echo json_encode(['resultado' => false, 'message' => 'No se pudo quitar el cliente']);
                        }
                    }
                }else{
                    echo json_encode(['resultado' => false, 'message' => 'No hay ningun cliente asociado']);
                }
            }
        }
    }
?>