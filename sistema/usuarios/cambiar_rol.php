<?php 
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idUsuario = isset($input['idUsuario']) ? $input['idUsuario'] : null;
    $nuevoRol = isset($input['nuevoRol']) ? $input['nuevoRol'] : null;

    if($nuevoRol != '' && $nuevoRol != null){
        $query_update = $conexionDB->prepare("
            UPDATE empleados
            SET Rol = ?
            WHERE IdEmpleado = ?
        ");
        $query_update->bind_param("ii",$nuevoRol,$idUsuario);
        if($query_update->execute()){
            echo json_encode(['resultado' => true, 'mensaje' => 'El rol del usuario a sido cambiado']);
        }else{
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo cambiar el rol del usuaro']);
        }
    }else{
        echo json_encode(['resultado' => false, 'mensaje' => 'No ha seleccionado ningún rol']);
    }

?>