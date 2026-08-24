<?php 
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idUsuario = isset($input['idUsuario']) ? $input['idUsuario'] : null;
    $nuevaClave = isset($input['nuevaClave']) ? $input['nuevaClave'] : null;
    $claveRepetida = isset($input['claveRepetida']) ? $input['claveRepetida'] : null;
    if($nuevaClave != '' && $nuevaClave != null){
        if($claveRepetida != '' && $claveRepetida != null){
            if($nuevaClave === $claveRepetida){
                $clave_hash = password_hash($nuevaClave, PASSWORD_DEFAULT);
                $query_update = $conexionDB->prepare("
                    UPDATE empleados
                    SET Clave = ?
                    WHERE IdEmpleado = ?
                ");
                $query_update->bind_param("si",$clave_hash,$idUsuario);
                if($query_update->execute()){
                    echo json_encode(['resultado' => true, 'mensaje' => 'La contraseña ha sido actualizada correctamente']);
                }else{
                    echo json_encode(['resultado' => false, 'mensaje' => 'no se pudo actualizar la contraseña']);
                }
            }else{
                echo json_encode(['resultado' => false, 'mensaje' => 'La contraseña no coincide']);
            }
        }else{
            echo json_encode(['resultado' => false, 'mensaje' => 'Debe ingresar un valor']);
        }
    }else{
        echo json_encode(['resultado' => false, 'mensaje' => 'Debe ingresar un valor']);
    }
?>