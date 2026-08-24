<?php 
    session_start();
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idUsuario = isset($input['idUsuario']) ? $input['idUsuario'] : null;
    $nombreUsuario = isset($input['nombreUsuario']) ? $input['nombreUsuario'] : null;
    $dniUsuario = isset($input['dniUsuario']) ? $input['dniUsuario'] : null;
    $direccionUsuario = isset($input['direccionUsuario']) ? $input['direccionUsuario'] : null;
    $telefonoUsuario = isset($input['telefonoUsuario']) ? $input['telefonoUsuario'] : null;
    $correoUsuario = isset($input['correoUsuario']) ? $input['correoUsuario'] : null;
    $usuario = isset($input['usuario']) ? $input['usuario'] : null;

    if($idUsuario != '' && $nombreUsuario != '' && $dniUsuario != '' && $direccionUsuario != '' && $telefonoUsuario != '' && $correoUsuario != '' && $usuario != ''){
        $query_insert = $conexionDB->prepare("
            UPDATE empleados
            SET Nombre = ?, Dni = ?, Direccion = ?, Telefono = ?, Email = ?, Usuario = ?
            WHERE IdEmpleado = ? 
        ");
        $query_insert->bind_param("ssssssi",$nombreUsuario,$dniUsuario,$direccionUsuario,$telefonoUsuario,$correoUsuario,$usuario, $idUsuario);
        if($query_insert->execute()){
            
            echo json_encode(['resultado' => true, 'mensaje' => 'Usuario actualizado correctamente']);
        }else{
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo actualizar el usuario']);
        }
    }else{
        echo json_encode(['resultado' => false, 'mensaje' => 'El id del usuario no puede estar vacio']);
    }
?>