<?php
include "../../conexion.php";
require_once __DIR__ . "/../includes/analytics.php";

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$filters = analyticsGetDateFilters(is_array($input) ? $input : []);
$busqueda = isset($input['busqueda']) ? trim((string)$input['busqueda']) : '';
$filtrosVarios = isset($input['filtrosVarios']) ? trim((string)$input['filtrosVarios']) : '';
$current_page = isset($input['page']) ? (int)$input['page'] : 1;
$results_per_page = isset($input['per_page']) ? (int)$input['per_page'] : 10;

$clientData = analyticsGetClientsListData(
    $conexionDB,
    $filters,
    $busqueda,
    $filtrosVarios,
    $current_page,
    $results_per_page
);

if ($clientData['rows']) {
    echo json_encode([
        'resultado' => true,
        'datos' => $clientData['rows'],
        'total_records' => $clientData['total_records'],
        'mensaje' => 'Busqueda exitosa.',
    ]);
} else {
    echo json_encode([
        'resultado' => false,
        'mensaje' => 'No se encontraron resultados.',
        'total_records' => 0,
    ]);
}

$conexionDB->close();
