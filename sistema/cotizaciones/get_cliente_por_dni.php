<?php
session_start();
include "../../conexion.php";
header('Content-Type: application/json; charset=utf8');

$dni = isset($_GET['dni']) ? trim($_GET['dni']) : '';

if (empty($dni) || strlen($dni) != 8 || !ctype_digit($dni)) {
    echo json_encode(['resultado' => false, 'mensaje' => 'DNI inválido, debe ser 8 dígitos']);
    exit;
}

$dni = intval($dni);

$query = $conexionDB->prepare("
    SELECT IdCliente, Dni, Nombre, Direccion, Telefono, Email
    FROM clientes
    WHERE Dni = ?
    LIMIT 1
");

/*
-- Fallback por si la tabla se llama "cliente" (singular) con otros nombres de campo:
$query = $conexionDB->prepare("
    SELECT IdCliente AS IdCliente, dniCliente AS Dni, nombreCliente AS Nombre, direccionCliente AS Direccion, telefonoCliente AS Telefono, emailCliente AS Email
    FROM cliente
    WHERE dniCliente = ?
    LIMIT 1
");
*/

if ($query) {
    $query->bind_param("i", $dni);
    if ($query->execute()) {
        $result = $query->get_result();
        if ($result->num_rows > 0) {
            $datos = $result->fetch_assoc();
            echo json_encode(['resultado' => true, 'datos' => $datos]);
        } else {
            echo json_encode(['resultado' => false, 'mensaje' => 'No encontrado']);
        }
    } else {
        echo json_encode(['resultado' => false, 'mensaje' => 'Error en la ejecución de la consulta']);
    }
    $query->close();
} else {
    echo json_encode(['resultado' => false, 'mensaje' => 'Error en la preparación de la consulta']);
}

$conexionDB->close();
?>
