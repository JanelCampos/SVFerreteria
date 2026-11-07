<?php
    require_once "../../conexion.php";
    require_once __DIR__ . "/validaciones_proveedor.php";

    $input = json_decode(file_get_contents('php://input'), true);
    $idProveedor = is_array($input) ? filter_var($input['id'] ?? null, FILTER_VALIDATE_INT) : false;
    if (!$idProveedor || $idProveedor < 1) {
        responderProveedor(false, 'El identificador del proveedor no es válido.');
    }

    try {
        $consulta = $conexionDB->prepare(
            "SELECT IdProveedor, ruc, Nombre, Direccion, Telefono, Email FROM proveedores WHERE IdProveedor = ?"
        );
        $consulta->bind_param("i", $idProveedor);
        $consulta->execute();
        $proveedor = $consulta->get_result()->fetch_assoc();
        $consulta->close();

        if (!$proveedor) {
            responderProveedor(false, 'No se encontró el proveedor.');
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['resultado' => true, 'datos' => $proveedor]);
    } catch (mysqli_sql_exception $exception) {
        error_log("Error al consultar proveedor: " . $exception->getMessage());
        http_response_code(500);
        responderProveedor(false, 'No se pudieron cargar los datos del proveedor.');
    }
?>
