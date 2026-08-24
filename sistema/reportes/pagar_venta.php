<?php 
    session_start();
    include "../../conexion.php";
    include "funciones.php";

    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idVenta = isset($input['idVenta']) ? $input['idVenta'] : null;
    $totalVenta = isset($input['totalVenta']) ? $input['totalVenta'] : null;
    $pago = isset($input['pago']) ? $input['pago'] : null;
    $metodoPago = isset($input['metodoPago']) ? $input['metodoPago'] : null;
    $efectivo = isset($input['efectivo']) ? $input['efectivo'] : null;
    $tarjeta = isset($input['tarjeta']) ? $input['tarjeta'] : null;
    $vuelto = isset($input['vuelto']) ? $input['vuelto'] : null;
    $metodoPagoVuelto = isset($input['metodoPagoVuelto']) ? $input['metodoPagoVuelto'] : null;
    $saldo = isset($input['saldo']) ? $input['saldo'] : null;
    $utilidad = isset($input['utilidad']) ? $input['utilidad'] : null;
    $query = $conexionDB->prepare("
        SELECT dniCliente, Total
        FROM ventas
        WHERE IdVenta = ?
    ");
    $query->bind_param("i",$idVenta);
    if($query->execute()){
        $result = $query->get_result();
        $data = $result->fetch_assoc();
        $dniCliente = $data['dniCliente'];
        $total = $data['Total'];
    }
    $estadoVenta = 'pagado';
    if($saldo != 0){
        $estadoVenta = 'saldo';
    }

    $resultado = registrarVentaPendienteEnCaja($conexionDB,$metodoPago,$totalVenta,$efectivo,$tarjeta,$vuelto,$metodoPagoVuelto,$saldo,$utilidad,$estadoVenta);
    if($resultado){
        $resultado = registrarVentaPendienteEnCliente($conexionDB,$dniCliente,$estadoVenta,$efectivo,$tarjeta,$utilidad,$total);
        if($resultado){
            $resultado = actualizarEstadoDeVenta($conexionDB,$idVenta,$estadoVenta,$efectivo,$tarjeta,$saldo);
            if($resultado){
                echo json_encode(['resultado' => true, 'mensaje' => 'Se realizó el pago de la venta']);
            }else{
                echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo actualizar la venta']);
            }
        }else{
            echo json_encode(['resultado' => false, 'mensaje' => 'No se registro la venta en el cliente']);
        }
    }else{
        echo json_encode(['resultado' => false, 'mensaje' => 'No se registro la venta en la caja']);
    }
    
?>