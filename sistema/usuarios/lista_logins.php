<?php
session_start();
include "../../conexion.php";
if (empty($_SESSION['active']) || $_SESSION['rol'] != 1) {
    header('Location: ../../index.php');
    exit;
}

$queryEmpleados = mysqli_query($conexionDB, "SELECT IdEmpleado, Nombre, Usuario FROM empleados ORDER BY Nombre ASC");
$empleados = [];
while ($e = mysqli_fetch_assoc($queryEmpleados)) $empleados[] = $e;
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
    <div class="container-fluid" style="padding-top: 110px;">
        <div class="row mb-3">
            <div class="col-12">
                <div class="card shadow-sm bg-white">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Auditoría de Accesos al Sistema</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-danger btn-sm" onclick="exportarLoginsPDF()">
                                <i class="far fa-file-pdf me-1"></i>PDF
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="exportarLoginsEXCEL()">
                                <i class="far fa-file-excel me-1"></i>Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label for="busquedaLogin" class="form-label small mb-1">Buscar (IP/Usuario/Motivo)</label>
                                <input type="text" id="busquedaLogin" class="form-control form-control-sm" placeholder="Buscar..." oninput="buscarLogin(1)">
                            </div>
                            <div class="col-md-2">
                                <label for="filtroEmpleado" class="form-label small mb-1">Usuario</label>
                                <select id="filtroEmpleado" class="form-select form-select-sm" onchange="buscarLogin(1)">
                                    <option value="">Todos</option>
                                    <?php foreach ($empleados as $e): ?>
                                        <option value="<?php echo $e['IdEmpleado']; ?>"><?php echo $e['Nombre'] . ' (' . $e['Usuario'] . ')'; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filtroExito" class="form-label small mb-1">Resultado</label>
                                <select id="filtroExito" class="form-select form-select-sm" onchange="buscarLogin(1)">
                                    <option value="">Todos</option>
                                    <option value="1">Éxito</option>
                                    <option value="0">Fallo</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filtroDispositivo" class="form-label small mb-1">Dispositivo</label>
                                <select id="filtroDispositivo" class="form-select form-select-sm" onchange="buscarLogin(1)">
                                    <option value="">Todos</option>
                                    <option value="Escritorio">Escritorio</option>
                                    <option value="Móvil">Móvil</option>
                                    <option value="Tablet">Tablet</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="filtroFechaDesde" class="form-label small mb-1">Fecha desde</label>
                                <input type="date" id="filtroFechaDesde" class="form-control form-control-sm" onchange="buscarLogin(1)">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small mb-1">&nbsp;</label>
                                <button class="btn btn-outline-secondary btn-sm w-100" onclick="limpiarFiltrosLogins()"><i class="fas fa-redo"></i></button>
                            </div>
                        </div>

                        <div id="alertaSinResultadosLogin" class="alert alert-warning py-2 d-none mb-2">
                            No se encontraron registros con los filtros actuales.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-sm align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha y Hora</th>
                                        <th>Usuario</th>
                                        <th>IP</th>
                                        <th>Dispositivo</th>
                                        <th>Resultado</th>
                                        <th>Motivo / Detalle</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaLoginsBody">
                                </tbody>
                            </table>
                        </div>

                        <div id="paginadorLogin" class="d-flex justify-content-center mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include "../includes/footer_2.php"; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() { buscarLogin(1); });
    </script>
</body>
</html>
