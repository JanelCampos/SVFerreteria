<?php 
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $id = isset($input['idUsuarioEliminar']) ? $input['idUsuarioEliminar'] : null;

    if($id != '' && $id != null){
        $query_delete = $conexionDB->prepare("
        DELETE
        FROM empleados
        WHERE IdEmpleado = ?
        ");
        $query_delete->bind_param("i", $id);
        if($query_delete->execute()){
            echo json_encode(['resultado' => true, 'mensaje' => 'El usuario a sido eliminado correctamente']);

        }else{
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo eliminar el usuario']);

        }
    }else{
        echo json_encode(['resultado' => false, 'mensaje' => 'El id del usuario no puede estar vacio']);
    }
?>