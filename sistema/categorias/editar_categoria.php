<?php
    include "../../conexion.php";
    header('Content-Type: application/json');
    session_start();
    if ($_SESSION['rol'] != 1) {
        echo json_encode(['resultado' => false, 'mensaje' => 'Permisos insuficientes']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $IdCategoria = isset($input['IdCategoria']) ? intval($input['IdCategoria']) : 0;
    $nombre = isset($input['nombre']) ? trim($input['nombre']) : '';
    $descripcion = isset($input['descripcion']) ? trim($input['descripcion']) : '';

    if ($IdCategoria <= 0 || $nombre === '') {
        echo json_encode(['resultado' => false, 'mensaje' => 'El nombre es obligatorio']);
        exit;
    }

    $query_check = $conexionDB->prepare("SELECT IdCategoria FROM categorias WHERE Nombre = ? AND IdCategoria <> ? AND Estado = 1");
    $query_check->bind_param("si", $nombre, $IdCategoria);
    $query_check->execute();
    $existente = $query_check->get_result()->num_rows;
    $query_check->close();

    if ($existente > 0) {
        echo json_encode(['resultado' => false, 'mensaje' => 'Ya existe una categoría con ese nombre']);
        exit;
    }

    $query_update = $conexionDB->prepare("
        UPDATE categorias
        SET Nombre = ?, Descripcion = ?
        WHERE IdCategoria = ?
    ");
    $query_update->bind_param("ssi", $nombre, $descripcion, $IdCategoria);
    if ($query_update->execute()) {
        echo json_encode(['resultado' => true, 'mensaje' => 'La categoría ha sido actualizada']);
    } else {
        echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo actualizar la categoría']);
    }
    $query_update->close();
    $conexionDB->close();
?>
