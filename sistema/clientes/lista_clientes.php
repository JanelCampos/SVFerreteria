<?php
include "../../conexion.php";
session_start();
require_once __DIR__ . "/../includes/analytics.php";
include "../includes/paginador.php";

$filters = analyticsGetDateFilters($_GET);
$results_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$busquedaCliente = isset($_GET['busquedaCliente']) ? trim((string)$_GET['busquedaCliente']) : '';
$filtrosVarios = isset($_GET['filtrosVarios']) ? trim((string)$_GET['filtrosVarios']) : '';
$yearOptions = analyticsGetYearOptions();

$clientData = analyticsGetClientsListData(
    $conexionDB,
    $filters,
    $busquedaCliente,
    $filtrosVarios,
    $current_page,
    $results_per_page
);

$rows = $clientData['rows'];
$total_records = $clientData['total_records'];
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
    <div id="rol" data-rol="<?= $_SESSION['rol']; ?>"></div>
    <section id="container">
        <div class="title_container">
            <h1>Listado de clientes</h1>
            <a href="registro_cliente.php" class="btn_new"><i class="fas fa-user-plus"></i> Crear cliente</a>
        </div>

        <div class="header_container">
            <div class="row g-3 align-items-end">   
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="busquedaCliente">Nombre / DNI</label>
                    <input class="filtrosBusqueda" type="text" name="busquedaCliente" id="busquedaCliente" placeholder="Nombre / DNI" value="<?php echo htmlspecialchars($busquedaCliente); ?>">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="filtrosVarios">Ranking</label>
                    <select class="filtrosBusqueda" name="filtrosVarios" id="filtrosVarios">
                        <option value="">Orden reciente</option>
                        <option value="cantidadCompra" <?php echo $filtrosVarios === 'cantidadCompra' ? 'selected' : ''; ?>>Cliente con mas compras</option>
                        <option value="mayorCompra" <?php echo $filtrosVarios === 'mayorCompra' ? 'selected' : ''; ?>>Cliente con mayor monto</option>
                        <option value="mayorUtilidad" <?php echo $filtrosVarios === 'mayorUtilidad' ? 'selected' : ''; ?>>Cliente con mayor utilidad</option>
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
                        <button type="button" class="btn btn-primary" onclick="buscarCliente()">Aplicar filtros</button>
                        <a class="btn btn-outline-secondary" href="lista_clientes.php">Restablecer</a>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-4 ms-lg-auto">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <button class="btn btn-danger" type="button" onclick="exportarClientesPDF()">Exportar PDF</button>
                        <button class="btn btn-success" type="button" onclick="exportarClientesEXCEL()">Exportar Excel</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="containerTable">
            <table id="tablaClientes">
                <thead>
                    <tr>
                        <th>DNI</th>
                        <th>Nombre y apellidos</th>
                        <th>Direccion</th>
                        <th>Telefono</th>
                        <th>Fecha de registro</th>
                        <th>C. compras</th>
                        <?php if($_SESSION['rol'] == 1){ ?>
                            <th>M. compras</th>
                            <th>Ganancias</th>
                            <th>Acciones</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows) { ?>
                        <?php foreach ($rows as $data) { ?>
                            <tr>
                                <td><a href="../operaciones/venta_articulo.php?dni=<?php echo $data['Dni']; ?>"><?php echo $data['Dni']; ?></a></td>
                                <td><?php echo htmlspecialchars($data['Nombre']); ?></td>
                                <td><?php echo htmlspecialchars($data['direccion']); ?></td>
                                <td><?php echo htmlspecialchars((string)$data['Telefono']); ?></td>
                                <td><?php echo htmlspecialchars($data['Fecha_Registro']); ?></td>
                                <td><?php echo (int)$data['cantidadCompras']; ?></td>
                                <?php if($_SESSION['rol'] == 1){ ?>
                                    <td>S/. <?php echo number_format((float)$data['montoCompras'], 2); ?></td>
                                    <td>S/. <?php echo number_format((float)$data['gananciaGenerada'], 2); ?></td>
                                    <td>
                                        <div class="btn-group dropend">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Acciones
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><button class="dropdown-item" onclick="mostrarFormulario('editarCliente', <?php echo $data['Id_Cliente']; ?>)">Editar</button></button></li>
                                                <li><button class="dropdown-item" onclick="mostrarFormulario('reiniciarMetricas', <?php echo $data['Id_Cliente']; ?>)">Reiniciar metricas</button></li>
                                                <li><button class="dropdown-item" onclick="mostrarFormulario('eliminarCliente', <?php echo $data['Id_Cliente']; ?>)">Eliminar</button></li>
                                            </ul>
                                        </div>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr><td colspan="9">No se encontraron clientes para el periodo seleccionado.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div id="reiniciarMetricas" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Reiniciar metricas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idClienteMetricas" name="idClienteMetricas">
                        <div class="mb-3">
                            <p>¿Esta seguro de reiniciar las metricas historicas del cliente?</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="button" onclick="reiniciarMetricas()">Confirmar</button>
                        <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('reiniciarMetricas')">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="eliminarCliente" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Eliminar cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idClienteEliminar" name="idClienteEliminar">
                        <div class="mb-3">
                            <p class="bg-danger text-white p-2">¿Esta seguro de eliminar el cliente? esta accion no se puede revertir.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="button" onclick="eliminarCliente()">Confirmar</button>
                        <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('eliminarCliente')">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="editarCliente" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Editar cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idClienteEditar" name="idClienteEditar">
                        <div class="mb-3">
                            <label for="nombreClienteEditar">Nombre y apellidos</label>
                            <input type="text" name="nombreClienteEditar" id="nombreClienteEditar" placeholder="Ingrese nombre completo">
                        </div>
                        <div class="mb-3">
                            <label for="dniClienteEditar">Dni</label>
                            <input type="number" name="dniClienteEditar" id="dniClienteEditar" placeholder="Ingrese el DNI" oninput="limitarDigitos(this,8)">
                        </div>
                        <div class="mb-3">
                            <label for="telefonoClienteEditar">Telefono</label>
                            <input type="number" name="telefonoClienteEditar" id="telefonoClienteEditar" placeholder="Ingrese un telefono" oninput="limitarDigitos(this,9)">
                        </div>
                        <div class="mb-3">
                            <label for="direccionClienteEditar">Direccion</label>
                            <input type="text" name="direccionClienteEditar" id="direccionClienteEditar" placeholder="Ingrese la direccion">
                        </div>
                        <div class="mb-3">
                            <label for="fecha_registroClienteEditar">Fecha de registro</label>
                            <input type="date" name="fecha_registroClienteEditar" id="fecha_registroClienteEditar">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="button" onclick="editarCliente()">Confirmar</button>
                        <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('editarCliente')">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <?php renderPaginator($total_records, $results_per_page, $current_page, 'lista_clientes.php'); ?>
        <div id="paginator"></div>
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
