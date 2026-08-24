<?php 
    include "../../conexion.php";

    $query = $conexionDB->prepare("
        SELECT *
        FROM cliente_temp
    ");
    if($query){
        if($query->execute()){
            $result = $query->get_result();
            $row = $result->num_rows;
            if($row > 0){
                echo json_encode(['resultado' => true]);
            }else{
                echo json_encode(['resultado' => false, 'message' => 'Tiene que ingresar un cliente']);
            }
        }else{
            echo json_encode(['resultado' => false, 'message' => 'Error en la ejecucion de la consulta']);
        }
    }else{
        echo json_encode(['resultado' => false, 'message' => 'error en la consulta']);
    }
?>