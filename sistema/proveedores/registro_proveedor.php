<?php
    require_once "../../conexion.php";
    require_once __DIR__ . "/validaciones_proveedor.php";
    session_start();

    $valores = [
        'ruc' => '',
        'nombre' => '',
        'direccion' => '',
        'telefono' => '',
        'correo' => ''
    ];
    $alert = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($valores as $campo => $valor) {
            if (isset($_POST[$campo]) && is_string($_POST[$campo])) {
                $valores[$campo] = trim($_POST[$campo]);
            }
        }

        if ($valores['ruc'] === '' || $valores['nombre'] === '') {
            $alert = '<p class="alert alert-danger">El RUC y el nombre son obligatorios.</p>';
        } elseif (strlen($valores['ruc']) > 20 || strlen($valores['nombre']) > 50) {
            $alert = '<p class="alert alert-danger">El RUC admite hasta 20 caracteres y el nombre hasta 50.</p>';
        } elseif (strlen($valores['direccion']) > 50 || strlen($valores['telefono']) > 20 || strlen($valores['correo']) > 50) {
            $alert = '<p class="alert alert-danger">Verifique la longitud de los campos opcionales.</p>';
        } elseif ($valores['correo'] !== '' && !filter_var($valores['correo'], FILTER_VALIDATE_EMAIL)) {
            $alert = '<p class="alert alert-danger">Ingrese un correo electrónico válido.</p>';
        } else {
            $bloqueoRuc = null;
            $registrado = false;
            try {
                $bloqueoRuc = bloquearRucProveedor($conexionDB, $valores['ruc']);
                if (existeProveedorConRuc($conexionDB, $valores['ruc'])) {
                    $alert = '<p class="alert alert-danger">Ya existe un proveedor con ese RUC.</p>';
                } else {
                    $direccion = $valores['direccion'] !== '' ? $valores['direccion'] : null;
                    $telefono = $valores['telefono'] !== '' ? $valores['telefono'] : null;
                    $correo = $valores['correo'] !== '' ? $valores['correo'] : null;
                    $query_insert = $conexionDB->prepare(
                        "INSERT INTO proveedores (ruc, Nombre, Direccion, Telefono, Email) VALUES (?, ?, ?, ?, ?)"
                    );
                    $query_insert->bind_param("sssss", $valores['ruc'], $valores['nombre'], $direccion, $telefono, $correo);
                    $query_insert->execute();
                    $query_insert->close();
                    $registrado = true;
                }
            } catch (mysqli_sql_exception $exception) {
                error_log("Error al registrar proveedor: " . $exception->getMessage());
                $alert = '<p class="alert alert-danger">No se pudo guardar el proveedor. Intente nuevamente.</p>';
            } catch (RuntimeException $exception) {
                error_log("Error al bloquear RUC de proveedor: " . $exception->getMessage());
                $alert = '<p class="alert alert-danger">No se pudo verificar el RUC. Intente nuevamente.</p>';
            } finally {
                if ($bloqueoRuc !== null) {
                    try {
                        desbloquearRucProveedor($conexionDB, $bloqueoRuc);
                    } catch (mysqli_sql_exception $exception) {
                        error_log("Error al liberar RUC de proveedor: " . $exception->getMessage());
                    }
                }
            }
            if ($registrado) {
                header("Location: lista_proveedores.php");
                exit;
            }
        }
    }

    function escaparProveedor($valor) {
        return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
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
            <div><?php echo $alert; ?></div>

            <form action="" method="post">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="ruc">RUC</label>
                        <input type="text" name="ruc" id="ruc" placeholder="Ingrese el RUC" maxlength="20" required value="<?php echo escaparProveedor($valores['ruc']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" placeholder="Ingrese el nombre" maxlength="50" required value="<?php echo escaparProveedor($valores['nombre']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="direccion">Dirección</label>
                        <input type="text" name="direccion" id="direccion" placeholder="Ingrese una dirección" maxlength="50" value="<?php echo escaparProveedor($valores['direccion']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="telefono">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" placeholder="Ingrese un teléfono" maxlength="20" value="<?php echo escaparProveedor($valores['telefono']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="correo">Email</label>
                        <input type="email" name="correo" id="correo" placeholder="Ingrese un correo electrónico" maxlength="50" value="<?php echo escaparProveedor($valores['correo']); ?>">
                    </div>
                    <div class="col-12 d-flex justify-content-between mt-3">
                        <button type="submit" class="btn_save_1"><i class="far fa-save"></i> Guardar Proveedor</button>
                        <a href="lista_proveedores.php" class="link_delete_1"><i class="fas fa-minus-circle"></i> Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <?php include "../includes/footer_2.php"; ?>
</body>
</html>
