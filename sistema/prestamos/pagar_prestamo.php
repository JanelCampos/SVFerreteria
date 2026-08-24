<?php 
    session_start();
    include "../../conexion.php";
    header('Content-Type: application/json');

     // Obtener los datos POST enviados
    $input = json_decode(file_get_contents('php://input'), true);

    $idPrestamo = isset($input['idPrestamo']) ? $input['idPrestamo'] : null;
    $nombrePrestamista = isset($input['nombrePrestamista']) ? $input['nombrePrestamista'] : null;
    $montoPagar = isset($input['montoPagar']) ? $input['montoPagar'] : null;
    $monto = isset($input['monto']) ? $input['monto'] : null;
    $metodoPago = isset($input['metodoDePago']) ? $input['metodoDePago'] : null;

    $query = $conexionDB->prepare("SELECT montoPagar FROM prestamos WHERE idPrestamo = ?");

    if($query){
        $query->bind_param("i",$idPrestamo);
        if($query->execute()){
            $result = $query->get_result();
            $data = $result->fetch_assoc();
            $montoPendiente = $montoPagar - $monto;

            if($montoPendiente === 0){

                //actualizar prestamo
                $query_update = $conexionDB->prepare("
                    UPDATE prestamos 
                    SET montoPagar = ?, estado = ?
                    WHERE idPrestamo = ?
                ");
                if($query_update){
                    $estado = 1;
                    $query_update->bind_param("dii",$montoPendiente,$estado,$idPrestamo);
                    if($query_update->execute()){

                        //actualizar cuotas
                        $query_update = $conexionDB->prepare("
                            UPDATE cuotas
                            SET estado = ?
                            WHERE idPrestamo = ?
                        ");
                        if($query_update){
                            $estado = 1;
                            $query_update->bind_param("ii",$estado,$idPrestamo);
                            if($query_update->execute()){
                                //registrar en caja
                                include "../includes/resta_caja_con_utilidad.php";

                                $query_insert = $conexionDB->prepare("
                                    INSERT INTO caja(Actividad,Monto_salida,totalEfectivoDia,totalTarjetaDia,totalCajaDia,utilidadDia,TotalEfectivo,TotalTarjeta,Total_caja,Utilidad,Cod_Empleado,Estado)
                                    VALUES(?,?,?,?,?,?,?,?,?,?,?,?)
                                ");
                                if($query_insert){
                                    $actividad = "Pago prestamo";
                                    $estado = "Abierto";
                                    $query_insert->bind_param("sdddddddddis",$actividad,$monto,$totalEfectivoDia,$totalTarjetaDia,$totalCajaDia,$utilidadDia,$totalEfectivo,$totalTarjeta,$totalCaja,$utilidadTotal,$user,$estado);
                                    if($query_insert->execute()){
                                        $query_insert_gastos = $conexionDB->prepare("
                                            INSERT INTO gastos(montoGasto,fechaGasto,medioPago,tipoGasto,descripcion)
                                            VALUES (?,?,?,?,?)
                                        ");
                                        $tipoGasto = 'personal';
                                        $descripcion = 'Pago de prestamo';
                                        $fechaPago = date('Y-m-d');
                                        $query_insert_gastos->bind_param("dssss",$monto,$fechaPago,$metodoPago,$tipoGasto,$descripcion);
                                        if($query_insert_gastos->execute()){
                                            echo json_encode(['resultado' => true, 'mensaje' => 'Pago exitoso']);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }else{
                $query_update = $conexionDB->prepare("
                    UPDATE prestamos
                    SET montoPagar = ?
                    WHERE idPrestamo = ?
                ");
                if($query_update){
                    $query_update->bind_param("di",$montoPendiente,$idPrestamo);
                    if($query_update->execute()){
                        //registrar en caja
                        include "../includes/resta_caja_sin_utilidad.php";

                        $query_insert = $conexionDB->prepare("
                            INSERT INTO caja(Actividad,Monto_salida,TotalEfectivo,TotalTarjeta,Total_caja,Utilidad,Cod_Empleado,Estado)
                            VALUES(?,?,?,?,?,?,?,?)
                        ");
                        if($query_insert){
                            $actividad = "Pago prestamo";
                            $estado = "Abierto";
                            $query_insert->bind_param("sdddddis",$actividad,$monto,$totalEfectivo,$totalTarjeta,$totalCaja,$utilidadTotal,$user,$estado);
                            if($query_insert->execute()){
                                $query_insert_gastos = $conexionDB->prepare("
                                    INSERT INTO gastos(montoGasto,fechaGasto,medioPago,tipoGasto,descripcion)
                                    VALUES (?,?,?,?,?)
                                ");
                                $tipoGasto = 'personal';
                                $descripcion = 'Pago de prestamo';
                                $fechaPago = date('Y-m-d');
                                $query_insert_gastos->bind_param("dssss",$monto,$fechaPago,$metodoPago,$tipoGasto,$descripcion);
                                if($query_insert_gastos->execute()){
                                    echo json_encode(['resultado' => true, 'mensaje' => 'Pago exitoso']);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
?>