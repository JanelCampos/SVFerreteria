<?php

    function procesarVenta($conexionDB,$efectivo,$tarjeta,$totalGanancias,$vuelto,$metodoVuelto,$estadoVenta,$totalVenta,$fechaVenta,$dniCliente,$metodoPago){
        $codVendedor = isset($_SESSION['idUser']) ? $_SESSION['idUser'] : null;
        $lastInserIdCaja = registrarEnCaja($conexionDB,$efectivo,$tarjeta,$totalGanancias,$vuelto,$metodoVuelto,$estadoVenta,$totalVenta);
        $lastInsertIdVenta = registrarVenta($conexionDB,$fechaVenta,$lastInserIdCaja,$dniCliente,$metodoPago,$efectivo,$tarjeta,$totalVenta,$totalGanancias,$estadoVenta,$vuelto,$codVendedor);
        registrarDetalleVenta($conexionDB,$lastInsertIdVenta);
        registrarDetalleFactura($conexionDB,$lastInsertIdVenta,$metodoPago);
        registrarCliente($conexionDB,$dniCliente,$lastInsertIdVenta,$estadoVenta);
        limpiarDatosTemporales($conexionDB);
        $queryVenta = $conexionDB->prepare("
            SELECT IdVenta, dniCliente, Estado
            FROM ventas
            WHERE IdVenta = ?
        ");
        if($queryVenta){
            $queryVenta->bind_param("i",$lastInsertIdVenta);
            if($queryVenta->execute()){
                $result = $queryVenta->get_result();
                $dataVenta = $result->fetch_assoc();
                $dniVenta = $dataVenta['dniCliente'];
                $idVenta = $dataVenta['IdVenta'];
                $estado = $dataVenta['Estado'];
                echo json_encode(['resultado' => true, 'estado' => $estado, 'dniCliente' => $dniVenta, 'idVenta' => $idVenta]);
            }
        }
    }

    function registrarEnCaja($conexionDB,$efectivo,$tarjeta,$totalGanancias,$vuelto,$metodoVuelto,$estadoVenta,$totalVenta){
        include "../includes/total_caja.php";
        if($estadoVenta === 'pendiente'){
            $efectivo = 0;
            $tarjeta = 0;
            $vuelto = 0;
            $totalIngeso = 0;
            $totalGanancias = 0;
        }
        $saldo = 0;
        if($estadoVenta === 'saldo'){
            $saldo = $totalVenta - ($efectivo + $tarjeta);
            $totalGanancias = 0;
        }
        $totalEfectivoDia += $efectivo;
        $totalTarjetaDia += $tarjeta;
        $totalEfectivo += $efectivo;
        $totalTarjeta += $tarjeta;
        $totalIngreso = $efectivo + $tarjeta - $vuelto - $saldo;
        $totalCajaDia += $totalIngreso;
        $totalCaja += $totalIngreso;
        $utilidadDia += $totalGanancias;
        $utilidadTotal += $totalGanancias;
        
        if($metodoVuelto === 'efectivo'){
            $totalEfectivo = $totalEfectivo - $vuelto - $saldo;
            $totalEfectivoDia = $totalEfectivoDia - $vuelto - $saldo;
        }
        if($metodoVuelto === 'tarjeta'){
            $totalTarjeta = $totalTarjeta - $vuelto - $saldo;
            $totalTarjetaDia = $totalTarjetaDia - $vuelto - $saldo;
        }
        $idEmpleado = $_SESSION['idUser'];

        $query_insert = $conexionDB->prepare("
            INSERT INTO caja(Actividad,Monto_inicial,totalEfectivoDia,totalTarjetaDia,totalCajaDia,utilidadDia,TotalEfectivo,TotalTarjeta,Total_caja,Utilidad,Cod_Empleado,Estado)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        if($query_insert){
            $actividad = 'Venta de producto';
            $estado = 'Abierto';
            $query_insert->bind_param("sdddddddddis",$actividad,$totalIngreso,$totalEfectivoDia,$totalTarjetaDia,$totalCajaDia,$utilidadDia,$totalEfectivo,$totalTarjeta,$totalCaja,$utilidadTotal,$idEmpleado,$estado);
            if($query_insert->execute()){
                $lastInserIdCaja = $conexionDB->insert_id;
                return $lastInserIdCaja;
            }else{
                return false;
            }
        }
    }

    function registrarVenta($conexionDB,$fechaVenta,$lastInserIdCaja,$dniCliente,$metodoPago,$efectivo,$tarjeta,$totalVenta,$totalGanancias,$estadoVenta,$vuelto,$codVendedor = null){
        if($estadoVenta === 'pagado'){
            $saldo = 0;
        }
        if($estadoVenta === 'pendiente'){
            $saldo = $totalVenta;
        }
        if($estadoVenta === "saldo"){
            $saldo = $totalVenta - $efectivo - $tarjeta;
        }
        $query_insert = $conexionDB->prepare("
            INSERT INTO ventas(Fecha, Cod_Caja, dniCliente, Medio_Pago, efectivo, tarjeta, Total, utilidad, Estado, saldo, vuelto, Cod_Vendedor)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        if ($query_insert) {
            $query_insert->bind_param("sissddddsddi", $fechaVenta, $lastInserIdCaja, $dniCliente, $metodoPago, $efectivo, $tarjeta, $totalVenta, $totalGanancias, $estadoVenta, $saldo, $vuelto, $codVendedor);
            if ($query_insert->execute()) {
                $lastInsertIdVenta = $conexionDB->insert_id;
                return $lastInsertIdVenta;
            } else {
                return false;
            }
        }
        return false;
    }

    function registrarDetalleVenta($conexionDB,$codVenta){
        $query_insert = $conexionDB->prepare("
            INSERT INTO detalle_venta_articulos (Cod_Venta,Cod_Articulo, nombreArticulo, Cantidad,Precio_Compra,Precio_Venta,Ganancias,Total,Unidad,FactorAplicado,PorcentajeDescuento,PrecioConDescuento)
            SELECT ?,codArticulo,nombreArticulo,cantidad,Precio_Compra,precio_venta,
                   (PrecioConDescuento * cantidad) - (Precio_Compra * cantidad) as ganancias,
                   (cantidad * PrecioConDescuento) as total,
                   Unidad, FactorAplicado, PorcentajeDescuento, PrecioConDescuento
            FROM detalle_temp
        ");
        if($query_insert){
            $query_insert->bind_param("i",$codVenta);
            if($query_insert->execute()){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    function registrarDetalleFactura($conexionDB, $idVenta, $medioPago){
        $query_insert = $conexionDB->prepare("
            INSERT INTO detallefactura (idVenta, codArticulo, Cantidad, precio_venta, Medio_Pago, Unidad, FactorAplicado, PorcentajeDescuento, PrecioConDescuento)
            SELECT ?, codArticulo, cantidad, PrecioConDescuento, ?, Unidad, FactorAplicado, PorcentajeDescuento, PrecioConDescuento
            FROM detalle_temp
        ");
        if($query_insert){
            $query_insert->bind_param("is",$idVenta,$medioPago);
            if($query_insert->execute()){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    function registrarCliente($conexionDB,$dniCliente,$idVenta,$estadoVenta){
        $queryClienteTemp = $conexionDB->prepare("
            SELECT *
            FROM cliente_temp
        ");
        if($queryClienteTemp){
            if($queryClienteTemp->execute()){
                $resultClienteTemp = $queryClienteTemp->get_result();
                $dataClienteTemp = $resultClienteTemp->fetch_assoc();
                $nombreCliente = $dataClienteTemp['nombre'];
                $telefonoCliente = $dataClienteTemp['telefono'];
                $direccionCliente = $dataClienteTemp['direccion'];
                $fechaRegistroCliente = $dataClienteTemp['fechaRegistro']; 
            }
        }
        
        $queryVenta = $conexionDB->prepare("
            SELECT Total, utilidad, efectivo, tarjeta
            FROM ventas
            WHERE IdVenta = ?
        ");
        if($queryVenta){
            $queryVenta->bind_param("i",$idVenta);
            if($queryVenta->execute()){
                $resultVenta = $queryVenta->get_result();
                $dataVenta = $resultVenta->fetch_assoc();
                $totalVenta = $dataVenta['Total'];
                $efectivo = $dataVenta['efectivo'];
                $tarjeta = $dataVenta['tarjeta'];
                $gananciaVenta = $dataVenta['utilidad'];
            }
        }

        if($estadoVenta === 'pendiente' || $estadoVenta === 'saldo'){
            $totalVenta = 0;
            $gananciaVenta = 0;
        }
        
        // if($estadoVenta === 'saldo'){
        //     $totalIngreso = $efectivo + $tarjeta;
        //     $capital = $totalVenta - $gananciaVenta;
        //     $totalVenta = $totalIngreso;
        //     if($totalIngreso > $capital){
        //         $gananciaParcial = $totalIngreso - $capital;
        //         $gananciaVenta = $gananciaParcial;
        //     }else{
        //         $gananciaVenta = 0;
        //     }
        // }

        $query = $conexionDB->prepare("
            SELECT *
            FROM clientes
            WHERE Dni = ?
        ");
        if($query){
            $query->bind_param("i",$dniCliente);
            if($query->execute()){
                $resultCliente = $query->get_result();
                $row = $resultCliente->num_rows;
                if($row > 0){
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
                    $cantidadCompras += 1;
                    $montoCompras += $totalVenta;
                    $gananciaGenerada += $gananciaVenta;

                    $query_update = $conexionDB->prepare("
                        UPDATE clientes
                        SET cantidadCompras = ?, montoCompras = ?, gananciaGenerada = ?
                        WHERE Dni = ?
                    ");
                    if($query_update){
                        $query_update->bind_param("iddi",$cantidadCompras,$montoCompras,$gananciaGenerada,$dniCliente);
                        if($query_update->execute()){
                            return true;
                        }else {
                            return false;
                        }
                    }
                }else{
                    $query_insert = $conexionDB->prepare("
                        INSERT INTO clientes(Nombre,Dni,Telefono,direccion,Fecha_Registro,cantidadCompras,montoCompras,gananciaGenerada)
                        VALUES(?,?,?,?,?,?,?,?)
                    ");
                    if($query_insert){
                        $cantidadCompras = 1;
                        $montoCompras = $totalVenta;
                        $gananciaGenerada = $gananciaVenta;
                        $query_insert->bind_param("siissddd",$nombreCliente,$dniCliente,$telefonoCliente,$direccionCliente,$fechaRegistroCliente,$cantidadCompras,$montoCompras,$gananciaGenerada);
                        if($query_insert->execute()){
                            return true;
                        }else{
                            return false;
                        }
                    }
                }
            }
        }
    }

    function actualizarStock($conexionDB){
        $queryTemp = $conexionDB->prepare("
            SELECT codArticulo, cantidad
            FROM detalle_temp
        ");
        if($queryTemp){
            if($queryTemp->execute()){
                $resultTemp = $queryTemp->get_result();
                while($dataTemp = $resultTemp->fetch_assoc()){
                    $codArticulo = $dataTemp['codArticulo'];
                    $cantidadVendida = $dataTemp['cantidad'];
                    $queryArticulo = $conexionDB->prepare("
                        SELECT Cantidad
                        FROM articulos
                        WHERE IdArticulo = ?
                    ");
                    if($queryArticulo){
                        $queryArticulo->bind_param("i",$codArticulo);
                        if($queryArticulo->execute()){
                            $resultArticulo = $queryArticulo->get_result();
                            $dataArticulo = $resultArticulo->fetch_assoc();
                            $cantidadActual = $dataArticulo['Cantidad'];
                            $cantidadActualizada = $cantidadActual - $cantidadVendida;
                            $query_update = $conexionDB->prepare("
                                UPDATE articulos
                                SET Cantidad = ?
                                WHERE IdArticulo = ?
                            ");
                            if($query_update){
                                $query_update->bind_param("di",$cantidadActualizada,$codArticulo);
                                if(!$query_update->execute()){
                                    return false;
                                }
                            }
                        }
                    }
                }
                return true;
            }
        }
    }

    function limpiarDatosTemporales($conexionDB){
        $query_delete_clienteTemp = $conexionDB->prepare("
            DELETE 
            FROM cliente_temp
        ");
        if($query_delete_clienteTemp){
            if($query_delete_clienteTemp->execute()){
                $query_delete_productosTemp = $conexionDB->prepare("
                    DELETE
                    FROM detalle_temp
                ");
                if($query_delete_productosTemp){
                    if($query_delete_productosTemp->execute()){
                        return true;
                    }else{
                        return false;
                    }
                }
            }
        }
    }
       
?>