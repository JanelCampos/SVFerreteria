<?php
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idCliente = isset($input['idCliente']) ? $input['idCliente'] : null;

    if($idCliente === '' && $idCliente === null){
        echo json_encode(['resultado' => false, 'mensaje' => 'El id no puede estar vacio']);
    }else{
        $query_delete = $conexionDB->prepare("
            DELETE 
            FROM clientes 
            WHERE Id_Cliente = ?
        ");
        $query_delete->bind_param("i", $idCliente);
        if($query_delete->execute()){
            echo json_encode(['resultado' => true, 'mensaje' => 'Cliente eliminado']);
        }else{
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo eliminar el cliente']);
            
        }
    }
?>