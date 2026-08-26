<?php 
    include "../../conexion.php";
    session_start();
    $totalEfectivo = 0;
    $totalTarjeta = 0;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $alert = '';
        if (empty($_POST['monto'])) {
            $alert = '<p class="alert alert-danger">Ingrese valor en todos los campos obligatorios.</p>';
        } else {
            $monto = $_POST['monto'];
            $fechaGasto = $_POST['fechaGasto'];
            $metodoPago = $_POST['medioPago'];
            $tipoGasto = $_POST['tipoGasto'];
            $descripcion = $_POST['descripcion'];
            $usuario = $_SESSION['idUser'];

            if ($monto > 0) {
                // Insertar datos en la tabla gastos
                $query_insert = mysqli_query($conexionDB, "INSERT INTO gastos(montoGasto, fechaGasto, medioPago, tipoGasto, descripcion)
                                                            VALUES('$monto', '$fechaGasto', '$metodoPago', '$tipoGasto', '$descripcion')");

                if ($query_insert) {
                    // Calcular total de caja
                    include "../includes/total_caja.php";

                    if ($metodoPago == "efectivo") {
                        $montoEfectivo = $totalEfectivo - $monto;
                        $montoTarjeta = $totalTarjeta;
                        $totalEfectivoDia -= $monto;
                    } else {
                        $montoTarjeta = $totalTarjeta - $monto;
                        $montoEfectivo = $totalEfectivo;
                        $totalTarjetaDia -= $monto;
                    }

                    if ($tipoGasto == "personal") {
                        $utilidad = $utilidadTotal - $monto;
                        $utilidadDia -= $monto;
                    } else {
                        $utilidad = $utilidadTotal;
                    }

                    $totalCajaDia -= $monto;
                    $total = $totalCaja - $monto;

                    $query_insert_caja = mysqli_query($conexionDB, "INSERT INTO caja(Actividad, Monto_salida, totalEfectivoDia, totalTarjetaDia, totalCajaDia, utilidadDia, TotalEfectivo, TotalTarjeta, Total_caja, Utilidad, Cod_Empleado, Estado)
                                                            VALUES('Egreso de dinero', '$monto', $totalEfectivoDia, $totalTarjetaDia, $totalCajaDia, $utilidadDia, '$montoEfectivo', '$montoTarjeta', '$total', '$utilidad', '$usuario', 'Abierto')");

                    if ($query_insert_caja) {
                        mysqli_close($conexionDB);
                        header('Location: ../reportes/gastosDelDia.php'); // Redirigir a una página de éxito
                        exit;
                    } else {
                        $alert = '<p class="alert alert-danger">Error al registrar en la caja.</p>';
                    }
                } else {
                    $alert = '<p class="alert alert-danger">Error al registrar el gasto.</p>';
                }
            } else {
                $alert = '<p class="alert alert-danger">El valor debe ser mayor a 0.</p>';
            }
        }
    }

    // Verificar el estado de la caja
    $usuario = $_SESSION['idUser'];
    $query = mysqli_query($conexionDB, "SELECT Estado, IdCaja FROM caja WHERE IdCaja = (SELECT MAX(IdCaja) FROM caja)");
    $resultado = mysqli_fetch_array($query);
    $estado = $resultado['Estado'] ?? '';
    mysqli_close($conexionDB);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php include "../includes/scripts_2.php"; ?>
    <?php include "../includes/title.php"; ?>
</head>
<body>
    <?php include "../includes/header_2.php"; ?>
    <section id="container">
        <?php if ($estado == 'Abierto') { ?>
        <div class="form_register">
            <h1>Registrar Gasto</h1>
            <hr>
            <div><?php echo isset($alert) ? $alert : ''; ?></div>

            <form action="" method="post">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="monto">Monto</label>
                        <input type="number" name="monto" id="monto" placeholder="Ingrese monto en S/." step="0.01">
                    </div>
                    <div class="col-md-6">
                        <label for="fechaGasto">Fecha de gasto</label>
                        <input type="date" name="fechaGasto" id="fechaGasto" value='<?php echo date('Y-m-d'); ?>'>
                    </div>
                    <div class="col-md-6">
                        <label for="medioPago">Medio de pago</label>
                        <select name="medioPago" id="medioPago">
                            <option value="efectivo" selected>Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="tipoGasto">Tipo de gasto</label>
                        <select name="tipoGasto" id="tipoGasto">
                            <option value="personal" selected>Personal</option>
                            <?php if($_SESSION['rol'] == 1){ ?>
                                <option value="capital">Capital</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="descripcion">Descripción</label>
                        <textarea name="descripcion" id="descripcion"></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-between mt-3">
                        <button type="submit" class="btn_save_1"><i class="far fa-check-circle"></i> Confirmar</button>
                        <a href="../index.php" class="link_delete_1" style="float: right;"><i class="fas fa-minus-circle"></i> Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
        <?php } else { ?>
        <div class="data_delete">
            <i class="fas fa-cash-register fa-7x" style="color: #e66262"></i>
            <br>
            <h1 style="color: #ff1a1a; font-size: 25px;">DEBE ABRIR CAJA PARA INICIAR LA VENTA</h1>
            <br><br>
            <button class="btn_save" type="button" onclick="mostrarFormulario('abrirCaja', <?php echo $usuario; ?>)">Abrir caja</button>                        
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
    </section>
    <?php include "../includes/footer_2.php"; ?>
</body>
</html>
