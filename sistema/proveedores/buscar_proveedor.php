<?php
    require_once "../../conexion.php";
    header('Content-Type: application/json; charset=utf-8');

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['resultado' => false, 'mensaje' => 'La solicitud no tiene un formato válido.']);
        exit;
    }

    $busqueda = isset($input['busqueda']) && is_string($input['busqueda']) ? trim($input['busqueda']) : '';
    $pagina = isset($input['page']) ? filter_var($input['page'], FILTER_VALIDATE_INT) : 1;
    $pagina = $pagina && $pagina > 0 ? $pagina : 1;
    $porPagina = 10;
    $desde = ($pagina - 1) * $porPagina;
    $filtro = '%' . $busqueda . '%';

    try {
        $consulta = $conexionDB->prepare(
            "SELECT IdProveedor, ruc, Nombre, Direccion, Telefono, Email
             FROM proveedores
             WHERE Nombre LIKE ?
             ORDER BY IdProveedor DESC
             LIMIT ?, ?"
        );
        $consulta->bind_param("sii", $filtro, $desde, $porPagina);
        $consulta->execute();
        $resultado = $consulta->get_result();
        $proveedores = $resultado->fetch_all(MYSQLI_ASSOC);
        $consulta->close();

        $consulta_total = $conexionDB->prepare("SELECT COUNT(*) AS total FROM proveedores WHERE Nombre LIKE ?");
        $consulta_total->bind_param("s", $filtro);
        $consulta_total->execute();
        $total = (int) $consulta_total->get_result()->fetch_assoc()['total'];
        $consulta_total->close();

        echo json_encode([
            'resultado' => true,
            'datos' => $proveedores,
            'total_records' => $total,
            'mensaje' => 'Búsqueda exitosa.'
        ]);
    } catch (mysqli_sql_exception $exception) {
        error_log("Error al buscar proveedores: " . $exception->getMessage());
        http_response_code(500);
        echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo completar la búsqueda.']);
    }
?>
