<?php
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $id = isset($input['id']) ? intval($input['id']) : 0;

    $query_categoria = $conexionDB->prepare("SELECT IdCategoria, Nombre FROM categorias WHERE Estado = 1 ORDER BY Nombre ASC");
    $query_categoria->execute();
    $result_categoria = $query_categoria->get_result();
    $data_categoria = [];
    while($row = $result_categoria->fetch_assoc()) {
        $data_categoria[] = $row;
    }
    $query_categoria->close();

    $query_proveedor = $conexionDB->prepare("SELECT IdProveedor, Nombre FROM proveedores ORDER BY Nombre ASC");
    $query_proveedor->execute();
    $result_proveedor = $query_proveedor->get_result();
    $data_proveedor = [];
    while($row = $result_proveedor->fetch_assoc()) {
        $data_proveedor[] = $row;
    }
    $query_proveedor->close();

    if($id > 0){
        $query = $conexionDB->prepare("
            SELECT a.IdArticulo, a.codigoBarras, a.Nombre, a.Cantidad, a.Stock_Alerta, a.Precio_Compra,
                   a.Precio_Unitario, a.Precio_Minimo, a.Unidad_Presentacion, a.Cod_Categoria, a.Cod_Proveedor,
                   c.Nombre as nombreCategoria, p.Nombre as nombreProveedor
            FROM articulos a
            LEFT JOIN categorias c ON c.IdCategoria = a.Cod_Categoria
            INNER JOIN proveedores p ON p.IdProveedor = a.Cod_Proveedor
            WHERE IdArticulo = ?
        ");
        $query->bind_param("i", $id);
        if($query->execute()){
            $result = $query->get_result();
            $data = $result->fetch_assoc();
            $unidadPresentacion = $data['Unidad_Presentacion'];
            $data['Unidad_Presentacion'] = $unidadPresentacion;
            $query_unidades = $conexionDB->prepare("
                SELECT IdUnidad, Unidad, FactorEquivalencia, PrecioVenta, PrecioMinimo, EsPredeterminada 
                FROM articulo_unidades 
                WHERE Cod_Articulo = ? AND Unidad != ?
                ORDER BY EsPredeterminada DESC, IdUnidad ASC");
            $query_unidades->bind_param("is", $id, $unidadPresentacion);
            $query_unidades->execute();
            $unidades = [];
            $res_unidades = $query_unidades->get_result();
            while ($row = $res_unidades->fetch_assoc()) {
                $unidades[] = $row;
            }
            $query_unidades->close();

            $query_descuentos = $conexionDB->prepare("
                SELECT IdDescuento, CantidadMinima, PorcentajeDescuento 
                FROM articulo_descuentos_cantidad 
                WHERE Cod_Articulo = ? 
                ORDER BY CantidadMinima ASC");
            $query_descuentos->bind_param("i", $id);
            $query_descuentos->execute();
            $descuentos = [];
            $res_desc = $query_descuentos->get_result();
            while ($row = $res_desc->fetch_assoc()) {
                $descuentos[] = $row;
            }
            $query_descuentos->close();

            echo json_encode([
                'resultado' => true,
                'datos' => $data,
                'categorias' => $data_categoria,
                'proveedores' => $data_proveedor,
                'unidades' => $unidades,
                'descuentos' => $descuentos
            ]);
        } else {
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo traer los datos del artículo']);
        }
        $query->close();
    } else {
        echo json_encode([
            'resultado' => true,
            'categorias' => $data_categoria,
            'proveedores' => $data_proveedor,
            'mensaje' => 'Listas cargadas'
        ]);
    }
    $conexionDB->close();
?>
