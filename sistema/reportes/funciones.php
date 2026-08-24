<?php 
    include "../../conexion.php";

    function actualizarEstadoDeVenta($conexionDB,$idVenta,$estadoVenta,$efectivo,$tarjeta,$saldo){
        $query_update = $conexionDB->prepare("
            UPDATE ventas
            SET Estado = ?, efectivo = ?, tarjeta = ?, saldo = ? 
            WHERE IdVenta = ?
        ");
        $query_update->bind_param("sdddi",$estadoVenta,$efectivo,$tarjeta,$saldo,$idVenta);
        if($query_update->execute()){
            return true;
        }else{
            return false;
        }
    }

    function registrarVentaPendienteEnCliente($conexionDB,$dniCliente,$estadoVenta,$efectivo,$tarjeta,$utilidad,$totalVenta){        
        if($estadoVenta === 'saldo'){
            $totalVenta = 0;
            $utilidad = 0;
            // $totalIngreso = $efectivo + $tarjeta;
            // $capital = $totalVenta - $utilidad;
            // $totalVenta = $totalIngreso;
            // if($totalIngreso > $capital){
            //     $gananciaParcial = $totalIngreso - $capital;
            //     $utilidad = $gananciaParcial;
            // }else{
            //     $utilidad = 0;
            // }
        }

        $query = $conexionDB->prepare("
            SELECT *
            FROM clientes
            WHERE Dni = ?
        ");
        if($query){
            $query->bind_param("i",$dniCliente);
            if($query->execute()){
                $resultCliente = $query->get_result();
                $data = $resultCliente->fetch_assoc();
                $cantidadCompras = $data['cantidadCompras'];
                $montoCompras = $data['montoCompras'];
                $gananciaGenerada = $data['gananciaGenerada'];
                if($cantidadCompras === null ){
                    $cantidadCompras = 0;
                }
                if($montoCompras === null){
                    $montoCompras = 0;
                }
                if($gananciaGenerada === null){
                    $gananciaGenerada = 0;
                }

                $montoCompras += $totalVenta;
                $gananciaGenerada += $utilidad;

                $query_update = $conexionDB->prepare("
                    UPDATE clientes
                    SET montoCompras = ?, gananciaGenerada = ?
                    WHERE Dni = ?
                ");
                if($query_update){
                    $query_update->bind_param("ddi",$montoCompras,$gananciaGenerada,$dniCliente);
                    if($query_update->execute()){
                        return true;
                    }else {
                        return false;
                    }
                }
            }
        }
    }

    function registrarVentaPendienteEnCaja($conexionDB,$metodoPago,$totalVenta,$efectivo,$tarjeta,$vuelto,$metodoPagoVuelto,$saldo,$utilidad,$estadoVenta){
        include "../includes/total_caja.php";

        if($metodoPago === 'efectivo'){
            $totalEfectivo += $efectivo;
            $totalEfectivoDia += $efectivo;
        }else if($metodoPago === 'tarjeta'){
            $totalTarjeta += $tarjeta;
            $totalTarjetaDia += $tarjeta;
        }else{
            $totalEfectivo += $efectivo;
            $totalTarjeta += $tarjeta;
            $totalTarjetaDia += $tarjeta;
            $totalEfectivoDia += $efectivo;
        }

        if($metodoPagoVuelto === 'efectivo'){
            $totalEfectivo -= $vuelto;
            $totalEfectivoDia -= $vuelto;
        }else{
            $totalTarjeta -= $vuelto;
            $totalTarjetaDia -= $vuelto;
        }

        $total_ingreso = $efectivo + $tarjeta - $vuelto;
        $totalCaja += $totalVenta - $saldo;
        $totalCajaDia += $totalVenta - $saldo;

        if($saldo == 0){
            $utilidadTotal += $utilidad;
            $utilidadDia += $utilidad;
        }else{
            $utilidadTotal += 0;
            $utilidadDia += 0;
        }

        $query_insert = $conexionDB->prepare("
            INSERT INTO caja(Actividad,Monto_inicial,totalEfectivoDia,totalTarjetaDia,totalCajaDia,utilidadDia,TotalEfectivo,TotalTarjeta,Total_caja,Utilidad,Cod_Empleado,Estado)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $actividad = 'Pago venta pendiente';
        $estado = 'Abierto';
        $query_insert->bind_param("sdddddddddis",$actividad,$total_ingreso,$totalEfectivoDia,$totalTarjetaDia,$totalCajaDia,$utilidadDia,$totalEfectivo,$totalTarjeta,$totalCaja,$utilidadTotal,$user,$estado);
        if($query_insert->execute()){
            return true;
        }else{
            return false;
        }
    }

    function eliminarDetalleVentaArticulo($conexionDB,$idVenta){
        $query_delete = $conexionDB->prepare("
            DELETE
            FROM detalle_venta_articulos
            WHERE Cod_Venta = ?
        ");
        $query_delete->bind_param("i",$idVenta);
        if($query_delete->execute()){
            return true;
        }else{
            return false;
        }
    }

    function eliminarDetalleFactura($conexionDB,$idVenta){
        $query_delete = $conexionDB->prepare("
            DELETE
            FROM detallefactura
            WHERE idVenta = ?
        ");
        $query_delete->bind_param("i",$idVenta);
        if($query_delete->execute()){
            return true;
        }else{
            return false;
        }
    }

    function eliminarVenta($conexionDB, $idVenta){
        $query_delete = $conexionDB->prepare("
            DELETE 
            FROM ventas
            WHERE IdVenta = ?
        ");
        $query_delete->bind_param("i",$idVenta);
        if($query_delete->execute()){
            return true;
        }else{
            return false;
        }
    }

    function cambiarEstadoDeVenta($conexionDB,$idVenta,$nuevoEstado){
        $query_update = $conexionDB->prepare("
            UPDATE ventas
            SET Estado = ?, saldo = 0
            WHERE IdVenta = ?
        ");
        $query_update->bind_param("si",$nuevoEstado,$idVenta);
        if($query_update->execute()){
            return true;
        }else{
            return false;
        }
    }

    function quitarVentaAlCliente($conexionDB,$dniCliente,$estadoVenta,$totalVenta,$utilidadVenta,$saldo){

        if($estadoVenta === 'pendiente' || $estadoVenta === 'saldo'){
            $totalVenta = 0;
            $utilidadVenta = 0;
        }

        // if($estadoVenta === 'saldo'){
        //     $dineroPagado = $totalVenta - $saldo;
        //     if($dineroPagado > $utilidadVenta){
        //         $gananciaParcial = $dineroPagado - $utilidadVenta;
        //         $utilidadVenta = $gananciaParcial;
        //     }
        //     $totalVenta = $dineroPagado;
        //     $utilidadVenta = 0;
        // }

        $query_update = $conexionDB->prepare("
            UPDATE clientes
            SET cantidadCompras = cantidadCompras - 1, montoCompras = montoCompras - ?, gananciaGenerada = gananciaGenerada - ?
            WHERE Dni = ?
        ");
        $query_update->bind_param("ddi",$totalVenta,$utilidadVenta,$dniCliente);
        if($query_update->execute()){
            return true;
        }else{
            return false;
        }
    }

    function quitarVentaDeCaja($conexionDB,$estadoVenta,$totalVenta,$actividad,$estadoCaja,$metodoPago,$utilidadVenta,$saldo){
        include "../includes/total_caja.php";

        if($estadoVenta === 'pagado'){
            if($metodoPago === 'efectivo'){
                $totalEfectivo -= $totalVenta;
                $totalEfectivoDia -= $totalVenta;
            }
            if($metodoPago === 'tarjeta'){
                $totalTarjeta -= $totalVenta;
                $totalTarjetaDia -= $totalVenta;
            }

            $totalCaja -= $totalVenta;
            $totalCajaDia -= $totalVenta;
            $utilidadTotal -= $utilidadVenta;
            $utilidadDia -= $utilidadVenta;
        }

        if($estadoVenta === 'saldo'){
            $dineroPagado = $totalVenta - $saldo;
            $dineroCapital = $totalVenta - $utilidadVenta;
            if($metodoPago === 'efectivo'){
                $totalEfectivo -= $dineroPagado;
                $totalEfectivoDia -= $dineroPagado;
            }else{
                $totalTarjeta -= $dineroPagado;
                $totalTarjetaDia -= $dineroPagado;
            }

            $totalCaja -= $dineroPagado;
            $totalCajaDia -= $dineroPagado;
            if($dineroCapital > $utilidadVenta){
                $gananciaParcial = $dineroCapital - $utilidadVenta;
                $utilidadTotal -= $gananciaParcial;
                $utilidadDia -= $gananciaParcial;
            }
            $totalVenta = $dineroPagado;
        }

        $query_insert = $conexionDB->prepare("
            INSERT INTO caja(Actividad,Monto_salida,totalEfectivoDia,totalTarjetaDia,totalCajaDia,utilidadDia,TotalEfectivo,TotalTarjeta,Total_caja,Utilidad,Cod_Empleado,Estado)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        if($query_insert){
            $query_insert->bind_param("sdddddddddis",$actividad,$totalVenta,$totalEfectivoDia,$totalTarjetaDia,$totalCajaDia,$utilidadDia,$totalEfectivo,$totalTarjeta,$totalCaja,$utilidadTotal,$user,$estadoCaja);

            if($query_insert->execute()){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    function restarCajaConUtilidad($conexionDB,$monto,$utilidad,$metodoPago){
        include "../includes/total_caja.php";

        if($metodoPago == "efectivo"){
            $totalEfectivo = $totalEfectivo - $monto;
        }else{
            $totalTarjeta = $totalTarjeta - $monto;
        }
    
        $totalCaja = $totalCaja - $monto;
        $utilidadTotal = $utilidadTotal - $utilidad;

        $query_insert = $conexionDB->prepare("
            INSERT INTO caja(Actividad,Monto_salida,TotalEfectivo,TotalTarjeta,Total_caja,Utilidad,Cod_Empleado,Estado)
            VALUES(?,?,?,?,?,?,?,?)
        ");
        if($query_insert){
            $actividad = "Eliminar venta";
            $estado = "Abierto";

            $query_insert->bind_param("sdddddis",$actividad,$monto,$totalEfectivo,$totalTarjeta,$totalCaja,$utilidadTotal,$user,$estado);

            if($query_insert->execute()){

            }else{

            }
        }else{
            
        }
    }

    function descontarVentaCaja($conexionDB, $id, $metodoPago){
        $query = $conexionDB->prepare("
            SELECT Total, utilidad
            FROM ventas
            WHERE IdVenta = ?
        ");
        if($query){
            $query->bind_param("i",$id);
            if($query->execute()){
                $result = $query->get_result();
                $data = $result->fetch_assoc();
                
                $totalVenta = $data['Total'];
                $utilidad = $data['utilidad'];

                restarCajaConUtilidad($conexionDB,$totalVenta,$utilidad,$metodoPago);
            }
        }else{

        }
    }

    function actualizarStock($conexionDB, $id){
        $query = $conexionDB->prepare("
            SELECT Cod_Articulo, Cantidad
            FROM detalle_venta_articulos
            WHERE Cod_Venta = ?
        ");

        if($query){
            $query->bind_param("i",$id);

            if($query->execute()){
                $result = $query->get_result();
                while($data = $result->fetch_assoc()){
                    $idArticulo = $data['Cod_Articulo'];
                    $cantidad = $data['Cantidad'];

                    $query_update = $conexionDB->prepare("
                        UPDATE articulos
                        SET Cantidad = Cantidad + ?
                        WHERE IdArticulo = ?
                    ");
                    if($query_update){
                        $query_update->bind_param("ii",$cantidad,$idArticulo);

                        if(!$query_update->execute()){
                            return false;
                        }
                    }else{
                        return false;
                    }
                }
                return true; 
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    function eliminarRegistro($conexionDB, $consulta, $id){
        $query_delete = $conexionDB->prepare("
            $consulta
        ");
        if($query_delete){
            $query_delete->bind_param("i",$id);
            if($query_delete->execute()){
                return "Operaxión exitosa";
            }else{
                return "Ocurrio un error con la consulta";
            }
        }else{
            return "Ocurrio un error con la consulta";
        }
    }

    function getVentasPorMes($conexionDB){
        $query = "SELECT 
                        MONTH(ventas.Fecha) AS mes,
                        YEAR(ventas.Fecha) as anio, 
                        SUM(ventas.Total) + COALESCE((
                            SELECT SUM(ventalibre.montoVentaLibre) 
                            FROM ventalibre 
                            WHERE MONTH(ventalibre.fechaVentaLibre) = MONTH(ventas.Fecha) 
                            AND YEAR(ventalibre.fechaVentaLibre) = YEAR(ventas.Fecha)
                            GROUP BY YEAR(ventalibre.fechaVentaLibre), MONTH(ventalibre.fechaVentaLibre)
                        ), 0) AS totalVentas 
                    FROM ventas 
                    GROUP BY anio, mes 
                    ORDER BY anio, mes;";

        $result = mysqli_query($conexionDB, $query);
    
        // Inicializar array con 12 posiciones para los meses (de 0 a 11, que representan enero a diciembre)
        $ventasPorMes = [];
    
        while ($row = mysqli_fetch_assoc($result)) {
            $anio = $row['anio'];
            $mes = str_pad($row['mes'], 2, "0", STR_PAD_LEFT); // convierte 1 → 01, etc.
            $clave = "$anio-$mes";
            
            // Rellenar el array con las ventas de cada mes
            $ventasPorMes[$clave] = (float)$row['totalVentas'];
        }
    
        return $ventasPorMes;
    }    

    function getGananciasPorMes($conexionDB) {
        $query = "SELECT 
                    MONTH(v.Fecha) AS mes, 
                    YEAR(v.Fecha) AS anio,
                    SUM(v.utilidad) + COALESCE((
                        SELECT SUM(vl.montoVentaLibre) 
                        FROM ventalibre vl 
                        WHERE vl.tipoIngreso = 'personal' 
                          AND MONTH(vl.fechaVentaLibre) = MONTH(v.Fecha)
                          AND YEAR(vl.fechaVentaLibre) = YEAR(v.Fecha)
                    ), 0) AS ganancias 
                  FROM ventas v
                  GROUP BY YEAR(v.Fecha), MONTH(v.Fecha)
                  ORDER BY YEAR(v.Fecha), MONTH(v.Fecha);";
    
        $result = mysqli_query($conexionDB, $query);
    
        $gananciasPorMes = [];
    
        while ($row = mysqli_fetch_assoc($result)) {
            $anio = $row['anio'];
            $mes = str_pad($row['mes'], 2, "0", STR_PAD_LEFT); // convierte 1 → 01, etc.
            $clave = "$anio-$mes";

            $gananciasPorMes[$clave] = (float)$row['ganancias'];
        }
    
        return $gananciasPorMes;
    }

    function gastosPorMes($conexionDB){
        $query = "SELECT
                    MONTH(fechaGasto) as mes,
                    YEAR(fechaGasto) as anio, 
                    SUM(montoGasto) as gastos 
                    FROM gastos 
                    WHERE tipoGasto = 'personal'
                    GROUP BY YEAR(fechaGasto), MONTH(fechaGasto);";

        $result = mysqli_query($conexionDB, $query);
        
        $gastosPorMes = [];
    
        while ($row = mysqli_fetch_assoc($result)) {
            $anio = $row['anio'];
            $mes = str_pad($row['mes'], 2, "0", STR_PAD_LEFT); // convierte 1 → 01, etc.
            $clave = "$anio-$mes";

            $gastosPorMes[$clave] = (float)$row['gastos'];
        }
    
        return $gastosPorMes;
    }

    function gastosCapitalPorMes($conexionDB){
        $query = "SELECT 
                    MONTH(fechaGasto) as mes,
                    YEAR(fechaGasto) as anio,
                    SUM(montoGasto) as gastos 
                    FROM gastos 
                    WHERE tipoGasto = 'capital' 
                    GROUP BY YEAR(fechaGasto), MONTH(fechaGasto)";

        $result = mysqli_query($conexionDB, $query);
        
        $gastosCapitalPorMes = [];
    
        while ($row = mysqli_fetch_assoc($result)) {
            $anio = $row['anio'];
            $mes = str_pad($row['mes'], 2, "0", STR_PAD_LEFT); // convierte 1 → 01, etc.
            $clave = "$anio-$mes";
            // Rellenar el array con las ventas de cada mes
            $gastosCapitalPorMes[$clave] = (float)$row['gastos'];
        }
    
        return $gastosCapitalPorMes;
    }

    function quitarGastoDeCaja($conexionDB,$idGasto,$montoGasto,$medioPago,$tipoGasto){
        $query_delete = $conexionDB->prepare("
            DELETE
            FROM gastos
            WHERE idGastos = ? 
        ");
        $query_delete->bind_param("i",$idGasto);
        if($query_delete->execute()){ 
            include "../includes/total_caja.php";

            if($medioPago === 'efectivo'){
                $totalEfectivo += $montoGasto;
                $totalEfectivoDia += $montoGasto;
            }else{
                $totalTarjeta += $montoGasto;
                $totalTarjetaDia += $montoGasto;
            }

            if($tipoGasto === 'personal'){
                $utilidadDia += $montoGasto;
                $utilidadTotal += $montoGasto;
            }

            $totalCaja += $montoGasto;
            $totalCajaDia += $montoGasto;

            $query_insert = $conexionDB->prepare("
                INSERT INTO caja(Actividad,Monto_salida,totalEfectivoDia,totalTarjetaDia,totalCajaDia,utilidadDia,TotalEfectivo,TotalTarjeta,Total_caja,Utilidad,Cod_Empleado,Estado)
                VALUES(?,?,?,?,?,?,?,?,?,?,?,?)
            ");

            if($query_insert){
                $actividad = "Anular gasto";
                $estado = "Abierto";

                $query_insert->bind_param("sdddddddddis",$actividad,$montoGasto,$totalEfectivoDia,$totalTarjetaDia,$totalCajaDia,$utilidadDia,$totalEfectivo,$totalTarjeta,$totalCaja,$utilidadTotal,$user,$estado);

                if($query_insert->execute()){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
?>