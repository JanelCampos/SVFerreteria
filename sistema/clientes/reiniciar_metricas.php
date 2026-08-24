<?php 
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idCliente = isset($input['idCliente']) ? $input['idCliente'] : null;
    
    if($idCliente != '' && $idCliente != null){
        $query_update = $conexionDB->prepare("
            UPDATE clientes
            SET cantidadCompras = 0, montoCompras = 0, gananciaGenerada = 0
            WHERE Id_Cliente = ?
        ");
        $query_update->bind_param("i", $idCliente);
        if($query_update->execute()){
            echo json_encode(['resultado' => true, 'mensaje' => 'Las metricas ha sido reiniciadas']);

        }else{
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo reiniciar las metricas']);

        }
    }else{
        echo json_encode(['resultado' => false, 'mensaje' => 'El id está vacio']);
    }

?>