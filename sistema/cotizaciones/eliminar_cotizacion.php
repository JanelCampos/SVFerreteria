<?php
    session_start();
    include "../../conexion.php";
    header('Content-Type: application/json');

    if (empty($_SESSION['active']) || $_SESSION['rol'] != 1) {
        echo json_encode(['resultado' => false, 'mensaje' => 'Acceso no autorizado. Se requiere rol de administrador.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $idCotizacion = isset($input['IdCotizacion']) ? intval($input['IdCotizacion']) : 0;

    if ($idCotizacion <= 0) {
        echo json_encode(['resultado' => false, 'mensaje' => 'ID de cotización inválido']);
        exit;
    }

    $sqlDelete = "DELETE FROM cotizaciones WHERE IdCotizacion = ?";
    $queryDelete = $conexionDB->prepare($sqlDelete);
    if ($queryDelete) {
        $queryDelete->bind_param("i", $idCotizacion);
        if ($queryDelete->execute()) {
            if ($queryDelete->affected_rows > 0) {
                echo json_encode(['resultado' => true, 'mensaje' => 'Cotización eliminada con éxito']);
            } else {
                echo json_encode(['resultado' => false, 'mensaje' => 'No se encontró la cotización para poder eliminarla']);
            }
        } else {
            echo json_encode(['resultado' => false, 'mensaje' => 'Error al ejecutar la eliminación']);
        }
        $queryDelete->close();
    } else {
        echo json_encode(['resultado' => false, 'mensaje' => 'Error en la consulta: ' . $conexionDB->error]);
    }
?>
