<?php 

    include "../../conexion.php";    
    session_start();

    if(!empty($_POST)){
        $alert='';
        if(empty($_POST['nombre']) || empty($_POST['dni']) || empty($_POST['direccion']) ||
           empty($_POST['telefono']) || empty($_POST['correo']) || empty($_POST['usuario']) ||
           empty($_POST['clave']) || empty($_POST['rol'])){
               include "../alertas/msg_error.php";
        } else {

            $nombre = $_POST['nombre'];
            $dni = $_POST['dni'];
            $direccion = $_POST['direccion'];
            $telefono = $_POST['telefono'];
            $email = $_POST['correo'];
            $user = $_POST['usuario'];
            $clave = $_POST['clave'];
            $clave_hash = password_hash($clave, PASSWORD_DEFAULT);
            $rol = $_POST['rol'];

            $query = mysqli_query($conexionDB,"SELECT * FROM empleados WHERE Usuario = '$user' OR Email = '$email' ");
            $result = mysqli_fetch_array($query);

            if($result > 0){
                $alert = '<p class="alert alert-danger">El correo o el usuario ya existe.</p>';
            } else {
                $query_insert = mysqli_query($conexionDB,"INSERT INTO empleados(Nombre,Dni,Direccion,Telefono,Email,Usuario,Clave,Rol)
                                                        VALUES('$nombre','$dni','$direccion','$telefono','$email','$user','$clave_hash','$rol')");
                mysqli_close($conexionDB);
                if($query_insert){
                    header("Location: lista_usuarios.php");
                } else {
                    $alert = '<p class="alert alert-danger">Error al crear el usuario.</p>';
                    header("Location: registro_usuario.php");
                }
            }
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
            <h1>Registro de usuario</h1>
            <hr>
            <div><?php echo isset($alert) ? $alert : ''; ?></div>

            <form action="" method="post">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Ingrese Nombre Completo">
                    </div>

                    <div class="col-md-3">
                        <label for="dni" class="form-label">DNI</label>
                        <input type="number" class="form-control" name="dni" id="dni"
                            placeholder="Ingrese el DNI"
                            oninput="limitarDigitos(this,8)">
                    </div>

                    <div class="col-md-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="number" class="form-control" name="telefono" id="telefono"
                            placeholder="Ingrese un Teléfono"
                            oninput="limitarDigitos(this,9)">
                    </div>

                    <div class="col-md-6">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control" name="direccion" id="direccion"
                            placeholder="Ingrese una Dirección">
                    </div>

                    <div class="col-md-6">
                        <label for="correo" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" name="correo" id="correo"
                            placeholder="Ingrese un Correo electrónico">
                    </div>

                    <div class="col-md-6">
                        <label for="usuario" class="form-label">Usuario</label>
                        <input type="text" class="form-control" name="usuario" id="usuario"
                            placeholder="Ingrese un Usuario">
                    </div>

                    <div class="col-md-6">
                        <label for="clave" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="clave" id="clave"
                            placeholder="Ingrese una Contraseña">
                    </div>

                    <?php
                        include "../../conexion.php";
                        $query_rol = mysqli_query($conexionDB,"SELECT * FROM rol");
                        mysqli_close($conexionDB);
                        $result_rol = mysqli_num_rows($query_rol);
                    ?>

                    <div class="col-md-6">
                        <label for="rol" class="form-label">Tipo de usuario</label>
                        <select name="rol" id="rol" class="form-select">
                            <option value="0">- Seleccione -</option>

                            <?php
                            if($result_rol > 0){
                                while ($rol = mysqli_fetch_array($query_rol)){
                            ?>
                                <option value="<?php echo $rol['IdRol']; ?>">
                                    <?php echo $rol['rol']; ?>
                                </option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-12 d-flex justify-content-between mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="far fa-save"></i> Crear usuario
                        </button>

                        <a href="lista_usuarios.php" class="btn btn-secondary">
                            <i class="fas fa-times-circle"></i> Cancelar
                        </a>
                    </div>

                </div>

            </form>

        </div>

	</section>
	<?php include "../includes/footer_2.php"; ?>
</body>
</html>