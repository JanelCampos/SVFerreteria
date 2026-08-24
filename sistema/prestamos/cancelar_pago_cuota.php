<?php 
    session_start();
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idCuota = isset($input['idCuota']) ? $input['idCuota'] : null;
    $idPrestamo = isset($input['idPrestamo']) ? $input['idPrestamo'] : null;
    $monto = isset($input['montoCuota']) ? $input['montoCuota'] : null;
    $metodoPago = isset($input['metodoPago']) ? $input['metodoPago'] : null;

    $query_update = $conexionDB->prepare("
        UPDATE cuotas 
        SET estado = 0 
        WHERE idCuota = ?
    ");
    $query_update->bind_param("i",$idCuota);
    if($query_update->execute()){
        $query_updatePrestamo = $conexionDB->prepare("
            UPDATE prestamos
            SET montoPagar = montoPagar + ?
            WHERE idPrestamo = ?
        ");
        $query_updatePrestamo->bind_param("di",$monto,$idPrestamo);
        if($query_updatePrestamo->execute()){
            $query = $conexionDB->prepare("
                SELECT * 
                FROM cuotas 
                WHERE idPrestamo = ? AND estado = 0
            ");
            $query->bind_param("i",$idPrestamo);
            if($query->execute()){
                $result = $query->get_result();
                $row = $result->num_rows;
                if($row > 0){
                    $query_update = $conexionDB->prepare("
                        UPDATE prestamos 
                        SET estado = 0 
                        WHERE idPrestamo = ?
                    ");
                    $query_update->bind_param("i",$idPrestamo);
                    if($query_update->execute()){
                        //registrar en caja
                        include "../includes/suma_caja_con_utilidad.php";
                        
                        $query_insert = $conexionDB->prepare("
                            INSERT INTO caja(Actividad,Monto_inicial,totalEfectivoDia,totalTarjetaDia,totalCajaDia,utilidadDia,TotalEfectivo,TotalTarjeta,Total_caja,Utilidad,Cod_Empleado,Estado)
                            VALUES(?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?)
                        ");
                        if($query_insert){
                            $actividad = "Cancelar pago cuota";
                            $estado = "Abierto";
                            $query_insert->bind_param("sdddddddddis",$actividad,$monto,$totalEfectivoDia,$totalTarjetaDia,$totalCajaDia,$utilidadDia,$totalEfectivo,$totalTarjeta,$totalCaja,$utilidadTotal,$user,$estado);
                            
                            if($query_insert->execute()){
                                $query_insertGasto = $conexionDB->prepare("
                                    INSERT INTO gastos(montoGasto,fechaGasto,medioPago,tipoGasto,descripcion)
                                    VALUES (?,?,?,?,?)
                                ");
                                $tipoGasto = 'personal';
                                $descripcion = 'Cancelacion de pago de cuota';
                                $fechaPago = date('Y-m-d');
                                $montoCancelacion = -$monto;
                                $query_insertGasto->bind_param("dssss",$montoCancelacion,$fechaPago,$metodoPago,$tipoGasto,$descripcion);
                                if($query_insertGasto->execute()){
                                    echo json_encode(['resultado' => true, 'mensaje' => 'El pago de cuota ha sido anulado']);
                                }else{
                                    echo json_encode(['resultado' => false, 'mensaje' => 'No se registro el gasto']);
                                }
                            }
                        }
    
                    }
                }else{
                    echo json_encode(['resultado' => false, 'mensaje' => 'El pago de cuota no se pudo anular']);
                }
            }
        }
    }
?>