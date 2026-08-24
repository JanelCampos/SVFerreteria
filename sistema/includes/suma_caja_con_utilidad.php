<?php 
    include "total_caja.php";

    if($totalEfectivo == ''){
        $totalEfectivo = 0;
    }

    if($totalEfectivoDia == ''){
        $totalEfectivoDia = 0;
    }

    if($totalTarjeta == ''){
        $totalTarjeta = 0;
    }

    if($totalTarjetaDia == ''){
        $totalTarjetaDia = 0;
    }

    if($totalCaja == ''){
        $totalCaja = 0;
    }
    if($totalCajaDia == ''){
        $totalCajaDia = 0;
    }

    if($utilidadTotal == ''){
        $utilidadTotal = 0;
    }

    if($utilidadDia == ''){
        $utilidadDia = 0;
    }

    if($metodoPago == "efectivo"){
        $totalEfectivo = $totalEfectivo + $monto;
        $totalEfectivoDia += $monto;
    }else{
        $totalTarjeta = $totalTarjeta + $monto;
        $totalTarjetaDia += $monto;
    }

    $totalCaja = $totalCaja + $monto;
    $totalCajaDia += $monto;

    $utilidadTotal = $utilidadTotal + $monto;
    $utilidadDia += $monto;
?>