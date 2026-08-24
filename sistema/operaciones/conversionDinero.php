<?php 
    session_start();
    include "../../conexion.php";
    $alert = '';
    if(!empty($_POST)){
        if(!empty($_POST['monto'])){
            $monto = $_POST['monto'];
            if($monto > 0){
                $cambio = $_POST['cambio'];
                $fecha = $_POST['fecha'];
                $user = $_SESSION['idUser'];

                include "../includes/total_caja.php";

                if($cambio == "efectivoTarjeta"){
                    $totalEfectivo = $totalEfectivo - $monto;
                    $totalTarjeta = $totalTarjeta + $monto;
                    $totalEfectivoDia -= $monto;
                    $totalTarjetaDia += $monto;
                }else{
                    $totalEfectivo = $totalEfectivo + $monto;
                    $totalTarjeta = $totalTarjeta - $monto;
                    $totalEfectivoDia += $monto;
                    $totalTarjetaDia -= $monto;
                }

                $query_insert = mysqli_query($conexionDB,"INSERT INTO caja(Actividad, Monto_inicial, Monto_salida, totalEfectivoDia, totalTarjetaDia, totalCajaDia, utilidadDia, TotalEfectivo, TotalTarjeta, Total_caja, Utilidad, Cod_Empleado, Estado)
                                                        VALUES('Conversion de dinero', '$monto', '$monto',$totalEfectivoDia,$totalTarjetaDia,$totalCajaDia,$utilidadDia,'$totalEfectivo', '$totalTarjeta', '$totalCaja', '$utilidadTotal','$user', 'Abierto')");

                if($query_insert){
                    header("Location: ../index.php");
                }else{
                    $alert = '<p class="alert alert-danger">Error al registrar la conversión de dinero.</p>';
                }
            }else{
                $alert = '<p class="alert alert-danger">El monto debe ser mayor a cero</p>';
            }
        }else{
            $alert = '<p class="alert alert-danger">Todos los campos son obligatorios.</p>';
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
</head>
<body>
    <?php include "../includes/header_2.php" ?>
    <section id="container">
        <?php
            $usuario = $_SESSION['idUser'];
            $query = mysqli_query($conexionDB, "
                SELECT Estado, IdCaja FROM caja 
                WHERE IdCaja = (SELECT MAX(IdCaja) FROM caja)");
            mysqli_close($conexionDB);
            $resultado = mysqli_fetch_array($query);
            $estado = $resultado['Estado'] ?? 'cerrado';
            if($estado == 'Abierto'){
        ?>

        <div class="form_register">
            <h1>Conversión de dinero</h1>
            <hr>
            <div><?php echo isset($alert) ? $alert : ''; ?></div>

            <form action="" method="post">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="monto">Monto</label>
                        <input type="number" name="monto" id="monto" autofocus step="0.01">
                    </div>
                    <div class="col-md-6">
                        <label for="cambio">De</label>
                        <select name="cambio" id="cambio">
                            <option value="efectivoTarjeta">Efectivo a Tarjeta</option>
                            <option value="tarjetaEfectivo">Tarjeta a Efectivo</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="fecha">Fecha de conversión</label>
                        <input type="date" name="fecha" id="fecha" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-12 d-flex justify-content-between mt-3">
                        <button type="submit" class="btn_save_1"><i class="far fa-check-circle"></i> Confirmar</button>
                        <a href="../index.php" class="link_delete_1" style="float: right;"><i class="fas fa-minus-circle"></i> Cancelar</a>
                    </div>
                </div>
            </form>
        </div>

        <?php } else {
        ?>
                <div class="data_delete">
                    <i class="fas fa-cash-register fa-7x" style="color: #e66262"></i>
                    <br>
                    <h1 style="color: #ff1a1a; font-size: 25px;">DEBE ABRIR CAJA PARA INICIAR LA VENTA</h1>
                        <br><br>
                        <button class="btn_save" type="button" onclick="mostrarFormulario('abrirCaja', <?php echo $usuario; ?>)">Abrir caja</button>                        
                </div>
        <?php
            }
        ?>
    
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
    </section>
    <?php include "../includes/footer_2.php"; ?>
</body>
</html>