<?php 
    session_start();
    include "../../conexion.php";
    include "funciones.php";
    header('Content-Type: application/json');

// Leer la entrada JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    //verifica si hay cliente
    $queryClienteTemp = $conexionDB->prepare("
        SELECT *
        FROM cliente_temp
    ");
    if($queryClienteTemp){
        if($queryClienteTemp->execute()){
            $resultClienteTemp = $queryClienteTemp->get_result();
            $row = $resultClienteTemp->num_rows;
            if($row > 0){
                $dataClienteTemp = $resultClienteTemp->fetch_assoc();
                $queryDetalleTemp = $conexionDB->prepare("
                    SELECT *
                    FROM detalle_temp
                ");
                if($queryDetalleTemp){
                    if($queryDetalleTemp->execute()){
                        $resultDetalleTemp = $queryDetalleTemp->get_result();
                        $row = $resultDetalleTemp->num_rows;
                        if($row > 0){
                            $totalVenta = 0;
                            $totalGanancias = 0;
                            while($producto = $resultDetalleTemp->fetch_assoc()){
                                $precioEfectivo = (isset($producto['PrecioConDescuento']) && floatval($producto['PrecioConDescuento']) > 0)
                                    ? floatval($producto['PrecioConDescuento'])
                                    : floatval($producto['precio_venta']);
                                $totalVenta += $precioEfectivo * floatval($producto['cantidad']);
                                $totalGanancias += floatval($producto['Ganancias']);
                            }
                            $totalVenta = round($totalVenta, 2);
                            $totalVentaCentimos = (int) round($totalVenta * 100);
                            $estadoVenta = isset($input['estadoVenta']) ? $input['estadoVenta'] : null;
                            $metodoPago = isset($input['metodoPago']) ? $input['metodoPago'] : null;
                            $efectivo = round((float) (isset($input['efectivo']) ? $input['efectivo'] : 0), 2);
                            $tarjeta = round((float) (isset($input['tarjeta']) ? $input['tarjeta'] : 0), 2);
                            $efectivoCentimos = (int) round($efectivo * 100);
                            $tarjetaCentimos = (int) round($tarjeta * 100);
                            $vuelto = isset($input['vuelto']) ? $input['vuelto'] : null;
                            $metodoVuelto = isset($input['metodoVuelto']) ? $input['metodoVuelto'] : null;
                            $fechaVenta = isset($input['fechaVenta']) ? $input['fechaVenta'] : null;
                            $dniCliente = $dataClienteTemp['dni'];

                            if($estadoVenta === 'pagado'){
                                if($metodoPago === 'efectivo'){
                                    if($efectivoCentimos >= $totalVentaCentimos){
                                        procesarVenta($conexionDB,$efectivo,$tarjeta,$totalGanancias,$vuelto,$metodoVuelto,$estadoVenta,$totalVenta,$fechaVenta,$dniCliente,$metodoPago);
                                    }else{
                                        echo json_encode(['resultado' => false, 'message' => 'El monto de pago debe ser mayor al de la venta']);
                                    }
                                }else if($metodoPago === 'tarjeta'){
                                    if($tarjetaCentimos >= $totalVentaCentimos){
                                        procesarVenta($conexionDB,$efectivo,$tarjeta,$totalGanancias,$vuelto,$metodoVuelto,$estadoVenta,$totalVenta,$fechaVenta,$dniCliente,$metodoPago);
                                    }else{
                                        echo json_encode(['resultado' => false, 'message' => 'El monto de pago debe ser mayor al de la venta']);
                                    }
                                }else{
                                    if($efectivoCentimos + $tarjetaCentimos >= $totalVentaCentimos){
                                        procesarVenta($conexionDB,$efectivo,$tarjeta,$totalGanancias,$vuelto,$metodoVuelto,$estadoVenta,$totalVenta,$fechaVenta,$dniCliente,$metodoPago);
                                    }else{
                                        echo json_encode(['resultado' => false, 'message' => 'El monto de pago debe ser mayor al de la venta']);
                                    }
                                }
                            }else{
                                procesarVenta($conexionDB,$efectivo,$tarjeta,$totalGanancias,$vuelto,$metodoVuelto,$estadoVenta,$totalVenta,$fechaVenta,$dniCliente,$metodoPago);
                            }
                        }else{
                            echo json_encode(['resultado' => false, 'message' => 'No has seleccionado ningun producto']);
                        }
                    }else{
                        echo json_encode(['resultado' => false, 'message' => 'Error en la ejecucion de la consulta']);
                    }
                }else{
                    echo json_encode(['resultado' => false, 'message' => 'error en la consulta']);
                }
            }else{
                echo json_encode(['resultado' => false, 'message' => 'Tiene que ingresar un cliente']);
            }
        }else{
            echo json_encode(['resultado' => false, 'message' => 'Error en la ejecucion de la consulta']);
        }
    }else{
        echo json_encode(['resultado' => false, 'message' => 'error en la consulta']);
    }
?>