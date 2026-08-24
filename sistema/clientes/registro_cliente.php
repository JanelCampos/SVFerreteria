<?php 

    include "../../conexion.php";    
    session_start();

    if(!empty($_POST)){
        $alert='';
        if(empty($_POST['nombre']) || empty($_POST['fecha_registro']) || empty($_POST['dni']) ){
               include "../alertas/msg_campos_obligatorios.php";
        } else {

            $nombre = $_POST['nombre'];
            $dni = $_POST['dni'];
            $telefono = $_POST['telefono'];
            $direccion = $_POST['direccion'];
            $fecha_registro = $_POST['fecha_registro'];
            $cantidadCompras = 0;
            $montoCompras = 0;
            $gananciaGenerada = 0;
        
            $query = mysqli_query($conexionDB,"SELECT * FROM clientes WHERE Dni = '$dni' ");
            $row = mysqli_num_rows($query);
            // $result = mysqli_fetch_array($query);

            if($row > 0){
                $alert = '<p class="alert alert-danger">El DNI ya existe.</p>';
                
            } else {
                $query_insert = mysqli_query($conexionDB,"INSERT INTO clientes(Nombre,Dni,Telefono,direccion,Fecha_Registro,cantidadCompras,montoCompras,gananciaGenerada)
                                                        VALUES('$nombre','$dni','$telefono','$direccion','$fecha_registro',$cantidadCompras,$montoCompras,$gananciaGenerada)");
                header("Location: lista_clientes.php");
        }
        mysqli_close($conexionDB);
        
        }
    }

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
        <div class="form_register">
            <h1>Registro de cliente</h1>
            <hr>
            <div><?php echo isset($alert) ? $alert : ''; ?></div>

            <form action="" method="post" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label for="nombre">Nombre y Apellidos</label>
                        <input type="text" name="nombre" id="nombre" placeholder="Ingrese Nombre Completo">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="dni">Dni</label>
                        <input type="number" name="dni" id="dni" placeholder="Ingrese el DNI" oninput="limitarDigitos(this,8)">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="telefono">Teléfono</label>
                        <input type="number" name="telefono" id="telefono" placeholder="Ingrese un Teléfono" oninput="limitarDigitos(this,9)">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="direccion">Dirección</label>
                        <input type="text" name="direccion" id="direccion" placeholder="Ingrese la dirección">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha_registro">Fecha de registro</label>
                        <input type="date" name="fecha_registro" id="fecha_registro" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-12 d-flex justify-content-between mt-3">
                        <button type="submit" class="btn_save_1"><i class="far fa-save"></i> Guardar Cliente</button>
                        <a href="lista_clientes.php" class="link_delete_1" style="float: right;"><i class="fas fa-minus-circle"></i> Cancelar</a>
                    </div>
                </div>
            </form>

        </div>                  
	</section>
	<?php include "../includes/footer_2.php"; ?>
</body>
</html>