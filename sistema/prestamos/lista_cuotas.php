<?php 
    session_start();
    include "../../conexion.php";

    if(!empty($_REQUEST['id'])){
        $idPrestamo = $_REQUEST['id'];
        //traer datos
        $query = mysqli_query($conexionDB,"
            SELECT * 
            FROM cuotas 
            WHERE idPrestamo = $idPrestamo");
        $result = mysqli_num_rows($query);

        $queryPrestamo = mysqli_query($conexionDB,"
            SELECT * 
            FROM prestamos 
            WHERE idPrestamo = $idPrestamo");
        $dataPrestamo = mysqli_fetch_array($queryPrestamo);
        $nombrePrestamista = $dataPrestamo['nombre'];
    }

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
            <h1>Listado de cuotas de <?php echo $nombrePrestamista; ?></h1>
            <a href="lista_prestamos.php" class="btn_new"><i class="fas fa-arrow-left"></i> Volver a préstamos</a>
        </div>

        <div class="containerTable">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Monto de cuota</th>
                        <th>Fecha de pago</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($result > 0){
                        while($data = mysqli_fetch_array($query)){
                    ?>
                    <tr>
                        <td><?php echo $data['idPrestamo'] ?></td>
                        <td>S/. <?php echo $data['montoCuota']; ?></td>
                        <td><?php echo $data['fechaCuota']; ?></td>
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
                                    <?php if($estado) { ?>
                                        <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('cancelarPago', <?php echo $data['idCuota']; ?>, <?php echo $data['montoCuota']; ?>, <?php echo $data['idPrestamo']; ?>)">Cancelar pago</button></li>
                                    <?php } else{ ?>
                                        <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('procesarPago', <?php echo $data['idCuota']; ?>, <?php echo $data['montoCuota']; ?>, <?php echo $data['idPrestamo']; ?>)">Pagar</button></li>
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
    </section>

    <div id="procesarPago" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                        <h2>Pago de cuota</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="idCuotaPago" name="idCuotaPago" value="">
                    <input type="hidden" id="idPrestamoPago" name="idPrestamoPago" value="">
                    <div class="mb-3">
                            <label for="montoCuotaPago">Monto:</label>
                    <input type="number" id="montoCuotaPago" name="montoCuotaPago" value="" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="metodoPagoCuota">Metodo de pago</label>
                        <select name="metodoPagoCuota" id="metodoPagoCuota">
                            <option value="efectivo" selected>Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="button" onclick="procesarPagoCuota()">Confirmar</button>
                    <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('procesarPago')">Cancelar</button>
                </div>
            </div>
        </div>
        
    </div>

    <div id="cancelarPago" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                        <h2>Cancelar pago de cuota</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="idCuotaCancelar" name="idCuotaCancelar" value="">
                    <input type="hidden" id="idPrestamoCancelar" name="idPrestamoCancelar" value="">
                    <div class="mb-3">
                        <label for="montoCuotaCancelar">Monto:</label>
                        <input type="number" id="montoCuotaCancelar" name="montoCuotaCancelar" value="" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="metodoPagoCancelar">Metodo de pago</label>
                        <select name="metodoPagoCancelar" id="metodoPagoCancelar">
                            <option value="efectivo" selected>Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="button" onclick="cancelarPagoCuota()">Cofirmar</button>
                    <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('cancelarPago')">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    <?php include "../includes/footer_2.php"; ?>    
</body>
</html>