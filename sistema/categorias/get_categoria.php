<?php
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);
    $idCategoria = isset($input['id']) ? intval($input['id']) : 0;

    if ($idCategoria <= 0) {
        echo json_encode(['resultado' => false, 'mensaje' => 'Id inválido']);
        exit;
    }

    $query = $conexionDB->prepare("
        SELECT IdCategoria, Nombre, Descripcion, Estado, FechaCreacion
        FROM categorias
        WHERE IdCategoria = ?
    ");
    $query->bind_param("i", $idCategoria);
    if ($query->execute()) {
        $result = $query->get_result();
        $data = $result->fetch_assoc();
        echo json_encode(['resultado' => true, 'datos' => $data]);
    } else {
        echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo traer los datos']);
    }
    $query->close();
    $conexionDB->close();
?>
