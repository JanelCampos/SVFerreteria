<?php
    include "../../conexion.php";
    session_start();
    include "../includes/paginador.php";
    require_once __DIR__ . "/../includes/analytics.php";
    $yearOptions = analyticsGetYearOptions();
    $filters = analyticsGetDateFilters($_GET);

    $results_per_page = 10;
    $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $consulta = "
        SELECT *
        FROM gastos 
        ORDER BY idGastos DESC
    ";
    $where = "
        gastos 
        ORDER BY idGastos DESC
    ";
    list($result, $total_records) = getPaginatedDataGastos($conexionDB, $consulta, $where, $current_page, $results_per_page);

    $row = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<?php include "../includes/scripts_2.php"; ?>
    <?php include "../includes/title.php"; ?>
    <style>
        #tarjeta_container{
            display: none;
        }

        #vuelto_pendiente_container{
            display: none;
        }

        #saldo_container{
            display: none;
        }
    </style>
</head>
<body>
    
    <?php include "../includes/header_2.php"; ?>
    <div id="rol" data-rol="<?= $_SESSION['rol']; ?>"></div>
	<section id="container">
        <div class="bg-white p-3 rounded shadow mb-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="mb-0">
                    <h1>Listado de gastos</h1>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="../operaciones/gastos.php" class="btn_new"><i class="fas fa-plus"></i> Registrar Gasto</a>
                    <a href="gastosDelDia.php" class="btn_new"><i class="fas fa-plus"></i> Gastos del día</a>
                    <a href="gastosDelMes.php" class="btn_new"><i class="fas fa-plus"></i> Gastos del mes</a>
                </div>
            </div>
        </div>
        <div class="header_container">
            <div class="row g-3 align-items-end">   
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="busquedaGasto">Descripción</label>
                    <input class="filtrosBusqueda" type="text" name="descripcion" id="busquedaGasto" placeholder="Palabra clave">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="busquedaMedioPago">Medio de pago</label>
                    <select class="filtrosBusqueda" name="busquedaMedioPago" id="busquedaMedioPago">
                        <option value="">Medio de gasto</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="tipoGasto">Tipo de gasto</label>
                    <select class="filtrosBusqueda" name="tipoGasto" id="tipoGasto">
                        <option value="">Tipo de gasto</option>
                        <option value="capital">Capital</option>
                        <option value="personal">Personal</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="period">Periodo</label>
                    <select class="filtrosBusqueda" name="period" id="period">
                        <option value="year" <?php echo $filters['period'] === 'year' ? 'selected' : ''; ?>>Anual</option>
                        <option value="month" <?php echo $filters['period'] === 'month' ? 'selected' : ''; ?>>Mensual</option>
                        <option value="custom" <?php echo $filters['period'] === 'custom' ? 'selected' : ''; ?>>Rango personalizado</option>
                    </select>
                </div>
                <div class="analytics-period-field col-12 col-md-4 col-lg-3" data-period-target="year">
                    <label for="year">Anio</label>
                    <select class="filtrosBusqueda" name="year" id="year">
                        <?php foreach ($yearOptions as $yearOption) { ?>
                            <option value="<?php echo $yearOption; ?>" <?php echo (int)$filters['year'] === (int)$yearOption ? 'selected' : ''; ?>><?php echo $yearOption; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="analytics-period-field col-12 col-md-4 col-lg-3" data-period-target="month">
                    <label for="month">Mes</label>
                    <input class="filtrosBusqueda" type="month" name="month" id="month" value="<?php echo htmlspecialchars($filters['month']); ?>">
                </div>
                <div class="analytics-period-field col-12 col-md-4 col-lg-3" data-period-target="custom">
                    <label for="start_date">Fecha inicio</label>
                    <input class="filtrosBusqueda" type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($filters['start_date']); ?>">
                </div>
                <div class="analytics-period-field col-12 col-md-4 col-lg-3" data-period-target="custom">
                    <label for="end_date">Fecha fin</label>
                    <input class="filtrosBusqueda" type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($filters['end_date']); ?>">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" onclick="buscarGasto()">Aplicar filtros</button>
                        <a href="gastos.php" class="btn btn-outline-secondary">Restablecer</a>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-4 ms-lg-auto">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <button class="btn btn-danger" type="button" onclick="exportarGastosPDF()">Exportar a PDF</button>
                        <button class="btn btn-success"  type="button" onclick="exportarGastosEXCEL()">Exportar a Excel</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="containerTable">
            <table id="tablaGastos">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Descripcion</th>
                        <th>monto</th>
                        <th>Fecha</th>
                        <th>Medio de pago</th>
                        <th>Tipo de gasto</th>
                        <th>Acciones</th>                
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if($row > 0){
                            while ($data = $result->fetch_assoc()){
                            ?>
                                <tr id="row_<?php echo $data["idGastos"]; ?>">
                                    <td><?php echo $data['idGastos']; ?></td>
                                    <td><?php echo $data['descripcion']; ?></td>
                                    <td>S/. <?php echo $data["montoGasto"]; ?></td>
                                    <td><?php echo date("Y-m-d",  strtotime($data["fechaGasto"])); ?></td>
                                    <td><?php echo $data['medioPago']; ?></td>
                                    <td><?php echo $data['tipoGasto']; ?></td>
                                    <td>
                                        <div class="btn-group dropend">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Acciones
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('anularGasto', <?php echo $data['idGastos']; ?>)">Anular gasto</button></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                    <?php
                            }
                        }
                    ?>
                </tbody>
            </table>
        </div>
        
        <?php 
            renderPaginator($total_records, $results_per_page, $current_page, 'gastos.php');
        ?>
        <div id="paginator"></div>

        <div id="anularGasto" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Anular gasto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idGastoAnular" name="idGastoAnular">
                        <div class="mb-3">
                            <p class="bg-danger text-white p-2">¿Está seguro de anular el gasto? Esta operación no se puede revertir.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="button" onclick="anularGasto()">Confirmar</button>
                        <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('anularGasto')">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

	</section>
	<?php include "../includes/footer_2.php"; ?>

    <script>
        (function toggleClientPeriodFields() {
            const periodControl = document.getElementById('period');
            const fields = document.querySelectorAll('.analytics-period-field');

            function refresh() {
                const selected = periodControl.value;
                fields.forEach((field) => {
                    const target = field.getAttribute('data-period-target');
                    const visible =
                        (selected === 'year' && target === 'year') ||
                        (selected === 'month' && target === 'month') ||
                        (selected === 'custom' && target === 'custom');

                    field.style.display = visible ? '' : 'none';
                });
            }

            refresh();
            periodControl.addEventListener('change', refresh);
        })();
    </script>
</body>
</html>