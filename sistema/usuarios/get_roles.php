<?php 
    include "../../conexion.php";
    header('Content-Type: application/json');

    $query = $conexionDB->prepare("
        SELECT *
        FROM rol
    ");
    if($query->execute()){
        $result = $query->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['resultado' => true, 'datos' => $data]);
    }else{
        echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo traer los roles']);
    }
?>