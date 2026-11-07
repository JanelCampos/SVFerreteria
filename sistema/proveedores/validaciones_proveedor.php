<?php
    function bloquearRucProveedor($conexionDB, $ruc) {
        $nombreBloqueo = 'proveedor_ruc_' . substr(hash('sha256', $ruc), 0, 48);
        $consulta = $conexionDB->prepare("SELECT GET_LOCK(?, 10)");
        $consulta->bind_param("s", $nombreBloqueo);
        $consulta->execute();
        $obtenido = $consulta->get_result()->fetch_row()[0];
        $consulta->close();

        if ((int) $obtenido !== 1) {
            throw new RuntimeException('No se pudo bloquear temporalmente el RUC.');
        }
        return $nombreBloqueo;
    }

    function desbloquearRucProveedor($conexionDB, $nombreBloqueo) {
        $consulta = $conexionDB->prepare("SELECT RELEASE_LOCK(?)");
        $consulta->bind_param("s", $nombreBloqueo);
        $consulta->execute();
        $consulta->close();
    }

    function existeProveedorConRuc($conexionDB, $ruc, $idExcluir = 0) {
        $consulta = $conexionDB->prepare(
            "SELECT IdProveedor FROM proveedores WHERE ruc = ? AND IdProveedor <> ? LIMIT 1"
        );
        $consulta->bind_param("si", $ruc, $idExcluir);
        $consulta->execute();
        $existe = $consulta->get_result()->num_rows > 0;
        $consulta->close();
        return $existe;
    }

    function responderProveedor($resultado, $mensaje) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['resultado' => $resultado, 'mensaje' => $mensaje]);
        exit;
    }
?>
