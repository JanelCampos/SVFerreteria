<?php 
    include "../../conexion.php";

    $palabraClave = isset($_GET['palabra']) ? $_GET['palabra'] : null;
    $palabraClave = '%'. strtolower($palabraClave).'%';

    if ($palabraClave !== null) {
        $query = $conexionDB->prepare("
            SELECT * 
            FROM articulos 
            WHERE  codigoBarras LIKE ? OR LOWER(Nombre) LIKE ?
        ");

        if ($query) {
            $query->bind_param("ss", $palabraClave, $palabraClave);

            if ($query->execute()) {
                $result = $query->get_result();
                $data = [];
                while($row = $result->fetch_assoc()){
                    $data[] = $row;
                }

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