<?php
    include "../../conexion.php";
    session_start();
    include "../includes/paginador.php";

    $results_per_page = 10;
    $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $consulta = "
        SELECT v.IdVenta, v.Fecha, v.Cod_Caja, v.dniCliente, cl.Nombre, v.Total, v.Estado, v.saldo, v.Medio_Pago,
        c.Cod_Empleado as empl, e.Nombre as nempl, v.utilidad
        FROM ventas v 
        INNER JOIN caja c ON v.Cod_Caja = c.IdCaja
        INNER JOIN empleados e ON c.Cod_Empleado = e.IdEmpleado
        INNER JOIN clientes cl ON cl.Dni = v.dniCliente
        WHERE DATE(v.Fecha) = CURDATE()
        ORDER BY IdVenta DESC
    ";
    $where = "
        ventas v 
        INNER JOIN caja c ON v.Cod_Caja = c.IdCaja
        INNER JOIN empleados e ON c.Cod_Empleado = e.IdEmpleado
        INNER JOIN clientes cl ON cl.Dni = v.dniCliente
        WHERE DATE(v.Fecha) = CURDATE()
        ORDER BY IdVenta DESC
    ";
    list($result, $total_records) = getPaginatedDataVentas($conexionDB, $consulta, $where, $current_page, $results_per_page);

    $row = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<?php include "../includes/scripts_2.php"; ?>
    <?php include "../includes/title.php"; ?>
    <style>
        #tarjeta_container{
            display: none;
        }

        #vuelto_pendiente_container{
            display: none;
        }

        #saldo_container{
            display: none;
        }
    </style>
