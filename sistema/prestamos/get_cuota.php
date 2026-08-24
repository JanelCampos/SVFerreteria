<?php 
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $id = isset($input['id']) ? $input['id'] : null;

    if($id != null){
        $query = $conexionDB->prepare("
            SELECT *
            FROM cuotas
            WHERE idCuota = ?
        ");
        $query->bind_param("i", $id);
        if($query->execute()){
            $result = $query->get_result();
            $data = $result->fetch_assoc();
            echo json_encode(['resultado' => true, 'datos' => $data]);
        } else {
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo traer los datos de la cuota']);
        }
    } else {
        echo json_encode(['resultado' => false, 'mensaje' => 'El id es nulo']);
    }
?>
