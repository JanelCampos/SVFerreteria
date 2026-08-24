<?php
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);
    $busqueda = isset($input['busqueda']) ? trim($input['busqueda']) : '';
    $stock = isset($input['stock']) ? trim($input['stock']) : '';
    $idProveedor = isset($input['IdProveedor']) ? $input['IdProveedor'] : '';
    $idCategoria = isset($input['IdCategoria']) ? $input['IdCategoria'] : '';
    $current_page = isset($input['page']) ? intval($input['page']) : 1;

    $results_per_page = 10;
    $start_from = ($current_page - 1) * $results_per_page;

    $tipos = "";
    $params = [];
    $where = " WHERE 1=1";

    if (!empty($busqueda)) {
        $where .= " AND (a.codigoBarras LIKE ? OR a.Nombre LIKE ?)";
        $tipos .= "ss";
        $params[] = "%" . $busqueda . "%";
        $params[] = "%" . $busqueda . "%";
    }

    if (!empty($idProveedor)) {
        $where .= " AND a.Cod_Proveedor = ?";
        $tipos .= "i";
        $params[] = $idProveedor;
    }

    if (!empty($idCategoria)) {
        $where .= " AND a.Cod_Categoria = ?";
        $tipos .= "i";
        $params[] = $idCategoria;
    }

    if (!empty($stock)) {
        if($stock == 'sinStock'){
            $where .= " AND a.Cantidad = 0";
        }
        if($stock == 'pocoStock'){
            $where .= " AND a.Cantidad > 0 AND a.Cantidad <= a.Stock_Alerta";
        }
        if($stock == 'conStock'){
            $where .= " AND a.Cantidad > a.Stock_Alerta";
        }
    }

    $sql = "
        SELECT a.IdArticulo, a.Nombre as nombreA, a.Cantidad, a.Stock_Alerta, a.Precio_Compra, a.Precio_Unitario,
               a.Precio_Minimo, a.Unidad_Presentacion, p.Nombre as nombreP, c.Nombre as nombreC
        FROM articulos a
        INNER JOIN proveedores p ON p.IdProveedor = a.Cod_Proveedor
        LEFT JOIN categorias c ON c.IdCategoria = a.Cod_Categoria
        $where
        ORDER BY a.IdArticulo DESC LIMIT ?, ?
    ";
    $tipos .= "ii";
    $params[] = $start_from;
    $params[] = $results_per_page;

    $query = $conexionDB->prepare($sql);
    if (!empty($params)) {
        $query->bind_param($tipos, ...$params);
    }

    $response = [];
    if ($query->execute()) {
        $result = $query->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $response[] = $row;
            }
            $count_sql = "
                SELECT COUNT(*) as total
                FROM articulos a
                INNER JOIN proveedores p ON p.IdProveedor = a.Cod_Proveedor
                LEFT JOIN categorias c ON c.IdCategoria = a.Cod_Categoria
                $where
            ";
            $count_params = array_slice($params, 0, count($params) - 2);
            $count_stmt = $conexionDB->prepare($count_sql);
            if (!empty($count_params)) {
                $count_tipos = substr($tipos, 0, strlen($tipos) - 2);
                $count_stmt->bind_param($count_tipos, ...$count_params);
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
        echo json_encode(['resultado' => false, 'mensaje' => 'Error en la consulta.']);
    }

    $conexionDB->close();
?>
