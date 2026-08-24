<?php 
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idCliente = isset($input['idCliente']) ? $input['idCliente'] : null;

    if($idCliente != '' || $idCliente != null){
        $query = $conexionDB->prepare("
            SELECT *
            FROM clientes
            WHERE Id_Cliente = ?
        ");
        $query->bind_param("i",$idCliente);
        if($query->execute()){
            $result = $query->get_result();
            $data = $result->fetch_assoc();
            echo json_encode(['resultado' => true, 'datos' => $data]);
        }else{
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo traer los datos del cliente']);

        }
    }else{
        echo json_encode(['resultado' => false, 'mensaje' => 'El id del cliente está vacio']);
    }
?>