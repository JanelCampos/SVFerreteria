<?php 
    session_start();
    include "../../conexion.php";
    header('Content-Type: application/json');

    // Obtener los datos POST enviados
    $input = json_decode(file_get_contents('php://input'), true);

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
        if($estadoActual === 'Cerrado'){
            echo json_encode(['resultado' => false, 'mensaje' => 'La caja ya está cerrada']);
        }else{
            include "../includes/total_caja.php";
        
            $query_insert = $conexionDB->prepare("
                INSERT INTO caja(Actividad,Monto_inicial,totalEfectivoDia,totalTarjetaDia,totalCajaDia,utilidadDia,TotalEfectivo,TotalTarjeta,Total_caja,utilidad,Cod_Empleado,Estado)
                VALUES(?,?,?,?,?,?,?,?,?,?,?,?)
            ");
            $monto = 0;
            $totalEfectivoDia = 0;
            $totalTarjetaDia = 0;
            $totalCajaDia = 0;
            $utilidadDia = 0;
            $actividad = 'Cerrar caja';
            $estado = 'Cerrado';
            $query_insert->bind_param("sdddddddddis",$actividad,$monto,$totalEfectivoDia,$totalTarjetaDia,$totalCajaDia,$utilidadDia,$totalEfectivo,$totalTarjeta,$totalCaja,$utilidadTotal,$user,$estado);
            if($query_insert->execute()){
                echo json_encode(['resultado' => true, 'mensaje' => 'La caja se cerró correctamente']);
            }else{
                echo json_encode(['resultado' => false, 'mensaje' => 'La caja no se pudo cerrar']);
            }
        }
    }
?>