<?php
 
    include "../conexion.php";
    session_start();
    include "funciones.php";

    if(!empty($_POST)){

        $alert = '';

        //Buscar articulo por nombre
        if($_POST['action'] == 'buscarArticulo'){
            if(!empty($_POST['buscar'])){

                $query = mysqli_query($conexionDB,"SELECT * FROM articulos WHERE Nombre LIKE LOWER('%".$_POST["buscar"]."%')");
    
                mysqli_close($conexionDB);
                $result = mysqli_num_rows($query);
    
                $data = array();
                if($result > 0){
                    while($row = mysqli_fetch_assoc($query)){
                        $data[] = $row;
                    }
                } else {
                    $data = 0;
                }
                echo json_encode($data,JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        //Extraer Artículo - Venta
        if($_POST['action'] == 'infoProducto'){

            $articulo = $_POST['articulo'];

            $query = mysqli_query($conexionDB,"SELECT IdArticulo,Nombre,Cantidad,Precio_Compra,Precio_Unitario,Cod_Proveedor FROM articulos WHERE IdArticulo LIKE '$articulo'");

            mysqli_close($conexionDB);

            $result = mysqli_num_rows($query);
            if($result > 0){
                $data = mysqli_fetch_assoc($query);
                echo json_encode($data,JSON_UNESCAPED_UNICODE);
                exit;
            }
            echo 'error';
            exit;
        }

        //Buscar Socio -cliente-
        if($_POST['action'] == 'searchCliente'){
            if(!empty($_POST['cliente'])){

                $dni = $_POST['cliente'];
                $query = mysqli_query($conexionDB,"SELECT * FROM clientes WHERE Dni LIKE '$dni'");
    
                mysqli_close($conexionDB);
                $result = mysqli_num_rows($query);
    
                $data = '';
                if($result > 0){
                    $data = mysqli_fetch_assoc($query);
                } else {
                    $data = 0;
                }
                echo json_encode($data,JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        //Registra Cliente - ventas
        if($_POST['action'] == 'addCliente'){
            $dni = $_POST['dni_cliente'];
            $nombre = $_POST['nom_cliente'];
            $telefono = $_POST['tel_cliente'];
            $direccion = $_POST['dir_cliente'];
            $correo = $_POST['cor_cliente'];
            $fecha_registro = $_POST['fec_cliente'];

            $query = mysqli_query($conexionDB,"SELECT * FROM clientes WHERE Dni = '$dni'");
            $result = mysqli_fetch_array($query);
            
            if($result > 0){
                $alert = '<p class="msg_error">El DNI ya existe.</p>';
            } else {
                $query_insert = mysqli_query($conexionDB,"INSERT INTO clientes (Dni, Nombre, Telefono, Direccion, Email, Fecha_Registro)
                                                        VALUES ('$dni','$nombre','$telefono','$direccion','$correo', '$fecha_registro')");
                
                if($query_insert){
                    $codCliente = mysqli_insert_id($conexionDB);
                    $msg = $codCliente;
                } else {
                    $msg = 'error';
                }                                    
            }

            mysqli_close($conexionDB);
            echo $msg;
            exit;
        }

        //Agregar producto al detalle temporal
        if($_POST['action'] == 'addProductDetalle'){
            if(empty($_POST['producto']) || empty($_POST['cantidad'] || empty($_POST['precioUnitario']))){
                echo 'error';
            } else {
                $codArticulo = $_POST['producto'];
                $cantidad = $_POST['cantidad'];
                $precioUnitario = $_POST['precioUnitario'];

                $query_detalle_temp = mysqli_query($conexionDB,"CALL add_detalle_temp($codArticulo,$cantidad,$precioUnitario)");
                $result = mysqli_num_rows($query_detalle_temp);

                $detalleTabla = '';
                $sub_total = 0;
                $total = 0;
                $arrayData = array();

                if($result > 0){
                    while ($data = mysqli_fetch_assoc($query_detalle_temp)){
                        $precioTotal = round($data['cantidad'] * $data['precio_venta'], 2);
                        $sub_total = round($sub_total + $precioTotal, 2);
                        $total = round($total + $precioTotal, 2);

                        $detalleTabla .= '<tr>
                                            <th>'.$data['codArticulo'].'</th>
                                            <td colspan="2">'.$data['Nombre'].'</td>
                                            <td class="textcenter">'.$data['cantidad'].'</td>
                                            <td class="textright">'.$data['precio_venta'].'</td>
                                            <td class="textright">'.$precioTotal.'</td>
                                            <td class="">
                                                <a class="link_delete" href="#" onclick="event.preventDefault(); del_product_detalle('.$data['correlativo'].');"><i class="far fa-trash-alt"></i></a>
                                            </td>
                                        </tr>';
                    }
                    
                    $detalleTotales = ' <tr>
                                            <td colspan="5" class="textright">SUBTOTAL</td>
                                            <td class="textright">'.$sub_total.'</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="textright">TOTAL</td>
                                            <td class="textright">'.$total.'</td>
                                        </tr>';

                    $arrayData['detalle'] = $detalleTabla;
                    $arrayData['totales'] = $detalleTotales;
                    $arrayData['total'] = $total;

                    echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);
                } else {
                    echo 'error';
                }
                mysqli_close($conexionDB);
            }
            exit;
        }

        //Extraer datos del detalle_temp
        if($_POST['action'] == 'searchForDetalle'){
            if(empty($_POST['user'])){
                echo 'error';
            } else {

                $query = mysqli_query($conexionDB,"SELECT tmp.correlativo,
                                                          tmp.cantidad,
                                                          tmp.precio_venta,
                                                          tmp.codArticulo,
                                                          a.Nombre
                                                    FROM detalle_temp tmp
                                                    INNER JOIN articulos a
                                                    ON tmp.codArticulo = a.IdArticulo");

                $result = mysqli_num_rows($query);

                $detalleTabla = '';
                $sub_total = 0;
                $total = 0;
                $arrayData = array();

                if($result > 0){
                    while ($data = mysqli_fetch_assoc($query)){
                        $precioTotal = round($data['cantidad'] * $data['precio_venta'], 2);
                        $sub_total = round($sub_total + $precioTotal, 2);
                        $total = round($total + $precioTotal, 2);

                        $detalleTabla .= '<tr>
                                            <th>'.$data['codArticulo'].'</th>
                                            <td colspan="2">'.$data['Nombre'].'</td>
                                            <td class="textcenter">'.$data['cantidad'].'</td>
                                            <td class="textright">'.$data['precio_venta'].'</td>
                                            <td class="textright">'.$precioTotal.'</td>
                                            <td class="">
                                                <a class="link_delete" href="#" onclick="event.preventDefault(); del_product_detalle('.$data['correlativo'].');"><i class="far fa-trash-alt"></i></a>
                                            </td>
                                        </tr>';
                    }
                    
                    $detalleTotales = ' <tr>
                                            <td colspan="5" class="textright">SUBTOTAL</td>
                                            <td class="textright">'.$sub_total.'</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="textright">TOTAL</td>
                                            <td class="textright">'.$total.'</td>
                                        </tr>';

                    $arrayData['detalle'] = $detalleTabla;
                    $arrayData['totales'] = $detalleTotales;

                    echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);
                } else {
                    echo 'error';
                }
                mysqli_close($conexionDB);
            }
            exit;
        }
        
        //Extraer datos del detalle_temp
        if($_POST['action'] == 'delProductDetalle'){
            if(empty($_POST['id_detalle'])){
                echo 'error';
            } else {

                $id_detalle = $_POST['id_detalle'];

                $query_detalle_temp = mysqli_query($conexionDB,"CALL del_detalle_temp($id_detalle)");
                $result = mysqli_num_rows($query_detalle_temp);

                $detalleTabla = '';
                $sub_total = 0;
                $total = 0;
                $arrayData = array();

                if($result > 0){
                    while ($data = mysqli_fetch_assoc($query_detalle_temp)){
                        $precioTotal = round($data['cantidad'] * $data['precio_venta'], 2);
                        $sub_total = round($sub_total + $precioTotal, 2);
                        $total = round($total + $precioTotal, 2);

                        $detalleTabla .= '<tr>
                                            <th>'.$data['codArticulo'].'</th>
                                            <td colspan="2">'.$data['Nombre'].'</td>
                                            <td class="textcenter">'.$data['cantidad'].'</td>
                                            <td class="textright">'.$data['precio_venta'].'</td>
                                            <td class="textright">'.$precioTotal.'</td>
                                            <td class="">
                                                <a class="link_delete" href="#" onclick="event.preventDefault(); del_product_detalle('.$data['correlativo'].');"><i class="far fa-trash-alt"></i></a>
                                            </td>
                                        </tr>';
                    }
                    
                    $detalleTotales = ' <tr>
                                            <td colspan="5" class="textright">SUBTOTAL</td>
                                            <td class="textright">'.$sub_total.'</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="textright">TOTAL</td>
                                            <td class="textright">'.$total.'</td>
                                        </tr>';

                    $arrayData['detalle'] = $detalleTabla;
                    $arrayData['totales'] = $detalleTotales;
                    $arrayData['total'] = $total;

                    echo json_encode($arrayData,JSON_UNESCAPED_UNICODE);
                } else {
                    echo 'error';
                }
                mysqli_close($conexionDB);
            }
            exit;
        }

        //Anular venta
        if($_POST['action'] == 'anularVenta'){

            $query_del = mysqli_query($conexionDB,"DELETE FROM detalle_temp");
            mysqli_close($conexionDB);

            if($query_del){
                echo 'ok';
            } else {
                echo 'error';
            }
            exit;
        }

        //Procesar Venta
        if($_POST['action'] == 'procesarVenta'){

            $estadoVenta = $_POST['estadoVenta'];
        
            if($estadoVenta == "pagada"){
                $usuario = $_SESSION['idUser'];
                $codcliente = $_POST['codcliente'];
                $pagoEfectivo = $_POST['pagoEfectivo'];
                $pagoTarjeta = $_POST['pagoTarjeta'];
                $fechaVenta = $_POST['fechaVenta'];
                $medioPago = $_POST['medioPago'];

                $query = $conexionDB->prepare("
                    SELECT *
                    FROM detalle_temp
                ");
                if($query){
                    if($query->execute()){
                        
                    }else{

                    }
                }else{

                }
            }else if($estadoVenta == "credito"){

            }else{

            }
            
            $query = mysqli_query($conexionDB,"SELECT * FROM detalle_temp");
            $result = mysqli_num_rows($query); 

            if($result > 0){
                //Inserta registro en caja
                try{
                    //Calcular monto de venta
                    $consultaMontoVenta = mysqli_query($conexionDB,"
                        SELECT SUM(cantidad*precio_venta) as montoVenta, SUM(Ganancias) as utilidad
                        FROM detalle_temp");
                    $data = mysqli_fetch_array($consultaMontoVenta);
                    $montoVenta = $data['montoVenta'];
                    $utilidad = $data['utilidad'];

                    //calcular total en efectivo
                    $consultaTotalEfectivo = mysqli_query($conexionDB,"SELECT SUM(TotalEfectivo) + '$pagoEfectivo' as totalEfectivo
                                FROM caja WHERE IdCaja = (SELECT MAX(IdCaja) FROM caja)");
                    $data = mysqli_fetch_array($consultaTotalEfectivo);
                    $totalEfectivo = $data['totalEfectivo'];

                    //calcular total en tarjeta
                    $consultaTotalTarjeta = mysqli_query($conexionDB,"SELECT SUM(TotalTarjeta) + '$pagoTarjeta' as totalTarjeta
                                FROM caja WHERE IdCaja = (SELECT MAX(IdCaja) FROM caja)");
                    $data = mysqli_fetch_array($consultaTotalTarjeta);
                    $totalTarjeta= $data['totalTarjeta'];

                    // calcular total de caja
                    $consultaTotalCaja = mysqli_query($conexionDB,"SELECT SUM(Total_caja) + $montoVenta as totalCaja
                                FROM caja WHERE IdCaja = (SELECT MAX(IdCaja) FROM caja)");
                    $data = mysqli_fetch_array($consultaTotalCaja);
                    $totalCaja = $data['totalCaja'];

                    //Calcular utilidad total
                    $consultaUtilidad = mysqli_query($conexionDB,"SELECT SUM(Utilidad) AS utilidadTotal FROM caja
                                                    WHERE IdCaja = (SELECT MAX(IdCaja) FROM caja)");
                    $data = mysqli_fetch_array($consultaUtilidad);
                    $utilidadTotal = $data['utilidadTotal'];
                    
                    $consultaUtilidadVenta = mysqli_query($conexionDB,"SELECT SUM(Ganancias) as ganancias FROM detalle_temp");
                    $data = mysqli_fetch_array($consultaUtilidadVenta);
                    $utilidadVenta = $data['ganancias'];

                    $utilidadTotal = $utilidadTotal + $utilidadVenta;

                    // //Consulta para el id del usuario
                    $idEmpleado = $_SESSION['idUser'];


                    $query_insert = mysqli_query($conexionDB,"INSERT INTO caja (Actividad,Monto_inicial,TotalEfectivo,TotalTarjeta,Total_caja,Utilidad,Cod_Empleado,Estado)
                                        VALUES('Venta de Artículo',$montoVenta,$totalEfectivo,$totalTarjeta,$totalCaja,$utilidadTotal,$idEmpleado,'Abierto')");

                    // //Calcular el id del ultimo resgitro a la caja
                    $lastInsertIdCaja = $conexionDB->insert_id;

                }catch(Exception $ex){
                    echo "se produjo un error: " + $ex->getMessage();
                }

                //Inserta registro en venta
                try{
                    $query_insert_venta = mysqli_query($conexionDB,"INSERT INTO ventas (Fecha,Cod_Caja,Id_Cliente,Medio_Pago,Total,utilidad,Estado)
                                                    VALUES ('$fechaVenta',$lastInsertIdCaja,$codcliente,'$medioPago',$montoVenta,$utilidad,'$estadoVenta')");
                    //Calcular el id del ultimo registro de la venta
                    $lastInsertIdVenta = $conexionDB->insert_id;

                }catch(Exception $ex){
                    echo "se produjo un error: " + $ex->getMessage();
                }

                // //Inserta registro en detalle de venta
                try{
                    $query_insert = mysqli_query($conexionDB,"INSERT INTO detalle_venta_articulos (Cod_Venta,Cod_Articulo,Cantidad,Precio_Compra,Precio_Venta,Ganancias,Total)
                                                        SELECT $lastInsertIdVenta,codArticulo,cantidad,Precio_Compra,precio_venta, (precio_venta * cantidad) - (Precio_Compra * cantidad) as ganancias, (cantidad * precio_venta) as total FROM detalle_temp");
                }catch(Exception $ex){
                    echo "se produjo un error: " + $ex->getMessage();
                }

                // Inserta registro en detalle factura
                try{
                    $query_insert = mysqli_query($conexionDB,"INSERT INTO detallefactura (nroFactura, CodArticulo, Cantidad, precio_venta,Medio_Pago)
                                                            SELECT $lastInsertIdVenta, codArticulo, cantidad, precio_venta, '$medioPago' FROM detalle_temp");
                }catch(Exception $ex){
                    echo "se produjo un error: " + $ex->getMessage();
                }

                //Actualizar stock de articulos
                try{
                    //Obtener id de los productos que se han vendido
                    $query = mysqli_query($conexionDB,"UPDATE articulos a
                                                    INNER JOIN detalle_temp dt ON dt.CodArticulo = a.IdArticulo
                                                    SET a.Cantidad = a.Cantidad - dt.cantidad");
                }catch(Exception $ex){
                    echo "se produjo un error: " + $ex->getMessage();
                }

                //eliminar datos de la tabla detalle temporal
                try{
                    $query = mysqli_query($conexionDB,"DELETE FROM detalle_temp;");
                }catch(Exception $ex){
                    echo "se produjo un error: " + $ex->getMessage();
                }

                //Enviar dato a traves de JSON
                $query = mysqli_query($conexionDB,"SELECT * FROM ventas WHERE IdVenta = $lastInsertIdVenta");
                $result_venta = mysqli_num_rows($query);
                if($result_venta > 0){
                    $data = mysqli_fetch_assoc($query);
                    echo json_encode($data,JSON_UNESCAPED_UNICODE);
                }else{
                    echo "error";
                }
            }

            mysqli_close($conexionDB);
            exit;
        }

        //codigo prueba
        if($_POST['action'] == 'pruebaPDF'){
            $query = mysqli_query($conexionDB,"SELECT * FROM ventas WHERE IdVenta = '107'");
            $data = mysqli_fetch_assoc($query);
            echo json_encode($data,JSON_UNESCAPED_UNICODE);

            mysqli_close($conexionDB);
        }

        //Cambiar contraseña
        if($_POST['action'] == 'changePassword'){
            if (!empty($_POST['passActual']) && !empty($_POST['passNuevo'])){
                $password = md5($_POST['passActual']);
                $newPass = md5($_POST['passNuevo']);
                $idUser = $_SESSION['idUser'];

                $code = '';
                $msg = '';
                $arrData = array();

                $query_user = mysqli_query($conexionDB,"SELECT * FROM empleados
                                                        WHERE Clave = '$password' and IdEmpleado = $idUser");
                $result = mysqli_num_rows($query_user);
                if($result > 0){
                    $query_update = mysqli_query($conexionDB,"UPDATE empleados SET Clave = '$newPass' WHERE IdEmpleado = $idUser");
                    mysqli_close($conexionDB);

                    if($query_update){
                        $code = '00';
                        $msg = "Su contraseña se ha actualizado con éxito.";
                    } else {
                        $code = '2';
                        $msg = "No es posible cambiar su contraseña.";
                    }
                } else {
                    $code = '1';
                    $msg = "La contraseña actual es incorrecta.";
                }
                $arrData = array('cod' => $code, 'msg' => $msg);
                echo json_encode($arrData,JSON_UNESCAPED_UNICODE);                                        

            } else {
                echo "error";
            }
            exit;
        }

    }
    exit;

    
?>