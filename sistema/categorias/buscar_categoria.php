<?php
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);
    $busqueda = isset($input['busqueda']) ? trim($input['busqueda']) : '';
    $current_page = isset($input['page']) ? intval($input['page']) : 1;

    $results_per_page = 10;
    $start_from = ($current_page - 1) * $results_per_page;

    $tipos = "";
    $params = [];
    $where = " WHERE Estado = 1";
    if (!empty($busqueda)) {
        $where .= " AND (Nombre LIKE ? OR Descripcion LIKE ?)";
        $tipos .= "ss";
        $params[] = "%" . $busqueda . "%";
        $params[] = "%" . $busqueda . "%";
    }

    $consulta = "
        SELECT IdCategoria, Nombre, Descripcion, Estado, FechaCreacion
        FROM categorias
        $where
        ORDER BY IdCategoria DESC LIMIT ?, ?
    ";
    $tipos .= "ii";
    $params[] = $start_from;
    $params[] = $results_per_page;

    $query = $conexionDB->prepare($consulta);
    if (!empty($params)) {
        $query->bind_param($tipos, ...$params);
    }
    $response = [];
    if ($query->execute()) {
        $result = $query->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $idCat = $row['IdCategoria'];
                $query_count = $conexionDB->prepare("SELECT COUNT(*) as cant FROM articulos WHERE Cod_Categoria = ?");
                $query_count->bind_param("i", $idCat);
                $query_count->execute();
                $count = $query_count->get_result()->fetch_assoc()['cant'];
                $query_count->close();
                $row['CantArticulos'] = $count;
                $response[] = $row;
            }

            $count_where = " FROM categorias $where";
            $count_query = "SELECT COUNT(*) as total" . $count_where;
            $count_stmt = $conexionDB->prepare($count_query);
            if (!empty($busqueda)) {
                $count_params = ["%" . $busqueda . "%", "%" . $busqueda . "%"];
                $count_stmt->bind_param("ss", ...$count_params);
            }
            $count_stmt->execute();
            $total_records = $count_stmt->get_result()->fetch_assoc()['total'];
            $count_stmt->close();

            echo json_encode(['resultado' => true, 'datos' => $response, 'total_records' => $total_records, 'mensaje' => 'Busqueda exitosa.']);
        } else {
            echo json_encode(['resultado' => false, 'mensaje' => 'No se encontraron resultados.']);
        }
        $query->close();
    } else {
        echo json_encode(['resultado' => false, 'mensaje' => 'Error en la ejecución de la consulta.']);
    }

    $conexionDB->close();
?>
