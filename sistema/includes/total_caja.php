<?php 
    $user = intval($_SESSION['idUser']);
    $query = $conexionDB->prepare("
        SELECT totalEfectivoDia, totalTarjetaDia, totalCajaDia, utilidadDia,TotalEfectivo, TotalTarjeta, Total_caja, Utilidad 
        FROM caja 
        WHERE IdCaja = (
            SELECT MAX(IdCaja) 
            FROM caja 
        )
    ");

    if($query){

        if($query->execute()){
            $result = $query->get_result();
            $row = $result->num_rows;
            if($row > 0){
                $data = $result->fetch_assoc();
                $totalEfectivoDia = $data['totalEfectivoDia'];
                $totalTarjetaDia = $data['totalTarjetaDia'];
                $totalCajaDia = $data['totalCajaDia'];
                $utilidadDia = $data['utilidadDia'];
                $totalEfectivo = $data['TotalEfectivo'];
                $totalTarjeta = $data['TotalTarjeta'];
                $totalCaja = $data['Total_caja'];
                $utilidadTotal = $data['Utilidad'];
            }else{
                $totalEfectivoDia = 0;
                $totalTarjetaDia = 0;
                $totalCajaDia = 0;
                $utilidadDia = 0;
                $totalEfectivo = 0;
                $totalTarjeta = 0;
                $totalCaja = 0;
                $utilidadTotal = 0;
            }
        }
    }

?>