</head>
<body>
    
    <?php include "../includes/header_2.php"; ?>
    <div id="rol" data-rol="<?= $_SESSION['rol']; ?>"></div>
	<section id="container">
        <div class="bg-white p-3 rounded shadow mb-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="mb-0">
                    <h1>Ventas del día</h1>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="../operaciones/venta_articulo.php" class="btn_new"><i class="fas fa-plus"></i> Venta Articulo</a>
                    <?php if ($_SESSION['rol'] == 1) { ?>
                        <a href="ventas.php" class="btn_new"><i class="fas fa-plus"></i> Listado de ventas</a>
                        <a href="ventasDelMes.php" class="btn_new"><i class="fas fa-plus"></i> Ventas del mes</a>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="header_container">
            <div class="row g-3 align-items-end">   
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="busquedaVentaDia">Id / Dni / Nombre Cliente</label>
                    <input class="filtrosBusqueda" type="text" name="busquedaVentaDia" id="busquedaVentaDia" placeholder="Id / Dni Cliente/ Nombre Cliente">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="busquedaNombreProductoDia">Nombre de Producto</label>
                    <input class="filtrosBusqueda" type="text" name="busquedaNombreProductoDia" id="busquedaNombreProductoDia" placeholder="Nombre de Producto">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="busquedaMedioPagoDia">Medio Pago</label>
                    <select class="filtrosBusqueda" name="busquedaMedioPagoDia" id="busquedaMedioPagoDia">
                        <option value="">Medio pago</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="ambos">Ambos</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="busquedaEstadoDia">Estado</label>
                    <select class="filtrosBusqueda" name="busquedaEstadoDia" id="busquedaEstadoDia">
                        <option value="">Estado</option>
                        <option value="pagado">Pagado</option>
                        <option value="saldo">Saldo</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="anulado">Anulado</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" onclick="buscarVentaDia()">Aplicar filtros</button>
                        <a href="ventasDelDia.php" class="btn btn-outline-secondary">Restablecer</a>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-4 ms-lg-auto">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <button type="button" class="btn btn-danger" onclick="exportarVentasPDFDia()">Exportar PDF</button>
                        <button type="button" class="btn btn-success" onclick="exportarVentasEXCELDia()">Exportar Excel</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="containerTable">
            <table id="tablaVentas">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Fecha / Hora: Venta</th>
                        <th>ID-Vendedor</th>
                        <th>DNI Cliente</th>
                        <th>Nombre cliente</th>
                        <th>Total</th>
                        <?php if($_SESSION['rol'] == 1){ ?>
                            <th>Utilidad</th>
                        <?php } ?>
                        <th>Factura</th>
                        <th>Estado</th>
                        <th>Pago</th>
                        <th>Saldo</th>
                        <th>Acciones</th>                
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if($row > 0){
                            while ($data = $result->fetch_assoc()){
                                $estadoVenta = $data['Estado'];
                            ?>
                                <tr id="row_<?php echo $data["IdVenta"]; ?>">
                                    <td><?php echo $data['IdVenta']; ?></td>
                                    <td><?php echo date("d-m-Y H:i:s", strtotime($data["Fecha"])); ?></td>
                                    <td><?php echo $data["empl"]; ?>-<?php echo $data["nempl"]; ?></td>
                                    <td><?php echo $data['dniCliente']; ?></td>
                                    <td><?php echo $data['Nombre']; ?></td>
                                    <td>S/. <?php echo $data["Total"]; ?></td>
                                    <?php if($_SESSION['rol'] == 1){ ?>
                                        <td>S/. <?php echo $data['utilidad']; ?></td>
                                    <?php } ?>
                                    <td>
                                        <div class="div_acciones">
                                            <div>
                                                <?php 
                                                    if($estadoVenta === 'pagado'){
                                                        ?>
                                                        <button class="btn btn-primary" type="button" onclick="generarPDF(<?php echo $data['dniCliente']; ?>, <?php echo $data['IdVenta']; ?>)"><i class="fas fa-print"></i></button>
                                                        <button class="btn btn-primary" type="button" onclick="generarTicket(<?php echo $data['IdVenta']; ?>)"><i class="fas fa-tag"></i></button>
                                                        <?php
                                                    }else if($estadoVenta === 'pendiente'){
                                                        ?>
                                                        <button class="btn btn-primary" type="button" onclick="generarPDF(<?php echo $data['dniCliente']; ?>, <?php echo $data['IdVenta']; ?>)"><i class="fas fa-print"></i></button>
                                                        <button class="btn btn-primary" type="button" onclick="generarTicket(<?php echo $data['IdVenta']; ?>)"><i class="fas fa-tag"></i></button>
                                                        <?php 
                                                    }else if($estadoVenta === 'saldo'){
                                                        ?>
                                                        <button class="btn btn-primary" type="button" onclick="generarPDF(<?php echo $data['dniCliente']; ?>, <?php echo $data['IdVenta']; ?>)"><i class="fas fa-print"></i></button>
                                                        <button class="btn btn btn-primary" type="button" onclick="generarTicket(<?php echo $data['IdVenta']; ?>)"><i class="fas fa-tag"></i></button>
                                                        <?php 
                                                    }
                                                ?>
                                            </div>
                                        </div>
                                    </td>
                                    <?php 
                                        if($estadoVenta === 'pagado'){
                                    ?>
                                        <td class="pagado"><?php echo $data['Estado']; ?></td>
                                    <?php
                                        }else if($estadoVenta === 'pendiente'){
                                    ?>
                                        <td class="pendiente"><?php echo $data['Estado']; ?></td>
                                    <?php
                                        }else if($estadoVenta === 'saldo'){
                                    ?>
                                        <td class="saldo"><?php echo $data['Estado']; ?></td>
                                    <?php 
                                        }else{
                                    ?>
                                        <td class="anulado"><?php echo $data['Estado']; ?></td>
                                    <?php
                                        }
                                    ?>
                                    <td><?php echo $data['Medio_Pago']; ?></td>
                                    <td><?php echo number_format($data['saldo'],2); ?></td>
                                    <td>
                                        <div class="btn-group dropend">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Acciones
                                            </button>
                                            <ul class="dropdown-menu">
                                                <?php 
                                                    $estadoVenta = $data['Estado'];
                                                    $idVenta = $data['IdVenta'];
                                                    if($estadoVenta === 'pagado'){
                                                ?>
                                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('anularVentaDia',<?php echo $idVenta; ?>)">Anular</button></li>
                                                <?php 
                                                    }else if($estadoVenta === 'pendiente' || $estadoVenta === 'saldo'){
                                                ?>
                                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('pagarVentaDia', <?php echo $idVenta; ?>)">Pagar</button></li>
                                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('anularVentaDia', <?php echo $idVenta; ?>)">Anular</button></li>
                                                <?php 
                                                    }else{
                                                ?>
                                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('eliminarVentaDia',<?php echo $idVenta; ?>)">Eliminar</button></li>
                                                <?php 
                                                    }
                                                ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                    <?php
                            }
                        }else{
                            ?>
                            <tr>
                                <td colspan="12" class="text-center">No se encontraron resultados.</td>
                            </tr>
                            <?php
                        }
                    ?>
                </tbody>
            </table>
        </div>
        
        <?php 
            renderPaginator($total_records, $results_per_page, $current_page, 'ventasDelDia.php');
        ?>
        <div id="paginator"></div>

        <div id="pagarVentaDia" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Pagar venta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idPagarVentaPendiente" name="idPagarVentaPendiente">
                        <input type="hidden" id="utilidadVentaPendiente" name="utilidadVentaPendiente">
                        <input type="hidden" id="tipoFiltro" name="tipoFiltro" value="dia">
                        <div class="mb-3">
                            <label for="montoTotalVenta">Monto total a pagar</label>
                            <input type="number" id="montoTotalVenta" name="montoTotalVenta" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="metodoPagoPendiente">Metodo pago</label>
                            <select name="metodoPagoPendiente" id="metodoPagoPendiente">
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="ambos">Ambos</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div id="efectivo_container">
                                <label for="efectivoPendiente">Monto efectivo: </label>
                                <input type="number" name="efectivoPendiente" id="efectivoPendiente" step="0.01">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div id="tarjeta_container">
                                <label for="tarjetaPendiente">Monto tarjeta: </label>
                                <input type="number" name="tarjetaPendiente" id="tarjetaPendiente" step="0.01">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div id="vuelto_pendiente_container">
                                <label for="vueltoPendiente">Vuelto: </label>
                                <input type="number" name="vueltoPendiente" id="vueltoPendiente" readonly value="0.00">
                                <label for="metodoVueltoPendiente">M. vuelto</label>
                                <select name="metodoVueltoPendiente" id="metodoVueltoPendiente">
                                    <option value="efectivo" selected>Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div id="saldo_container">
                                <label for="saldoPendiente">Saldo</label>
                                <input type="number" id="saldoPendiente" name="saldoPendiente" step="0.01" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="button" onclick="pagarVenta()">Confirmar</button>
                        <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('pagarVentaDia')">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="anularVentaDia" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Anular venta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idVenta" name="idVenta">
                        <input type="hidden" id="tipoFiltro" name="tipoFiltro" value="dia">
                        <div class="mb-3">
                            <label for="montoVenta">Monto total de la venta</label>
                            <input type="number" name="montoVenta" id="montoVenta" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="montoPagado">Monto pagado</label>
                            <input type="number" name="montoPagado" id="montoPagado" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="saldoVenta">Saldo</label>
                            <input type="number" name="saldoVenta" id="saldoVenta" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="metodoAnulacion">Metodo de devolución</label>
                            <select name="metodoAnulacion" id="metodoAnulacion">
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <p class="bg-danger text-white p-2">¿Está seguro de anular la venta? esta operación no se puede revertir.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="button" onclick="anularVenta()">Confirmar</button>
                        <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('anularVentaDia')">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="eliminarVentaDia" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Eliminar venta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idVenta" name="idVenta" value="">
                        <input type="hidden" id='estadoVentaEliminacion' name="estadoVentaEliminacion">
                        <input type="hidden" id="tipoFiltro" name="tipoFiltro" value="dia">
                        <div class="mb-3">
                            <p class="bg-danger text-white p-2">¿Está seguro de eliminar la venta? esta operación no se puede revertir.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="button" onclick="eliminarVenta()">Confirmar</button>
                        <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('eliminarVentaDia')">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
	</section>
	<?php include "../includes/footer_2.php"; ?>
</body>
</html>