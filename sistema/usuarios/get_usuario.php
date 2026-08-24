<?php 
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $id = isset($input['id']) ? $input['id'] : null;

    if($id != null){
        $query = $conexionDB->prepare("
            SELECT e.IdEmpleado, e.Nombre, e.Dni, e.Direccion, e.Telefono, e.Email, e.Usuario, r.rol
            FROM empleados e
            INNER JOIN rol r ON r.IdRol = e.Rol
            WHERE IdEmpleado = ?
        ");
        $query->bind_param("i", $id);
        if($query->execute()){
            $result = $query->get_result();
            $dataEmpleado = $result->fetch_assoc();
            echo json_encode(['resultado' => true, 'empleado' => $dataEmpleado]);
        } else {
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo traer los datos del usuario']);
        }
    } else {
        echo json_encode(['resultado' => false, 'mensaje' => 'El id es nulo']);
    }
?>
