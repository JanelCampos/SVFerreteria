<?php
if (empty($_SESSION['active'])) {
    header('location: ../');
}
?>
<header class="app-header">
    <nav class="navbar navbar-expand-xl navbar-dark bg-dark fixed-top shadow-sm app-navbar">
        <div class="container-fluid px-3 px-lg-4">
            <a href="index.php" class="navbar-brand d-flex align-items-center gap-3">
                <img class="app-logo" src="../img/logo_ferreteria.png" alt="Ferreteria USOL">
                <span class="d-flex flex-column lh-sm">
                    <span class="fw-semibold">Ferreteria USOL</span>
                    <small class="text-white-50">Panel de gestion</small>
                </span>
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Alternar navegación">
                <span class="navbar-toggler-icon"></span>
            </button>

            <?php include "nav.php"; ?>

            <div class="d-none d-xl-flex align-items-center gap-2 ms-3 text-white small">
                <span class="badge rounded-pill text-bg-light text-dark px-3 py-2"><?php echo $_SESSION['rol_name']; ?></span>
                <span class="text-white-50"><?php echo fechaC(); ?></span>
                <span class="fw-semibold"><?php echo $_SESSION['user']; ?></span>
                <a href="salir.php" class="btn btn-outline-light btn-sm">Salir</a>
            </div>
        </div>
    </nav>
</header>
