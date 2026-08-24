<?php 
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idCliente = isset($input['idCliente']) ? $input['idCliente'] : null;
    $nombreCliente = isset($input['nombreCliente']) ? $input['nombreCliente'] : null;
    $dniCliente = isset($input['dniCliente']) ? $input['dniCliente'] : null;
    $telefonoCliente = isset($input['telefonoCliente']) ? $input['telefonoCliente'] : null;
    $direccionCliente = isset($input['direccionCliente']) ? $input['direccionCliente'] : null;
    $fechaRegistroCliente = isset($input['fechaRegistroCliente']) ? $input['fechaRegistroCliente'] : null;
    
    if($nombreCliente != '' && $dniCliente != '' & $fechaRegistroCliente != ''){
        $query_update = $conexionDB->prepare("
            UPDATE clientes
            SET Nombre = ?, Dni = ?, Telefono = ?, direccion = ?, Fecha_Registro = ?
            WHERE Id_Cliente = ?
        ");
        $query_update->bind_param("sssssi",$nombreCliente,$dniCliente,$telefonoCliente,$direccionCliente,$fechaRegistroCliente,$idCliente);
        if($query_update->execute()){
            echo json_encode(['resultado' => true, 'mensaje' => 'El cliente ha sido actualizado']);

        }else{
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo actualizar el cliente']);

        }
    }else{
        echo json_encode(['resultado' => false, 'mensaje' => 'El nombre, dni o fecha de registro no puede estar vacio']);
    }

?>