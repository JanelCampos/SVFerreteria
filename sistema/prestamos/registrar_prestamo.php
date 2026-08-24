<?php 
    session_start();
    include "../../conexion.php";
    $totalEfectivoDia = 0;
    $totalTarjetaDia = 0;
    $totalCajaDia = 0;
    $utilidadDia = 0;
    $totalEfectivo = 0;
    $totalTarjeta = 0;
    $totalCaja = 0;
    $utilidadTotal = 0;


    if(!empty($_POST)){
        if(empty($_POST['montoCuota'] || $_POST['nombre']) || empty($_POST['monto']) || empty($_POST['cuotas']) || empty($_POST['fechaCuota'])){
            include "../alertas/msg_campos_obligatorios.php";
            mysqli_close($conexionDB);
        }else{
            $nombre = $_POST['nombre'];
            $monto = $_POST['monto'];
            $fechaPrestamo = $_POST['fechaPrestamo'];
            $metodoPago = $_POST['metodoPago'];
            $cuotas = $_POST['cuotas'];
            $montoCuota = $_POST['montoCuota'];
            $fechaCuota = $_POST['fechaCuota'];
            $descripcion = $_POST['descripcion'];
            $user = $_SESSION['idUser'];
            $montoPagar = $montoCuota * $cuotas;

            //ingresar datos en la tabla prestamos
            $query_insert = mysqli_query($conexionDB,"INSERT INTO prestamos(nombre, monto, montoPagar, fechaPrestamo, cuotas, montoCuota,fechaCuota, estado,descripcion, idEmpleado)
                                                    VALUES('$nombre','$monto','$montoPagar','$fechaPrestamo','$cuotas','$montoCuota','$fechaCuota', 'false','$descripcion','$user')");
            if($query_insert){
                $insert_id = $conexionDB->insert_id;
                $timestamp = strtotime($fechaCuota);
                 // Insertar la primera cuota con la fecha proporcionada
                 $query_insert_cuota = mysqli_query($conexionDB, "INSERT INTO cuotas(montoCuota, fechaCuota, estado, idPrestamo)
                                        VALUES($montoCuota, '$fechaCuota', 'false', $insert_id)");

                // Insertar las cuotas restantes con incremento de un mes
                for($i = 1; $i < $cuotas; $i++){
                    $timestamp = strtotime('+1 month', $timestamp);
                    $date = date('Y-m-d', $timestamp);
                    $query_insert_cuota = mysqli_query($conexionDB, "INSERT INTO cuotas(montoCuota, fechaCuota, estado, idPrestamo)
                                        VALUES($montoCuota, '$date', 'false', $insert_id)");
                }
                
                //insertar datos en caja
                include "../includes/suma_caja_sin_utilidad.php";

                $query_insert = mysqli_query($conexionDB,"INSERT INTO caja(Actividad, Monto_inicial, totalEfectivoDia, totalTarjetaDia, totalCajaDia, utilidadDia, TotalEfectivo, TotalTarjeta, Total_caja, Utilidad, Cod_Empleado, Estado)
                                                                        VALUES('Préstamo', $monto, $totalEfectivoDia, $totalTarjetaDia, $totalCajaDia, $utilidadDia, $totalEfectivo, $totalTarjeta, $totalCaja, $utilidadTotal, $user, 'Abierto')");

                if($query_insert){
                    mysqli_close($conexionDB);
                    header("Location: lista_prestamos.php");
                }else{
                    include "../alertas/msg_error.php";
                }

            }else{
                include "../alertas/msg_error.php";
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
</head>
<body>
    <?php include "../includes/header_2.php"; ?>
    <section id="container">
        <div class="form_register">
            <h1>Registrar préstamo</h1>
            <hr>
            <div><?php echo isset($alert) ? $alert : ''; ?></div>


            <form action="" method="post">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nombre">Nombre del banco</label>
                        <input type="text" name="nombre" id="nombre">
                    </div>
                    <div class="col-md-3">
                        <label for="monto">Monto de préstamo</label>
                        <input type="number" name="monto" id="monto" step="0.01">
                    </div>
                    <div class="col-md-3">
                        <label for="fechaPrestamo">Fecha de préstamo</label>
                        <input type="date" name="fechaPrestamo" id="fechaPrestamo" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="metodoPago">Método de pago</label>
                        <select name="metodoPago" id="metodoPago">
                            <option value="efectivo" selected>Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="cuotas">Numero de cuotas</label>
                        <input type="number" name="cuotas" id="cuotas">
                    </div>
                    <div class="col-md-3">
                        <label for="montoCuota">Monto de la cuota</label>
                        <input type="number" name="montoCuota" id="montoCuota" step="0.01">
                    </div>
                    <div class="col-md-3">
                        <label for="fechaCuota">Fecha de primera cuota</label>
                        <input type="date" name="fechaCuota" id="fechaCuota">
                    </div>
                    <div class="col-md-6">
                        <label for="descripcion">Descripción</label>
                        <textarea name="descripcion" id="descripcion"></textarea>
                    </div>
                </div>
                
                <div class="col-12 d-flex justify-content-between mt-3">
                    <button type="submit" class="btn_save_1"><i class="far fa-check-circle"></i> Confirmar</button>
                    <a href="lista_prestamos.php" class="link_delete_1" style="float: right;"><i class="fas fa-minus-circle"></i> Cancelar</a>
                </div>
            </form>
        </div>
    </section>
    
    <?php include "../includes/footer_2.php"; ?>    
</body>
</html>