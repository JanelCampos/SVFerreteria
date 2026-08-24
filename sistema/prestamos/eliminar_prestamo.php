<?php 
    session_start();
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idPrestamo = isset($input['idPrestamo']) ? $input['idPrestamo'] : null;

    $query_delete = $conexionDB->prepare("
        DELETE 
        FROM cuotas
        WHERE idPrestamo = ?
    ");
    $query_delete->bind_param("i",$idPrestamo);

    if($query_delete->execute()){
        $query_delete = $conexionDB->prepare("
            DELETE 
            FROM prestamos
            WHERE idPrestamo = ?
        ");

        if($query_delete){
            $query_delete->bind_param("i",$idPrestamo);
            if($query_delete->execute()){
                echo json_encode(['resultado' => true, 'mensaje' => 'Préstamo eliminado correctamente']);
            }else{
                echo json_encode(['resultado' => false, 'mensaje' => 'no se pudo eliminar el préstamo']);

            }
        }
    }else{
        echo json_encode(['resultado' => false, 'mensaje' => 'no se pudo eliminar las cuotas']);
    }
?>