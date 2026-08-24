<?php 

    include "../../conexion.php";  
    session_start();  

    if(!empty($_POST)){
        $alert='';
        if(empty($_POST['nombre']) || empty($_POST['telefono']) ){
               include "../alertas/msg_campos_obligatorios.php";
        } else {

            $nombre    = $_POST['nombre'];
            $direccion = $_POST['direccion'];
            $telefono  = $_POST['telefono'];
            $email     = $_POST['correo'];
            $ruc = $_POST['ruc'];

            $query_insert = mysqli_query($conexionDB,"INSERT INTO proveedores(ruc,Nombre,Direccion,Telefono,Email)
                                                        VALUES($ruc,'$nombre','$direccion','$telefono','$email')");

            if($query_insert){
                header("Location: lista_proveedores.php");
            } else {
                include "../alertas/msg_error.php";
            }
        }
        mysqli_close($conexionDB);
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
            <h1>Registro de proveedor</h1>
            <hr>
            <div><?php echo isset($alert) ? $alert : ''; ?></div>

            <form action="" method="post">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="ruc">Ruc</label>
                        <input type="number" name="ruc" id="ruc" placeholder="Ingrese el ruc">
                    </div>
                    <div class="col-md-6">
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" placeholder="Ingrese el Nombre">
                    </div>
                    <div class="col-md-6">
                        <label for="direccion">Dirección</label>
                        <input type="text" name="direccion" id="direccion" placeholder="Ingrese una Dirección">
                    </div>
                    <div class="col-md-6">
                        <label for="telefono">Teléfono</label>
                        <input type="number" name="telefono" id="telefono" placeholder="Ingrese un Teléfono" oninput="limitarDigitos(this,9)">
                    </div>
                    <div class="col-md-6">
                        <label for="correo">Email</label>
                        <input type="email" name="correo" id="correo" placeholder="Ingrese un Correo electrónico">
                    </div>
                    <div class="col-12 d-flex justify-content-between mt-3">
                        <button type="submit" class="btn_save_1"><i class="far fa-save"></i> Guardar Proveedor</button>
                        <a href="lista_proveedores.php" class="link_delete_1" style="float: right;"><i class="fas fa-minus-circle"></i> Cancelar</a>
                    </div>
                </div>
            </form>

        </div>

	</section>
	<?php include "../includes/footer_2.php"; ?>
</body>
</html>