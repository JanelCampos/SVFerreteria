<?php 
    include "../../conexion.php";
    header('Content-Type: application/json');

    // Obtener los datos POST enviados
    $input = json_decode(file_get_contents('php://input'), true);

    $idProveedor = isset($input['id']) ? $input['id'] : null;

    $query = $conexionDB->prepare("
        SELECT *
        FROM proveedores
        WHERE IdProveedor = ?
    ");
    $query->bind_param("i",$idProveedor);
    if($query->execute()){
        $result = $query->get_result();
        $data = $result->fetch_assoc();
        echo json_encode(['resultado' => true, 'datos' => $data]);
    }else{
        echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo traer los datos']);
    }
?>