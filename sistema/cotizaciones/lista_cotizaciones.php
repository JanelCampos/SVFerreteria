<?php
session_start();
require '../../conexion.php';

$queryEmpleados = mysqli_query($conexionDB, "SELECT IdEmpleado, Nombre FROM empleados ORDER BY Nombre ASC");
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
    <div class="container-fluid" style="padding-top:110px;">
        <div class="row mb-3">
            <div class="col-12">
                <div class="card shadow bg-white">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Lista de cotizaciones</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-danger btn-sm" onclick="exportarCotizacionesPDF()">
                                <i class="far fa-file-pdf me-1"></i>PDF
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="exportarCotizacionesEXCEL()">
                                <i class="far fa-file-excel me-1"></i>Excel
                            </button>
                            <a class="btn btn-primary btn-sm" href="nueva_cotizacion.php">
                                <i class="fas fa-plus me-1"></i>Nueva cotización
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label for="busquedaCot" class="form-label small mb-1">N° / cliente / obs</label>
                                <input type="text" id="busquedaCot" class="form-control form-control-sm" placeholder="N° / cliente / obs">
                            </div>
                            <div class="col-md-2">
                                <label for="IdEmpleado" class="form-label small mb-1">Vendedor</label>
                                <select id="IdEmpleado" class="form-select form-select-sm" onchange="buscarCotizacion()">
                                    <option value="">Todos</option>
                                    <?php foreach ($empleados as $e): ?>
                                        <option value="<?php echo $e['IdEmpleado']; ?>"><?php echo htmlspecialchars($e['Nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="fechaDesde" class="form-label small mb-1">Fecha desde</label>
                                <input type="date" id="fechaDesde" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label for="fechaHasta" class="form-label small mb-1">Fecha hasta</label>
                                <input type="date" id="fechaHasta" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-1 d-flex gap-1 align-items-end">
                                <button class="btn btn-outline-secondary btn-sm flex-grow-1" onclick="limpiarFiltrosCotizaciones()"><i class="fas fa-redo"></i></button>
                                <button class="btn btn-primary btn-sm flex-grow-1" onclick="buscarCotizacion()"><i class="fas fa-search"></i></button>
                            </div>
                        </div>

                        <div id="alertaSinResultadosCot" class="alert alert-info py-2 d-none mb-2">
                            No se encontraron cotizaciones con los filtros actuales.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-sm align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>N°</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Vendedor</th>
                                        <th>Total S/.</th>
                                        <th>Vigencia</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaCotizacionesBody">
                                </tbody>
                            </table>
                        </div>

                        <div id="paginadorCotizacion" class="d-flex justify-content-center mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include "../includes/footer_2.php"; ?>

    <div id="eliminarCotizacionModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="eliminarCotizacionModalLabel" class="modal-title">Eliminar cotización</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="IdCotizacionEliminar" name="IdCotizacionEliminar" value="">
                    <div class="mb-3">
                        <p class="bg-danger text-white p-2">¿Está seguro de eliminar la cotización? Esta operación no se puede revertir.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="eliminarCotizacion()">Eliminar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        window._esCotizacion = true;
        var rolUsuario = <?php echo isset($_SESSION['rol']) ? intval($_SESSION['rol']) : 0; ?>;

        $('#busquedaCot').keydown(function(event) {
            if (event.keyCode === 13) { // 13 es el código de tecla para Enter
                buscarCotizacion();
            }
        });

        function buscarCotizacion(page) {
            if (!document.getElementById('tablaCotizacionesBody')) return;
            page = page || 1;
            var busqueda = document.getElementById('busquedaCot').value;
            var IdEmpleado = document.getElementById('IdEmpleado').value;
            var FechaDesde = document.getElementById('fechaDesde').value;
            var FechaHasta = document.getElementById('fechaHasta').value;

            fetch('buscar_cotizaciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    busqueda: busqueda, IdEmpleado: IdEmpleado,
                    FechaDesde: FechaDesde, FechaHasta: FechaHasta, page: page
                })
            })
            .then(r => r.json())
            .then(resp => {
                var tbody = document.getElementById('tablaCotizacionesBody');
                var alerta = document.getElementById('alertaSinResultadosCot');
                var pagHtml = '';
                if (!resp.resultado) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error al cargar datos</td></tr>';
                    return;
                }
                if (resp.datos.length === 0) {
                    tbody.innerHTML = '';
                    alerta.classList.remove('d-none');
                } else {
                    alerta.classList.add('d-none');
                    var rows = '';
                    resp.datos.forEach(function(rr) {
                        var nomCli = rr.NombreCliente ? rr.NombreCliente : '—';
                        var dniCli = rr.DniCliente ? rr.DniCliente : '';
                        var nomEmp = rr.NombreEmpleado ? rr.NombreEmpleado : '—';
                        var badgeClass = 'text-bg-secondary';
                        var total = Number(rr.Total || 0).toFixed(2);
                        var vigencia = rr.VigenciaHasta ? rr.VigenciaHasta : '—';
                        var fechaCot = rr.Fecha ? rr.Fecha.substring(0,10) : '—';

                        var acciones = '';
                        acciones += '<a class="btn btn-outline-primary btn-sm me-1" href="imprimir_cotizacion.php?idCotizacion=' + rr.IdCotizacion + '&nPdf=1" target="_blank" title="Ver PDF"><i class="far fa-file-pdf"></i></a>';
                        acciones += '<a class="btn btn-outline-success btn-sm me-1" href="imprimir_cotizacion.php?idCotizacion=' + rr.IdCotizacion + '&nExcel=1" target="_blank" title="Ver Excel"><i class="far fa-file-excel"></i></a>';
                        acciones += '<a class="btn btn-outline-info btn-sm me-1" href="imprimir_cotizacion.php?idCotizacion=' + rr.IdCotizacion + '&nTicket=1" target="_blank" title="Ver Ticket"><i class="fas fa-tag"></i></a>';
                        if (new Date(vigencia) < new Date()) {
                            acciones += '<a class="btn btn-outline-warning btn-sm me-1" onclick="mostrarFormulario(\'eliminarCotizacionModal\', ' + rr.IdCotizacion + ')" title="Eliminar"><i class="fas fa-trash"></i></a>';
                        }

                        rows += '<tr>'
                            + '<td><strong>#' + rr.IdCotizacion + '</strong></td>'
                            + '<td>' + fechaCot + '</td>'
                            + '<td title="' + dniCli + '">' + nomCli + (dniCli ? '<br><small class="text-muted">DNI: ' + dniCli + '</small>' : '') + '</td>'
                            + '<td>' + nomEmp + '</td>'
                            + '<td class="text-end">S/. ' + total + '</td>'
                            + '<td>' + vigencia + '</td>'
                            + '<td>' + acciones + '</td>'
                            + '</tr>';
                    });
                    tbody.innerHTML = rows;
                }
                pagHtml = renderPaginadorCot(resp.paginaActual, resp.totalPaginas, resp.total);
                document.getElementById('paginadorCotizacion').innerHTML = pagHtml;
            });
        }

        function renderPaginadorCot(pagActual, totalPag, total) {
            if (totalPag <= 1) return '<div class="small text-muted">Total de registros: ' + total + '</div>';
            var html = '<nav aria-label="Paginacion cotizaciones"><ul class="pagination pagination-sm mb-0 me-2">';
            html += '<li class="page-item ' + (pagActual <= 1 ? 'disabled' : '') + '"><button class="page-link" onclick="buscarCotizacion(' + (pagActual - 1) + ')">«</button></li>';
            var maxBotones = 5;
            var ini = Math.max(1, pagActual - 2);
            var fin = Math.min(totalPag, ini + maxBotones - 1);
            ini = Math.max(1, fin - maxBotones + 1);
            for (var p = ini; p <= fin; p++) {
                html += '<li class="page-item ' + (p === pagActual ? 'active' : '') + '"><button class="page-link" onclick="buscarCotizacion(' + p + ')">' + p + '</button></li>';
            }
            html += '<li class="page-item ' + (pagActual >= totalPag ? 'disabled' : '') + '"><button class="page-link" onclick="buscarCotizacion(' + (pagActual + 1) + ')">»</button></li>';
            html += '</ul><div class="small text-muted align-self-center">Total: ' + total + ' registro(s)</div></nav>';
            return html;
        }

        function limpiarFiltrosCotizaciones() {
            document.getElementById('busquedaCot').value = '';
            document.getElementById('IdEmpleado').value = '';
            document.getElementById('fechaDesde').value = '';
            document.getElementById('fechaHasta').value = '';
            buscarCotizacion(1);
        }

        function obtenerFiltrosCotizaciones() {
            return {
                busqueda: document.getElementById('busquedaCot').value,
                IdEmpleado: document.getElementById('IdEmpleado').value,
                FechaDesde: document.getElementById('fechaDesde').value,
                FechaHasta: document.getElementById('fechaHasta').value
            };
        }

        function exportarCotizacionesPDF() {
            if (!document.getElementById('busquedaCot')) return;
            var f = obtenerFiltrosCotizaciones();
            var url = 'imprimir_lista_cotizaciones.php?nPdf=1'
                + '&busqueda=' + encodeURIComponent(f.busqueda)
                + '&IdEmpleado=' + encodeURIComponent(f.IdEmpleado)
                + '&FechaDesde=' + encodeURIComponent(f.FechaDesde)
                + '&FechaHasta=' + encodeURIComponent(f.FechaHasta);
            window.open(url, '_blank');
        }

        function exportarCotizacionesEXCEL() {
            if (!document.getElementById('busquedaCot')) return;
            var f = obtenerFiltrosCotizaciones();
            var url = 'imprimir_lista_cotizaciones.php?nExcel=1'
                + '&busqueda=' + encodeURIComponent(f.busqueda)
                + '&IdEmpleado=' + encodeURIComponent(f.IdEmpleado)
                + '&FechaDesde=' + encodeURIComponent(f.FechaDesde)
                + '&FechaHasta=' + encodeURIComponent(f.FechaHasta);
            window.open(url, '_blank');
        }

        function eliminarCotizacion() {
            var id = document.getElementById('IdCotizacionEliminar').value;
            fetch('eliminar_cotizacion.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ IdCotizacion: id })
            })
            .then(r => r.json())
            .then(resp => {
                if (resp.resultado) {
                    buscarCotizacion();
                    ocultarFormulario('eliminarCotizacionModal');
                    mostrarAlertaExitoTiempo(resp.mensaje);
                } else {
                    alert(resp.mensaje || 'No se pudo anular la cotización.');
                }
            })
            .catch(() => alert('Error de conexión al anular.'));
        }

        document.addEventListener('DOMContentLoaded', function() { buscarCotizacion(1); });
    </script>
</body>
</html>
