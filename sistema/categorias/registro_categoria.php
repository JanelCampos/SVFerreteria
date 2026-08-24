<?php
    include "../../conexion.php";
    session_start();

    if ($_SESSION['rol'] != 1) {
        header("Location: ../index.php");
        exit;
    }

    $alert = '';
    if (!empty($_POST)) {
        if (empty($_POST['nombre'])) {
            include "../alertas/msg_campos_obligatorios.php";
        } else {
            $nombre = trim($_POST['nombre']);
            $descripcion = trim($_POST['descripcion']);

            $query_check = $conexionDB->prepare("SELECT IdCategoria FROM categorias WHERE Nombre = ? AND Estado = 1");
            $query_check->bind_param("s", $nombre);
            $query_check->execute();
            $result_check = $query_check->get_result();
            $query_check->close();

            if ($result_check->num_rows > 0) {
                $alert = '<p class="alert alert-danger">El nombre de la categoría ya existe</p>';
            } else {
                $query_insert = $conexionDB->prepare("INSERT INTO categorias(Nombre, Descripcion, Estado) VALUES (?, ?, 1)");
                $query_insert->bind_param("ss", $nombre, $descripcion);
                if ($query_insert->execute()) {
                    header("Location: lista_categorias.php");
                    exit;
                } else {
                    include "../alertas/msg_error.php";
                }
                $query_insert->close();
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
            <h1>Registro de categoría</h1>
            <hr>
            <div><?php echo isset($alert) ? $alert : ''; ?></div>
            <form action="" method="post">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" placeholder="Ingrese el nombre de la categoría" required>
                    </div>
                    <div class="col-md-6">
                        <label for="descripcion">Descripción</label>
                        <input type="text" name="descripcion" id="descripcion" placeholder="Descripción (opcional)">
                    </div>
                    <div class="col-12 d-flex justify-content-between mt-3">
                        <button type="submit" class="btn_save_1"><i class="far fa-save"></i> Guardar categoría</button>
                        <a href="lista_categorias.php" class="link_delete_1" style="float: right;"><i class="fas fa-minus-circle"></i> Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <?php include "../includes/footer_2.php"; ?>
</body>
</html>
