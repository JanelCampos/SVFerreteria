<?php
include "../../conexion.php";
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$id = isset($input['idUsuario']) ? $input['idUsuario'] : null;
$claveIngresada = isset($input['claveActual']) ? $input['claveActual'] : null;

if ($claveIngresada != '' && $claveIngresada != null) {
    $query = $conexionDB->prepare("
        SELECT Clave
        FROM empleados
        WHERE IdEmpleado = ?
    ");
    $query->bind_param("i", $id);
    if ($query->execute()) {
        $result = $query->get_result();
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $claveActual = $data['Clave'];
            if (password_verify($claveIngresada, $claveActual)) {
                echo json_encode(['resultado' => true]);
            } else {
                echo json_encode(['resultado' => false, 'mensaje' => 'Ingrese su clave correcta']);
            }
        } else {
            echo json_encode(['resultado' => false, 'mensaje' => 'Usuario no encontrado']);
        }
    } else {
        echo json_encode(['resultado' => false, 'mensaje' => 'Error en la consulta']);
    }
    $query->close();
} else {
    echo json_encode(['resultado' => false, 'mensaje' => 'Tiene que ingresar su clave actual']);
}

$conexionDB->close();
?>
