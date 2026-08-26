<div class="collapse navbar-collapse" id="mainNavbar">
    <ul class="navbar-nav me-auto mb-2 mb-xl-0">
        <li class="nav-item">
            <a class="nav-link" href="index.php">Inicio</a>
        </li>

        <?php if ($_SESSION['rol'] == 1) { ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Usuarios</a>
                <ul class="dropdown-menu dropdown-menu-dark shadow border-0">
                    <li><a class="dropdown-item" href="usuarios/registro_usuario.php">Nuevo usuario</a></li>
                    <li><a class="dropdown-item" href="usuarios/lista_usuarios.php">Lista de usuarios</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="usuarios/lista_logins.php">Auditoría de accesos</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="alertas/lista_alertas.php"><i class="fas fa-bell me-2"></i>Alertas del sistema</a></li>
                </ul>
            </li>
        <?php } ?>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Clientes</a>
            <ul class="dropdown-menu dropdown-menu-dark shadow border-0">
                <li><a class="dropdown-item" href="clientes/registro_cliente.php">Nuevo cliente</a></li>
                <li><a class="dropdown-item" href="clientes/lista_clientes.php">Lista de clientes</a></li>
            </ul>
        </li>

        <?php if ($_SESSION['rol'] == 1) { ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Proveedores</a>
                <ul class="dropdown-menu dropdown-menu-dark shadow border-0">
                    <li><a class="dropdown-item" href="proveedores/registro_proveedor.php">Nuevo proveedor</a></li>
                    <li><a class="dropdown-item" href="proveedores/lista_proveedores.php">Lista de proveedores</a></li>
                </ul>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Prestamos</a>
                <ul class="dropdown-menu dropdown-menu-dark shadow border-0">
                    <li><a class="dropdown-item" href="prestamos/registrar_prestamo.php">Nuevo prestamo</a></li>
                    <li><a class="dropdown-item" href="prestamos/lista_prestamos.php">Lista de prestamos</a></li>
                </ul>
            </li>
        <?php } ?>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Articulos</a>
            <ul class="dropdown-menu dropdown-menu-dark shadow border-0">
                <?php if ($_SESSION['rol'] == 1) { ?>
                    <li><a class="dropdown-item" href="articulos/registro_articulo.php">Nuevo articulo</a></li>
                <?php } ?>
                <li><a class="dropdown-item" href="articulos/lista_articulos.php">Lista de articulos</a></li>
                <li><a class="dropdown-item" href="categorias/lista_categorias.php">Categorias</a></li>
            </ul>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Operaciones</a>
            <ul class="dropdown-menu dropdown-menu-dark shadow border-0">
                <li><a class="dropdown-item" href="operaciones/venta_articulo.php">Venta articulo</a></li>
                <?php if($_SESSION['rol'] == 1){ ?>
                    <li><a class="dropdown-item" href="operaciones/ventaLibre.php">Ingresos</a></li>
                <?php } ?>
                <li><a class="dropdown-item" href="operaciones/gastos.php">Gastos</a></li>
                <li><a class="dropdown-item" href="operaciones/conversionDinero.php">Conversion de dinero</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="cotizaciones/nueva_cotizacion.php">Nueva cotizacion</a></li>
                <li><a class="dropdown-item" href="cotizaciones/lista_cotizaciones.php">Lista de cotizaciones</a></li>
            </ul>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Reportes</a>
            <ul class="dropdown-menu dropdown-menu-dark shadow border-0">
                <?php if ($_SESSION['rol'] == 1) { ?>
                    <li><a class="dropdown-item" href="reportes/ventas.php">Listado de ventas</a></li>
                    <li><a class="dropdown-item" href="caja/lista_caja.php">Historial de caja</a></li>
                <?php } ?>
                <li><a class="dropdown-item" href="caja/actividad_caja_diaria.php">Actividad diaria</a></li>
                <?php if ($_SESSION['rol'] == 1) { ?>
                    <li><a class="dropdown-item" href="reportes/reportes.php">Analitica</a></li>
                    <li><a class="dropdown-item" href="reportes/gastos.php">Gastos</a></li>
                    <li><a class="dropdown-item" href="reportes/estadisticas.php">Productos</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="reportes/ventas_por_vendedor.php">Ventas por vendedor</a></li>
                    <!-- <li><a class="dropdown-item" href="reportes/capital_rentabilidad.php">Capital y rentabilidad</a></li> -->
                <?php } ?>
            </ul>
        </li>
    </ul>

    <div class="d-flex d-xl-none flex-column gap-2 border-top border-secondary pt-3 mt-3">
        <div class="text-white small">
            <div class="fw-semibold"><?php echo $_SESSION['user']; ?></div>
            <div class="text-white-50"><?php echo $_SESSION['rol_name']; ?></div>
        </div>
        <a href="salir.php" class="btn btn-outline-light btn-sm align-self-start">Salir</a>
    </div>
</div>
