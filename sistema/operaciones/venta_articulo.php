<?php 
    session_start();
    include "../../conexion.php";

    $deleteClienteTemp = $conexionDB->prepare("
        DELETE 
        FROM cliente_temp
    ");
    if($deleteClienteTemp){
        if($deleteClienteTemp->execute()){
            $query = $conexionDB->prepare("
                SELECT codArticulo, cantidad, FactorAplicado
                FROM detalle_temp
            ");
            if($query->execute()){ 
                $result = $query->get_result();
                $row = $result->num_rows;
                if($row > 0){
                    while($producto = $result->fetch_assoc()){
                        $codArticulo = $producto['codArticulo'];
                        $cantidad = floatval($producto['cantidad']);
                        $factor = floatval(isset($producto['FactorAplicado']) ? $producto['FactorAplicado'] : 1);
                        $cantidadDevolver = $cantidad / $factor;
                        
                        $query_update = $conexionDB->prepare("
                            UPDATE articulos
                            SET Cantidad = Cantidad + ?
                            WHERE IdArticulo = ?
                        ");
                        if($query_update){
                            $query_update->bind_param("di", $cantidadDevolver, $codArticulo);
                            if($query_update->execute()){
                            }
                        }
                    }
                    $deleteDetalleTemp = $conexionDB->prepare("
                        DELETE
                        FROM detalle_temp
                    ");
                    if($deleteDetalleTemp){
                        $deleteDetalleTemp->execute();
                    }
                }else{
                    $deleteDetalleTemp = $conexionDB->prepare("
                        DELETE
                        FROM detalle_temp
                    ");
                    if($deleteDetalleTemp){
                        $deleteDetalleTemp->execute();
                    }
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "../includes/scripts_2.php"; ?>
    <?php include "../includes/title.php"; ?>
    <link rel="stylesheet" href="estilos/estilos.css">
</head>
<body>
    <?php include "../includes/header_2.php"; ?>
    <div id="rol" data-rol="<?= $_SESSION['rol']; ?>"></div>
    <?php
        $usuario = $_SESSION['idUser'];
        $query = mysqli_query($conexionDB, "SELECT Estado, IdCaja FROM caja WHERE IdCaja = (SELECT MAX(IdCaja) FROM caja)");
        $resultado = mysqli_fetch_array($query);
        $estado = $resultado['Estado'];
        mysqli_close($conexionDB);
        // $row = mysqli_num_rows($query);
        // if($row > 0){
        //     $estado = $resultado['Estado'];
        // }else{
        //     $estado = "Cerrado";
        // }
        if($estado == 'Abierto'){
    ?>
    <div class="container-fluid" style="padding-top: 110px;">
        <div class="row g-3">
            <div class="col-lg-2 col-md-6">
                <div class="card shadow-sm h-100 bg-white">
                    <div class="card-header text-center">
                        <h5 class="mb-0">Datos del cliente</h5>
                    </div>
                    <div class="card-body">
                        <form action="" class="form_venta">
                            <input type="hidden" id="idCliente" name="idCliente">
                            <?php if(!empty($_REQUEST['dni'])){ 
                                $dni = $_REQUEST['dni'];    
                            ?>
                                <label for="dniCliente">Dni:</label>
                                <input type="number" id="dniCliente" value="<?php echo $dni; ?>" name="dniCliente" oninput="limitarDigitos(this,8)">
                            <?php }else { ?>
                                <label for="dniCliente">Dni:</label>
                                <input type="number" id="dniCliente" name="dniCliente" oninput="limitarDigitos(this,8)">
                            <?php } ?>
                            <label for="nombreCliente">Nombre:</label>
                            <input type="text" id="nombreCliente" name="nombreCliente">
                            <label for="direccionCliente">Dirección:</label>
                            <input type="text" id="direccionCliente" name="direccionCliente">
                            <label for="telefonoCliente">Telofono:</label>
                            <input type="number" id="telefonoCliente" name="telefonoCliente" oninput="limitarDigitos(this,9)">
                            <label for="fechaRegistroCliente">Fecha de registro:</label>
                            <input type="date" id="fechaRegistroCliente" name="fechaRegistroCliente" value="<?php echo date('Y-m-d'); ?>">
                            <br>
                            <button id="registrarCliente" class="btn btn-primary" type="button" onclick="añadirCliente()">Guardar</button>
                            <button class="btn btn-secondary" type="button" onclick="quitarCliente()">Quitar</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm h-100 bg-white">
                    <div class="card-header text-center">
                        <h5 class="mb-0">Buscar producto</h5>
                    </div>
                    <div class="card-body">
                        <form action="" class="form_venta">
                            <label for="palabraClave">Buscar producto(nombre/C. barra)</label>
                            <input type="text" id="palabraClave" name="palabraClave">
                            <div id="resultados" class="list-group overflow-auto mt-2" style="max-height:20rem;"></div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-md-6">
                <div class="card shadow-sm h-100 bg-white">
                    <div class="card-header text-center">
                        <h5 class="mb-0">Detalle de la venta</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Cant.</th>
                                        <th>UdM</th>
                                        <th>Precio</th>
                                        <th>Dto%</th>
                                        <th>Sub Total</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="datosVenta">
                                </tbody>  
                            </table>
                        </div>                      
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="card shadow-sm h-100 bg-white">
                    <div class="card-header text-center">
                        <h5 class="mb-0">Pago</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="post" class="form_venta">
                            <label for="estadoVenta">Estado</label>
                            <select name="estadoVenta" id="estadoVenta">
                                <option value="pagado" selected>Pagado</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="saldo">Saldo</option>
                            </select>
                            <div id="metodoPagoContainer">
                                <label for="metodoPago">Metodo de pago</label>
                                <select name="metodoPago" id="metodoPago">
                                    <option value="efectivo" selected>Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="ambos">Ambos</option>
                                </select>
                            </div>
                            <div id="montoEfectivo">
                                <label for="efectivo">Monto efectivo: </label>
                                <input type="number" name="efectivo" id="efectivo" step="0.01">
                            </div>
                            <div id="montoTarjeta">
                                <label for="tarjeta">Monto tarjeta: </label>
                                <input type="number" name="tarjeta" id="tarjeta" step="0.01">
                            </div>
                            <div id="vueltoContainer">
                                <label for="vuelto">Vuelto: </label>
                                <input type="number" name="vuelto" id="vuelto" readonly value="0.00">
                            </div>
                            <div id="metodoVueltoContainer">
                                <label for="metodoVuelto">M. vuelto</label>
                                <select name="metodoVuelto" id="metodoVuelto">
                                    <option value="efectivo" selected>Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                </select>
                            </div>
                            <div id="saldoContainer">
                                <label for="saldo">Saldo: </label>
                                <input type="number" name="saldo" id="saldo" readonly value="0.00">
                            </div>
                            <label for="fechaVenta">Fecha: </label>
                            <input type="datetime-local" name="fechaVenta" id="fechaVenta" value="<?php echo date('Y-m-d H:i'); ?>">
                            <br>
                            <button class="btn btn-primary" type="button" onclick="procesarVenta()">Procesar</button>
                            <button class="btn btn-secondary" type="button" onclick="limpiarVenta()">Anular</button>
                        </form>
                    </div>
                </div>
            </div>
        </div> 
    </div>
    <?php } else { ?>
        <div id="container">
            <div class="data_delete">
                <i class="fas fa-cash-register fa-7x" style="color: #e66262"></i>
                <br>
                <h1 style="color: #ff1a1a; font-size: 25px;">DEBE ABRIR CAJA PARA INICIAR LA VENTA</h1>
                    <br><br>
                    <button class="btn_save" type="button" onclick="mostrarFormulario('abrirCaja', <?php echo $usuario; ?>)">Abrir caja</button>                        
            </div>
        </div>
    <?php } ?>
    
    <div id="abrirCaja" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Abrir caja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="idAbrirCaja" name="idAbrirCaja">
                    <div class="mb-3">
                        <label for="montoAbrirCaja">Monto inicial</label>
                        <input type="number" id="montoAbrirCaja" name="montoAbrirCaja" step="0.01" placeholder="Ingrese el monto inicial">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="button" onclick="abrirCajaDeOtraPagina()">Confirmar</button>
                    <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('abrirCaja')">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="añadirProducto" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Añadir articulo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="idProducto" name="idProducto">
                    <input type="hidden" id="factorAplicado" name="factorAplicado" value="1">
                    <input type="hidden" id="porcentajeDescuentoAplicado" name="porcentajeDescuentoAplicado" value="0">
                    <input type="hidden" id="precioMinimoArticulo" name="precioMinimoArticulo" value="0">
                    <input type="hidden" id="unidadSeleccionada" name="unidadSeleccionada" value="">
                    <input type="hidden" id="cantidadActual" name="cantidadActual" value="">
                    <div class="mb-3">
                        <label for="nombreProducto" class="form-label">Nombre del producto</label>
                        <input type="text" id="nombreProducto" name="nombreProducto" class="form-control" readonly>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="stockProducto" class="form-label">Stock disponible</label>
                            <input type="number" id="stockProducto" name="stockProducto" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="unidadVenta" class="form-label">Unidad de venta</label>
                            <select id="unidadVenta" name="unidadVenta" class="form-select" onchange="cambiarUnidadVenta()">
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="precioVenta" class="form-label">Precio de venta (UdM)</label>
                            <input type="number" id="precioVenta" name="precioVenta" step="0.01" class="form-control" required oninput="calcularDescuentoVenta()">
                        </div>
                        <div class="col-md-6">
                            <label for="precioMinimoMostrar" class="form-label">Precio mínimo</label>
                            <input type="number" id="precioMinimoMostrar" name="precioMinimoMostrar" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="stockVenta" class="form-label">Cantidad a vender</label>
                            <input type="number" id="stockVenta" name="stockVenta" required value="1" min="0.01" step="0.01" class="form-control" oninput="calcularDescuentoVenta()">
                        </div>
                    </div>
                    <div id="equivalenteVentaInfo" class="mb-3 small text-muted fw-semibold" style="display:none;"></div>
                    <div class="card bg-light mb-3">
                        <div class="card-header py-2">
                            <strong>Previsualización de descuentos y totales</strong>
                        </div>
                        <div class="card-body py-2">
                            <div class="row g-2 text-sm">
                                <div class="col-md-4">
                                    <label class="form-label mb-1">Descuento aplicable</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" id="descuentoMostrar" class="form-control" readonly value="0">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1">Precio c/descuento</label>
                                    <input type="number" id="precioConDescuentoMostrar" class="form-control form-control-sm" readonly step="0.01">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1">Subtotal</label>
                                    <input type="text" id="subTotalMostrar" class="form-control form-control-sm" readonly>
                                </div>
                            </div>
                            <div id="infoDescuentosEscalonados" class="mt-2 text-muted small"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-check form-switch">
                        <label class="form-check-label" for="aplicarDescuento">
                            Aplicar Descuento
                        </label>
                        <input class="form-check-input" type="checkbox" value="" id="aplicarDescuento" switch>
                    </div>
                    <button class="btn btn-primary" type="button" onclick="añadirProducto()">Añadir</button>
                    <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('añadirProducto')">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <?php include "../includes/footer_2.php"; ?>
    
</body>
</html>