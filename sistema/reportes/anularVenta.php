<?php 
    session_start();
    include "../../conexion.php";
    include "funciones.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idVenta = isset($input['idVenta']) ? $input['idVenta'] : null;
    $metodoPago = isset($input['metodoPago']) ? $input['metodoPago'] : null;

    $queryVenta = $conexionDB->prepare("
        SELECT Estado, Total, utilidad, dniCliente, saldo
        FROM ventas
        WHERE IdVenta = ?
    ");
    $queryVenta->bind_param("i", $idVenta);
    if($queryVenta->execute()){
        $resultVenta = $queryVenta->get_result();
        $dataVenta = $resultVenta->fetch_assoc();
        $estadoVenta = $dataVenta['Estado'];
        $totalVenta = $dataVenta['Total'];
        $utilidadVenta = $dataVenta['utilidad'];
        $dniCliente = $dataVenta['dniCliente'];
        $saldo = $dataVenta['saldo'];
        $actividad = 'Anular venta';
        $estadoCaja = 'Abierto';
        $nuevoEstado = 'anulado';

        $resultado = quitarVentaDeCaja($conexionDB,$estadoVenta,$totalVenta,$actividad,$estadoCaja,$metodoPago,$utilidadVenta,$saldo);
        if($resultado){
            $resultado = quitarVentaAlCliente($conexionDB,$dniCliente,$estadoVenta,$totalVenta,$utilidadVenta,$saldo);
            if($resultado){

                $resultado = actualizarStock($conexionDB,$idVenta);
                if($resultado){

                    $resultado = cambiarEstadoDeVenta($conexionDB,$idVenta,$nuevoEstado);
                    if($resultado){
                        echo json_encode(['resultado' => true, 'mensaje' => 'La venta ha sido anulada']);
                    }else{
                        echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo actualizar el estado']);
                    }
                }else{
                    echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo actualizar el stock']);
                }
            }else{
                echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo quiar la venta al cliente']);
            }
        }else{
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo quitar la venta de la caja']);
        }
    }
?>