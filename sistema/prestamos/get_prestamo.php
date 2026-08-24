<?php
    session_start();
    include "../../conexion.php";

    $idPrestamo = isset($_GET['id']) ? $_GET['id'] : null;

    if ($idPrestamo !== null) {
        $query = $conexionDB->prepare("
            SELECT * 
            FROM prestamos
            WHERE idPrestamo = ?
        ");

        if ($query) {
            $query->bind_param("i", $idPrestamo);

            if ($query->execute()) {
                $result = $query->get_result();
                $data = $result->fetch_assoc();

                header('Content-Type: application/json');
                echo json_encode($data);

                $query->close();
            } else {
                header('Content-Type: application/json');
                echo json_encode(["error" => "Error en la ejecución de la consulta"]);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(["error" => "Error en la preparación de la consulta"]);
        }

        $conexionDB->close();
    } else {
        header('Content-Type: application/json');
        echo json_encode(["error" => "idPrestamo no especificado"]);
    }
?>