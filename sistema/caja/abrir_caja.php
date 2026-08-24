<?php 
    session_start();
    include "../../conexion.php";
    header('Content-Type: application/json');

    // Obtener los datos POST enviados
    $input = json_decode(file_get_contents('php://input'), true);
    $monto = isset($input['monto']) ? $input['monto'] : '';
    $id = isset($input['id']) ? $input['id'] : '';

    $query = $conexionDB->prepare("
        SELECT Estado 
        FROM caja 
        WHERE IdCaja = (SELECT MAX(IdCaja) FROM caja)
    ");
    if($query->execute()){
        $result = $query->get_result();
        $data = $result->fetch_assoc();
        $estadoActual = $data['Estado'];
        
        if($estadoActual === 'Abierto'){
            echo json_encode(['resultado' => false, 'mensaje' => 'La caja ya está abierta']);
        }else{
            if($monto <= 0 || $monto == ''){
                echo json_encode(['resultado' => false, 'mensaje' => 'Ingrese un valor válido']);
            }else{
                include "../includes/total_caja.php";
        
                $query_insert = $conexionDB->prepare("
                    INSERT INTO caja(Actividad,Monto_inicial,totalEfectivoDia,totalTarjetaDia,totalCajaDia,utilidadDia,TotalEfectivo,TotalTarjeta,Total_caja,utilidad,Cod_Empleado,Estado)
                    VALUES(?,?,?,?,?,?,?,?,?,?,?,?)
                ");
                $totalEfectivoDia += $monto;
                $totalCajaDia += $monto;
                $actividad = 'Abrir caja';
                $estado = 'Abierto';
                $query_insert->bind_param("sdddddddddis",$actividad,$monto,$totalEfectivoDia,$totalTarjetaDia,$totalCajaDia,$utilidadDia,$totalEfectivo,$totalTarjeta,$totalCaja,$utilidadTotal,$user,$estado);
                if($query_insert->execute()){
                    echo json_encode(['resultado' => true, 'mensaje' => 'La caja se abrió correctamente']);
                }else{
                    echo json_encode(['resultado' => false, 'mensaje' => 'La caja no se pudo abrir']);
                }
            }
        }
    }
?>