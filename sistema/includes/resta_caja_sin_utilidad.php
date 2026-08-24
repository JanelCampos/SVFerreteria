<?php 
    include "total_caja.php";

    if($metodoPago == "efectivo"){
        $totalEfectivo = $totalEfectivo - $monto;
        $totalEfectivoDia -= $monto;
    }else{
        $totalTarjeta = $totalTarjeta - $monto;
        $totalTarjetaDia -= $monto;
    }

    $totalCaja = $totalCaja - $monto;
    $totalCajaDia -= $monto;
?>