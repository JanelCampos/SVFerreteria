<?php

    session_start();
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idArticulo = isset($input['idArticulo']) ? intval($input['idArticulo']) : 0;
    $codigoBarras = isset($input['codigoBarras']) ? trim($input['codigoBarras']) : '';
    $nombreProducto = isset($input['nombre']) ? trim($input['nombre']) : '';
    $precioCompra = isset($input['precioCompra']) ? $input['precioCompra'] : '';
    $precioVenta = isset($input['precioVenta']) ? $input['precioVenta'] : '';
    $precioMinimo = isset($input['precioMinimo']) ? $input['precioMinimo'] : null;
    $stockAlerta = isset($input['stockAlerta']) ? intval($input['stockAlerta']) : null;
    $nuevaUnidad = isset($input['nuevaUnidad']) ? trim($input['nuevaUnidad']) : null;
    $unidadOtro = isset($input['unidadOtro']) ? trim($input['unidadOtro']) : null;
    $nuevoProveedor = isset($input['nuevoProveedor']) ? intval($input['nuevoProveedor']) : 0;
    $nuevaCategoria = isset($input['nuevaCategoria']) ? intval($input['nuevaCategoria']) : 0;
    $unidades = isset($input['unidades']) ? $input['unidades'] : [];
    $descuentos = isset($input['descuentos']) ? $input['descuentos'] : [];

    if($idArticulo <= 0 || empty($nombreProducto) || $precioCompra === '' || $precioCompra < 0 || empty($precioVenta) || $precioVenta <= 0){
        echo json_encode(['resultado' => false, 'mensaje' => 'Debe ingresar valores válidos']);
        exit;
    }

    if($precioMinimo === null || $precioMinimo === ''){
        $precioMinimo = 0;
    }

    function editarArticulo($conexionDB,$idArticulo, $codigoBarras, $nombreProducto, $precioCompra, $precioVenta, $precioMinimo, $stockAlerta, $nuevaUnidad, $unidadOtro, $nuevoProveedor, $nuevaCategoria, $unidades, $descuentos){

        $query = $conexionDB->prepare("
            SELECT Cod_Categoria, Cod_Proveedor, Cantidad, Unidad_Presentacion
            FROM articulos
            WHERE IdArticulo = ?
        ");
        $query->bind_param("i",$idArticulo);
        $query->execute();
        $result = $query->get_result();
        $dataArticulo = $result->fetch_assoc();
        $query->close();

        $idCategoria = $dataArticulo['Cod_Categoria'];
        $idProveedor = $dataArticulo['Cod_Proveedor'];
        $cantidad = $dataArticulo['Cantidad'];
        $unidadPresentacion = $dataArticulo['Unidad_Presentacion'];

        if($nuevoProveedor > 0){
            $idProveedor = $nuevoProveedor;
        }
        if($nuevaCategoria > 0){
            $idCategoria = $nuevaCategoria;
        }

        if($stockAlerta === null){
            $stockAlerta = 5;
        }

        if($nuevaUnidad === null || $nuevaUnidad === ''){
            $nuevaUnidad = $unidadPresentacion;
        }else if($nuevaUnidad === 'otro'){
            $nuevaUnidad = $unidadOtro;
        }

        $query_update_articulo = $conexionDB->prepare("
            UPDATE articulos
            SET codigoBarras = ?, Nombre = ?, Cantidad = ?, Stock_Alerta = ?, Precio_Compra = ?, Precio_Unitario = ?, Precio_Minimo = ?, Unidad_Presentacion = ?, Cod_Proveedor = ?, Cod_Categoria = ?
            WHERE IdArticulo = ?
        ");
        $query_update_articulo->bind_param("ssiidddsiii",$codigoBarras,$nombreProducto,$cantidad,$stockAlerta,$precioCompra,$precioVenta,$precioMinimo,$nuevaUnidad,$idProveedor,$idCategoria,$idArticulo);

        if(!$query_update_articulo->execute()){
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo actualizar el artículo: ' . $conexionDB->error]);
            return;
        }
        $query_update_articulo->close();

         $delete_unidades = $conexionDB->prepare("
            DELETE FROM articulo_unidades 
            WHERE Cod_Articulo = ?");
        $delete_unidades->bind_param("i", $idArticulo);
        $delete_unidades->execute();
        $delete_unidades->close();

        $factorEquivalencia = 1;
        $esPredeterminada = 1;

        $query_insert_unidadPresentacion = $conexionDB->prepare("
            INSERT INTO articulo_unidades (Cod_Articulo, Unidad , FactorEquivalencia, PrecioVenta, PrecioMinimo, EsPredeterminada) 
            VALUES (?,?,?,?,?,?)");
        $query_insert_unidadPresentacion->bind_param("isdddi", $idArticulo, $nuevaUnidad, $factorEquivalencia, $precioVenta, $precioMinimo, $esPredeterminada);
        $query_insert_unidadPresentacion->execute();
        $query_insert_unidadPresentacion->close();

        if (is_array($unidades) && count($unidades) > 0) {
            $insert_unidad = $conexionDB->prepare("
                INSERT INTO articulo_unidades (Cod_Articulo, Unidad , FactorEquivalencia, PrecioVenta, PrecioMinimo, EsPredeterminada) 
                VALUES (?,?,?,?,?,?)");
            foreach ($unidades as $u) {
                $u_unidad = trim($u['Unidad']);
                $u_factor = floatval($u['FactorEquivalencia']);
                $u_precio = floatval($u['PrecioVenta']);
                $u_minimo = floatval($u['PrecioMinimo']);
                $u_pred = isset($u['EsPredeterminada']) && $u['EsPredeterminada'] ? 1 : 0;
                if ($u_unidad !== '' && $u_factor > 0) {
                    $insert_unidad->bind_param("isdddi", $idArticulo, $u_unidad, $u_factor, $u_precio, $u_minimo, $u_pred);
                    $insert_unidad->execute();
                }
            }
            $insert_unidad->close();
        }

        $delete_desc = $conexionDB->prepare("DELETE FROM articulo_descuentos_cantidad WHERE Cod_Articulo = ?");
        $delete_desc->bind_param("i", $idArticulo);
        $delete_desc->execute();
        $delete_desc->close();

        if (is_array($descuentos) && count($descuentos) > 0) {
            $insert_desc = $conexionDB->prepare("INSERT INTO articulo_descuentos_cantidad (Cod_Articulo, CantidadMinima, PorcentajeDescuento) VALUES (?,?,?)");
            foreach ($descuentos as $d) {
                $d_cant = intval($d['CantidadMinima']);
                $d_porc = floatval($d['PorcentajeDescuento']);
                if ($d_cant > 0 && $d_porc > 0 && $d_porc <= 100) {
                    $insert_desc->bind_param("iid", $idArticulo, $d_cant, $d_porc);
                    $insert_desc->execute();
                }
            }
            $insert_desc->close();
        }

        echo json_encode(['resultado' => true, 'mensaje' => 'Se actualizó el artículo']);
    }

    if(empty($codigoBarras)){
       editarArticulo($conexionDB, $idArticulo, $codigoBarras, $nombreProducto, $precioCompra, $precioVenta, $precioMinimo, $stockAlerta, $nuevaUnidad,$unidadOtro,$nuevoProveedor, $nuevaCategoria, $unidades, $descuentos);
    }else{
        $query_codigo = $conexionDB->prepare("SELECT * FROM articulos WHERE codigoBarras = ? AND IdArticulo <> ?");
        $query_codigo->bind_param("si",$codigoBarras,$idArticulo);
        $query_codigo->execute();
        $result_codigo = $query_codigo->get_result();
        if($result_codigo->num_rows > 0){
            echo json_encode(['resultado' => false, 'mensaje' => 'El código de barras ya existe']);
        }else {
            editarArticulo($conexionDB, $idArticulo, $codigoBarras, $nombreProducto, $precioCompra, $precioVenta, $precioMinimo, $stockAlerta, $nuevaUnidad,$unidadOtro,$nuevoProveedor, $nuevaCategoria, $unidades, $descuentos);
        }
        $query_codigo->close();
    }
    $conexionDB->close();
?>
