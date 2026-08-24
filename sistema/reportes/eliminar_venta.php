<?php 
    session_start();
    include "../../conexion.php";
    include "funciones.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idVenta = isset($input['idVenta']) ? $input['idVenta'] : null;
    $stadoVenta = isset($input['estadoVenta']) ? $input['estadoVenta'] : null;

    $resultado = eliminarDetalleFactura($conexionDB,$idVenta);
    if($resultado){

        $resultado = eliminarDetalleVentaArticulo($conexionDB,$idVenta);
        if($resultado){
            $resultado = eliminarVenta($conexionDB,$idVenta);
            if($resultado){
                echo json_encode(['resultado' => true, 'mensaje' => 'Venta elimina correctamente']);
            }else{
                echo json_encode(['resultado' => false, 'mensaje' => 'error al eliminar la venta']);
            }
        }else{
            echo json_encode(['resultado' => false, 'mensaje' => 'error al eliminar el detalle venta']);
        }
    }else{
        echo json_encode(['resultado' => false, 'mensaje' => 'error al eliminar el detalle factura']);
    }
?>