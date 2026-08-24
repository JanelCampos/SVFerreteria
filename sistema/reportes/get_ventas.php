<?php
    session_start();
    include "../../conexion.php";
    header('Content-Type: application/json');

    $idVenta = isset($_GET['id']) ? $_GET['id'] : null;

    if ($idVenta !== null) {
        $query = $conexionDB->prepare("
            SELECT * 
            FROM ventas
            WHERE IdVenta = ?
        ");

        if ($query) {
            $query->bind_param("i", $idVenta);

            if ($query->execute()) {
                $result = $query->get_result();
                $data = $result->fetch_assoc();

                echo json_encode(['resultado' => true, 'datos' => $data]);

                $query->close();
            } else {
                echo json_encode(['resultado' => false , 'mensaje' => "Error en la ejecución de la consulta"]);
            }
        } else {
            echo json_encode(['resultado' => false, "mensaje" => "Error en la preparación de la consulta"]);
        }

        $conexionDB->close();
    } else {
        echo json_encode(['resultado' => false, "mensaje" => "idPrestamo no especificado"]);
    }
?>