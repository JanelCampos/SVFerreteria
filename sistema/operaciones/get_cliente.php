<?php 
    include "../../conexion.php";

    $dniCliente = isset($_GET['dni']) ? $_GET['dni'] : null;

    if ($dniCliente !== null) {
        $query = $conexionDB->prepare("
            SELECT *
            FROM clientes
            WHERE Dni = ?
        ");

        if ($query) {
            $query->bind_param("i", $dniCliente);

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