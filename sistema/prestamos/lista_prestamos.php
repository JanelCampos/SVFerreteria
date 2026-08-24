<?php 
    session_start();
    include "../../conexion.php";
    include "../includes/paginador.php";


    $results_per_page = 10;
    $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $consulta = "
        SELECT *
        FROM prestamos
        ORDER BY idPrestamo DESC
    ";
    $where = "
        prestamos
        ORDER BY idPrestamo DESC
    ";
    list($result, $total_records) = getPaginatedDataAll($conexionDB, $consulta, $where, $current_page, $results_per_page);
    $row = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "../includes/scripts_2.php"; ?>
</head>
<body>
    <?php include "../includes/header_2.php"; ?>
    <section id="container">
        <div class="title_container">
            <h1>Listado de préstamos</h1>
            <a href="registrar_prestamo.php" class="btn_new"><i class="fas fa-plus"></i> Nuevo préstamo</a>
        </div>
        <div class="header_container">
            <div class="row g-3 align-items-end">   
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="busquedaPrestamo">Nombre</label>
                    <input class="filtrosBusqueda" type="text" name="busquedaPrestamo" id="busquedaPrestamo" placeholder="Nombre">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary flex-grow-1" onclick="buscarPrestamo()">Aplicar filtros</button>
                        <a href="lista_prestamos.php" class="btn btn-outline-secondary flex-grow-1">Restablecer</a>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-4 ms-lg-auto">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <button type="button" class="btn btn-danger" onclick="exportarPrestamosPDF()">Exportar PDF</button>
                        <button type="button" class="btn btn-success" onclick="exportarPrestamosEXCEL()">Exportar Excel</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="containerTable">
            <table id="tablaPrestamos">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Nombre</th>
                        <th>Monto</th>
                        <th>N° Cuotas</th>
                        <th>M. cuota</th>
                        <th>T. a pagar</th>
                        <th>F. préstamo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($row > 0){
                        while($data = $result->fetch_assoc()){
                    ?>
                    <tr>
                        <td><?php echo $data['idPrestamo']; ?></td>
                        <td><?php echo $data['nombre']; ?></td>
                        <td>S/. <?php echo number_format($data['monto'],2); ?></td>
                        <td><?php echo $data['cuotas']; ?></td>
                        <td>S/. <?php echo number_format($data['montoCuota'],2); ?></td>
                        <td>S/. <?php echo number_format($data['montoPagar'],2); ?></td>
                        <td><?php echo $data['fechaPrestamo']; ?></td>
                        <?php 
                            $estado = $data['estado'];
                            if($estado){
                                ?>
                                <td class="pagado"><?php echo "Pagado"; ?></td>
                                <?php
                            }else{
                                ?>
                                <td class="pendiente"><?php echo "Pendiente"; ?></td>
                                <?php
                            }
                        ?>
                        <td>
                            <div class="btn-group dropend">
                                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Acciones
                                </button>
                                <ul class="dropdown-menu">
                                    <li><button class="dropdown-item" type="button" onclick="window.location.href='lista_cuotas.php?id=<?php echo $data["idPrestamo"]; ?>';">Ver cuotas</button></li>
                                    <?php if(!$data['estado']) { ?>
                                        <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('pagarPrestamo', <?php echo $data['idPrestamo']; ?>)">Pagar</button></li>
                                    <?php } ?>
                                    <?php if($data['estado']){ ?>
                                        <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('eliminarPrestamo', <?php echo $data['idPrestamo']; ?>)">Eliminar</button></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </td> 
                    </tr>
                    <?php
                        }
                    } 
                    ?>
                </tbody>
            </table>
        </div>

        <div id="pagarPrestamo" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Pago préstamo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idPrestamo" name="idPrestamo">
                        <div class="mb-3">
                            <label for="nombrePrestamista">Nombre del prestamista</label>
                            <input type="text" id="nombrePrestamista" name="nombrePrestamista" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="montoPagar">Deuda:</label>
                            <input type="number" id="montoPagar" name="montoPagar" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="monto">Monto a pagar</label>
                            <input type="number" id="monto" name="monto" required step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="metodoDePago">Metodo de pago</label>
                            <select name="metodoDePago" id="metodoDePago">
                                <option value="efectivo" selected>Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="button" onclick="pagarPrestamo()">Confirmar</button>
                        <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('pagarPrestamo')">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="eliminarPrestamo" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Eliminar préstamo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idPrestamoEliminar" name="idPrestamoEliminar" value="">
                        <div class="mb-3">
                            <p>¿está seguro de eliminar el préstamo?</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="button" onclick="eliminarPretamo()">Confirmar</button>
                        <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('eliminarPrestamo')">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // Renderizar el paginador
        renderPaginator($total_records, $results_per_page, $current_page, 'lista_prestamos.php');
        ?>
        <div id="paginator"></div>
    </section>
    <?php include "../includes/footer_2.php"; ?>    
</body>
</html>