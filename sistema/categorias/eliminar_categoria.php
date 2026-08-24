<?php
    include "../../conexion.php";
    header('Content-Type: application/json');
    session_start();
    if ($_SESSION['rol'] != 1) {
        echo json_encode(['resultado' => false, 'mensaje' => 'Permisos insuficientes']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $idCategoria = isset($input['idCategoria']) ? intval($input['idCategoria']) : 0;

    if ($idCategoria <= 0) {
        echo json_encode(['resultado' => false, 'mensaje' => 'El id no puede estar vacio']);
        exit;
    }

    $query = $conexionDB->prepare("SELECT COUNT(*) as cant FROM articulos WHERE Cod_Categoria = ?");
    $query->bind_param("i", $idCategoria);
    $query->execute();
    $row = $query->get_result()->fetch_assoc()['cant'];
    $query->close();

    if ($row > 0) {
        echo json_encode(['resultado' => false, 'mensaje' => 'No se puede eliminar la categoría, ya que hay artículos asociados']);
        exit;
    }

    $query_delete = $conexionDB->prepare("UPDATE categorias SET Estado = 0 WHERE IdCategoria = ?");
    $query_delete->bind_param("i", $idCategoria);
    if ($query_delete->execute()) {
        echo json_encode(['resultado' => true, 'mensaje' => 'La categoría ha sido eliminada correctamente']);
    } else {
        echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo eliminar la categoría']);
    }
    $query_delete->close();
    $conexionDB->close();
?>
