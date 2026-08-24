<?php 
    session_start();
    include "../../conexion.php";
    include "funciones.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idGasto= isset($input['idGasto']) ? $input['idGasto'] : null;

    $queryGasto = $conexionDB->prepare("
        SELECT montoGasto, medioPago, tipoGasto
        FROM gastos
        WHERE idGastos = ?
    ");
    $queryGasto->bind_param("i", $idGasto);
    if($queryGasto->execute()){
        $resultGasto = $queryGasto->get_result();
        $dataGasto = $resultGasto->fetch_assoc();
        $montoGasto = $dataGasto['montoGasto'];
        $medioPago = $dataGasto['medioPago'];
        $tipoGasto = $dataGasto['tipoGasto'];

        $resultado = quitarGastoDeCaja($conexionDB,$idGasto,$montoGasto,$medioPago,$tipoGasto);
        if($resultado){
            echo json_encode(['resultado' => true, 'mensaje' => 'El gasto ha sido anulado']);
        }else{
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo quitar el gasto de la caja']);
        }
    }
?>