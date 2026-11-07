<?php
    require_once "../../conexion.php";
    require_once __DIR__ . "/validaciones_proveedor.php";

    $input = json_decode(file_get_contents('php://input'), true);
    $idProveedor = is_array($input) ? filter_var($input['idProveedor'] ?? null, FILTER_VALIDATE_INT) : false;
    if (!$idProveedor || $idProveedor < 1) {
        responderProveedor(false, 'El identificador del proveedor no es válido.');
    }

    try {
        $consulta_articulos = $conexionDB->prepare("SELECT 1 FROM articulos WHERE Cod_Proveedor = ? LIMIT 1");
        $consulta_articulos->bind_param("i", $idProveedor);
        $consulta_articulos->execute();
        $tieneArticulos = $consulta_articulos->get_result()->num_rows > 0;
        $consulta_articulos->close();
        if ($tieneArticulos) {
            responderProveedor(false, 'No se puede eliminar el proveedor porque tiene artículos asociados.');
        }

        $consulta_delete = $conexionDB->prepare("DELETE FROM proveedores WHERE IdProveedor = ?");
        $consulta_delete->bind_param("i", $idProveedor);
        $consulta_delete->execute();
        $eliminado = $consulta_delete->affected_rows > 0;
        $consulta_delete->close();

        if (!$eliminado) {
            responderProveedor(false, 'No se encontró el proveedor.');
        }
        responderProveedor(true, 'El proveedor ha sido eliminado correctamente.');
    } catch (mysqli_sql_exception $exception) {
        error_log("Error al eliminar proveedor: " . $exception->getMessage());
        responderProveedor(false, 'No se pudo eliminar el proveedor. Intente nuevamente.');
    }
?>
