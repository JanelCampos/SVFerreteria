<?php 
    session_start();
    include "../../conexion.php";
    include "../includes/zona_horaria.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idCuota = isset($input['idCuota']) ? $input['idCuota'] : null;
    $idPrestamo = isset($input['idPrestamo']) ? $input['idPrestamo'] : null;
    $monto = isset($input['montoCuota']) ? $input['montoCuota'] : null;
    $metodoPago = isset($input['metodoPago']) ? $input['metodoPago'] : null;

    $query_update = $conexionDB->prepare("
        UPDATE cuotas 
        SET estado = 1 
        WHERE idCuota = ?
    ");
    $query_update->bind_param("i",$idCuota);
    if($query_update->execute()){
        include "../includes/resta_caja_con_utilidad.php";

        $query_insert = $conexionDB->prepare("
            INSERT INTO caja(Actividad,Monto_salida,totalEfectivoDia,totalTarjetaDia,totalCajaDia,utilidadDia,TotalEfectivo,TotalTarjeta,Total_caja,Utilidad,Cod_Empleado,Estado)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $actividad = 'Pago de cuota';
        $estado = 'Abierto';
        $query_insert->bind_param("sdddddddddis",$actividad,$monto,$totalEfectivoDia,$totalTarjetaDia,$totalCajaDia,$utilidadDia,$totalEfectivo,$totalTarjeta,$totalCaja,$utilidadTotal,$user,$estado);
        if($query_insert->execute()){
            $query_insert_gastos = $conexionDB->prepare("
                INSERT INTO gastos(montoGasto,fechaGasto,medioPago,tipoGasto,descripcion)
                VALUES (?,?,?,?,?)
            ");
            $tipoGasto = 'personal';
            $descripcion = 'Pago de cuota';
            $fechaPago = date('Y-m-d');
            $query_insert_gastos->bind_param("dssss",$monto,$fechaPago,$metodoPago,$tipoGasto,$descripcion);
            $query_insert_gastos->execute();

            $query_updatePrestamo = $conexionDB->prepare("
                UPDATE prestamos
                SET montoPagar = montoPagar - ?
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
                        echo json_encode(['resultado' => true, 'mensaje' => 'Cuota pagada con éxito']);                
                    }else{
                        $query_update = $conexionDB->prepare("
                            UPDATE prestamos 
                            SET estado = 1 
                            WHERE idPrestamo = ?
                        ");
                        $query_update->bind_param("i",$idPrestamo);
                        if($query_update->execute()){
                            echo json_encode(['resultado' => true, 'mensaje' => 'El préstamo ha sido pagado completamente']);                
                        }else{
                            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo actualizar el estado del préstamo']);                
                        }
                    }
                }else{
                    echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo actualizar el préstamo']);                
                }
            }
        }else{
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo registrar en caja']);                
        }
    }else{
        echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo pagar la cuota']);                
    }
?>