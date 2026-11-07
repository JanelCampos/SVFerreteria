<?php
    require_once "../../conexion.php";
    require_once __DIR__ . "/validaciones_proveedor.php";

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        responderProveedor(false, 'La solicitud no tiene un formato válido.');
    }

    $idProveedor = filter_var($input['IdProveedor'] ?? null, FILTER_VALIDATE_INT);
    $ruc = isset($input['ruc']) && is_string($input['ruc']) ? trim($input['ruc']) : '';
    $nombre = isset($input['nombre']) && is_string($input['nombre']) ? trim($input['nombre']) : '';
    $direccion = isset($input['direccion']) && is_string($input['direccion']) ? trim($input['direccion']) : '';
    $telefono = isset($input['telefono']) && is_string($input['telefono']) ? trim($input['telefono']) : '';
    $correo = isset($input['correo']) && is_string($input['correo']) ? trim($input['correo']) : '';

    if (!$idProveedor || $idProveedor < 1 || $ruc === '' || $nombre === '') {
        responderProveedor(false, 'El RUC y el nombre son obligatorios.');
    }
    if (strlen($ruc) > 20 || strlen($nombre) > 50 || strlen($direccion) > 50 || strlen($telefono) > 20 || strlen($correo) > 50) {
        responderProveedor(false, 'Verifique la longitud de los campos.');
    }
    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        responderProveedor(false, 'Ingrese un correo electrónico válido.');
    }

    $bloqueoRuc = null;
    $mensaje = '';
    $actualizado = false;
    try {
        $consulta_existencia = $conexionDB->prepare("SELECT IdProveedor FROM proveedores WHERE IdProveedor = ?");
        $consulta_existencia->bind_param("i", $idProveedor);
        $consulta_existencia->execute();
        $existe = $consulta_existencia->get_result()->num_rows > 0;
        $consulta_existencia->close();
        if (!$existe) {
            $mensaje = 'No se encontró el proveedor.';
        } else {
            $bloqueoRuc = bloquearRucProveedor($conexionDB, $ruc);
            if (existeProveedorConRuc($conexionDB, $ruc, $idProveedor)) {
                $mensaje = 'Ya existe otro proveedor con ese RUC.';
            } else {
                $direccion = $direccion !== '' ? $direccion : null;
                $telefono = $telefono !== '' ? $telefono : null;
                $correo = $correo !== '' ? $correo : null;
                $query_update = $conexionDB->prepare(
                    "UPDATE proveedores SET ruc = ?, Nombre = ?, Direccion = ?, Telefono = ?, Email = ? WHERE IdProveedor = ?"
                );
                $query_update->bind_param("sssssi", $ruc, $nombre, $direccion, $telefono, $correo, $idProveedor);
                $query_update->execute();
                $query_update->close();
                $actualizado = true;
            }
        }
    } catch (mysqli_sql_exception $exception) {
        error_log("Error al actualizar proveedor: " . $exception->getMessage());
        $mensaje = 'No se pudo actualizar el proveedor. Intente nuevamente.';
    } catch (RuntimeException $exception) {
        error_log("Error al bloquear RUC de proveedor: " . $exception->getMessage());
        $mensaje = 'No se pudo verificar el RUC. Intente nuevamente.';
    } finally {
        if ($bloqueoRuc !== null) {
            try {
                desbloquearRucProveedor($conexionDB, $bloqueoRuc);
            } catch (mysqli_sql_exception $exception) {
                error_log("Error al liberar RUC de proveedor: " . $exception->getMessage());
            }
        }
    }

    if ($actualizado) {
        responderProveedor(true, 'El proveedor ha sido actualizado.');
    }
    responderProveedor(false, $mensaje);
?>
