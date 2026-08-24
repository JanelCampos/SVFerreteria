<?php 
    include "../../conexion.php";

    $dniCliente = isset($_GET['dni']) ? $_GET['dni'] : null;
    $nombrecliente = isset($_GET['nombre']) ? $_GET['nombre'] : null;
    $direccionCliente = isset($_GET['direccion']) ? $_GET['direccion'] : null;
    $telefonoCliente = isset($_GET['telefono']) ? $_GET['telefono'] : null;
    $fechaRegistro = isset($_GET['fecha']) ? $_GET['fecha'] : null;
    
    if($dniCliente !== null && $nombrecliente !== null){
        $query_insert = $conexionDB->prepare("
            INSERT INTO cliente_temp(nombre,dni,telefono,direccion,fechaRegistro)
            VALUES(?,?,?,?,?)
        ");
        if($query_insert){
            $query_insert->bind_param("siiss",$nombrecliente,$dniCliente,$telefonoCliente,$direccionCliente,$fechaRegistro);

            if($query_insert->execute()){
                $query = $conexionDB->prepare("
                    SELECT * 
                    FROM cliente_temp
                ");
                if($query){
                    if($query->execute()){
                        $result = $query->get_result();
                        $data = $result->fetch_assoc();

                        header('Content-Type: application/json');
                        echo json_encode($data);

                        $query->close();
                    }else{
                        header('Content-Type: application/json');
                        echo json_encode(["error" => "Error en la ejecución de la consulta"]);
                    }
                }else{
                    header('Content-Type: application/json');
                    echo json_encode(["error" => "Error en la ejecución de la consulta"]);
                }
            }
        }else{
            header('Content-Type: application/json');
            echo json_encode(["error" => "Error en la ejecución de la consulta"]);
        }
    }else{
        header('Content-Type: application/json');
        echo json_encode(["error" => "El nombre y dni del cliente son obligatorios"]);
    }
?>