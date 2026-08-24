<?php 
    include "../conexion.php";

    function gastosDelMes(){
        include "../conexion.php";
        $totalRegistros = 0;

        $query = $conexionDB->prepare("
            SELECT COUNT(*) AS totalRegistros
            FROM gastos
            Where fechaGasto >= CURDATE() - INTERVAL 1 MONTH AND fechaGasto < CURDATE() + INTERVAL 1 DAY;
        ");
        if($query){
            if($query->execute()){
                $result = $query->get_result();
                $data = $result->fetch_assoc();
                $totalRegistros = $data['totalRegistros'];
            }
        }
        return $totalRegistros;
    }

    function gastosDelDia(){
        include "../conexion.php";
        $totalRegistros = 0;

        $query = $conexionDB->prepare("
            SELECT COUNT(*) AS totalRegistros
            FROM gastos
             Where fechaGasto >= CURDATE() AND fechaGasto < CURDATE() + INTERVAL 1 DAY;
        ");
        if($query){
            if($query->execute()){
                $result = $query->get_result();
                $data = $result->fetch_assoc();
                $totalRegistros = $data['totalRegistros'];
            }
        }
        return $totalRegistros;
    }

    function ventasDelMes(){
        include "../conexion.php";
        $totalRegistros = 0;

        $query = $conexionDB->prepare("
            SELECT COUNT(*) AS totalRegistros
            FROM ventas
            WHERE YEAR(Fecha) = YEAR(CURDATE()) AND MONTH(Fecha) = MONTH(CURDATE());
        ");
        if($query){
            if($query->execute()){
                $result = $query->get_result();
                $data = $result->fetch_assoc();
                $totalRegistros = $data['totalRegistros'];
            }
        }
        return $totalRegistros;
    }

    function ventasDelDìa(){
        include "../conexion.php";
        $totalRegistros = 0;

        $query = $conexionDB->prepare("
            SELECT COUNT(*) AS totalRegistros
            FROM ventas
            WHERE DATE(Fecha) = CURDATE();
        ");
        if($query){
            if($query->execute()){
                $result = $query->get_result();
                $data = $result->fetch_assoc();
                $totalRegistros = $data['totalRegistros'];
            }
        }
        return $totalRegistros;
    }

    function obtenerCantidadRegistros($conexionDB, $nombreTabla){
        
        $totalRegistros = 0;

        $query = $conexionDB->prepare("
            SELECT COUNT(*) as totalRegistros
            FROM $nombreTabla
        ");
        if($query){
            if($query->execute()){
                $result = $query->get_result();
                $data = $result->fetch_assoc();
                $totalRegistros = $data['totalRegistros'];
            }
        }
        
        return $totalRegistros;
    }
 ?>