<?php 
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $IdProveedor = isset($input['IdProveedor']) ? $input['IdProveedor'] : '';
    $nombre = isset($input['nombre']) ? $input['nombre'] : '';
    $direccion = isset($input['direccion']) ? $input['direccion'] : '';
    $telefono = isset($input['telefono']) ? $input['telefono'] : '';
    $correo = isset($input['correo']) ? $input['correo'] : '';
    $ruc = isset($input['ruc']) ? $input['ruc'] : '';

    if($IdProveedor != '' && $nombre != ''){
        $query_update = $conexionDB->prepare("
            UPDATE proveedores
            SET ruc = ?, Nombre = ?, Direccion = ?, Telefono = ?, Email = ?
            WHERE IdProveedor = ?
        ");
        $query_update->bind_param("issssi",$ruc,$nombre,$direccion,$telefono,$correo,$IdProveedor);
        if($query_update->execute()){
            echo json_encode(['resultado' => true, 'mensaje' => 'El proveedor ha sido actualizado']);

        }else{
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo actualizar el proveedor']);

        }
    }else{
        echo json_encode(['resultado' => false, 'mensaje' => 'Algunos campos no pueden ir vacios']);
    }
?>