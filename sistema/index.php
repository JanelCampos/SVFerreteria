<?php
session_start();
include "../conexion.php";
include "funciones.php";
require_once __DIR__ . "/includes/analytics.php";

$totalCaja = 0;
$utilidadTotal = 0;
$totalEfectivo = 0;
$totalTarjeta = 0;

$filters = analyticsGetDateFilters([
    'period' => 'year',
    'year' => date('Y'),
    'top_limit' => 10,
    'top_metric' => 'monto',
]);
$dashboard = analyticsGetReportDashboardData($conexionDB, $filters);
$overview = $dashboard['overview'];
$topClients = array_slice($dashboard['top_clients'], 0, 5);
$topProducts = array_slice($dashboard['top_products'], 0, 5);
$user = (int)$_SESSION['idUser'];

include "includes/total_caja.php";
$estadoCajaRow = analyticsFetchOne(
    $conexionDB,
    "SELECT Estado FROM caja WHERE IdCaja = (SELECT MAX(IdCaja) FROM caja)"
);
$estadoCaja = $estadoCajaRow['Estado'] ?? 'Cerrado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php include "includes/scripts.php"; ?>
    <?php include "includes/title_2.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include "includes/header.php"; ?>

    <section id="container" class="analytics-page">
        <div class="analytics-hero">
            <div>
                <span class="analytics-eyebrow">Resumen ejecutivo</span>
                <h1>Analitica principal del sistema</h1>
                <p>Al ingresar puedes ver ventas, utilidad, clientes activos, cartera pendiente y el comportamiento comercial del anio actual sin navegar entre varios modulos.</p>
            </div>
            <div class="analytics-actions">
                <?php if($_SESSION['idUser'] == 1){ ?>
                    <a href="reportes/reportes.php" class="btn btn-primary">Ver centro de reportes</a>
                    <a href="clientes/lista_clientes.php" class="btn btn-outline-primary">Analizar clientes</a>
                    <a href="reportes/estadisticas.php" class="btn btn-secondary">Productos clave</a>
                <?php } ?>
                <a href="reportes/ventasDelDia.php" class="btn btn-outline-secondary">ventas del dia</a>
            </div>
        </div>

        <?php if($_SESSION['idUser'] == 1){ ?>
            <div class="analytics-kpis">
                <article class="analytics-kpi">
                    <span>Ventas del periodo</span>
                    <strong>S/. <?php echo number_format($overview['total_sales'], 2); ?></strong>
                    <small><?php echo htmlspecialchars($filters['label']); ?></small>
                </article>
                <article class="analytics-kpi">
                    <span>Utilidad generada</span>
                    <strong>S/. <?php echo number_format($overview['total_profit'], 2); ?></strong>
                    <small>Margen registrado en ventas cerradas</small>
                </article>
                <article class="analytics-kpi">
                    <span>Venta promedio</span>
                    <strong>S/. <?php echo number_format($overview['average_ticket'], 2); ?></strong>
                    <small><?php echo (int)$overview['sales_count']; ?> ventas analizadas</small>
                </article>
                <article class="analytics-kpi">
                    <span>Clientes activos</span>
                    <strong><?php echo (int)$overview['active_clients']; ?></strong>
                    <small>Sin incluir Cliente General</small>
                </article>
                <article class="analytics-kpi">
                    <span>Cartera pendiente</span>
                    <strong>S/. <?php echo number_format($overview['pending_balance'], 2); ?></strong>
                    <small><?php echo (int)$overview['pending_sales']; ?> ventas con saldo o pendiente</small>
                </article>
                <article class="analytics-kpi">
                    <span>Productos agotados</span>
                    <strong><?php echo (int)$overview['sold_out_products']; ?></strong>
                    <small>Stock actual en cero</small>
                </article>
            </div>
        <?php } ?>

        <div class="analytics-grid">
            <?php if($_SESSION['idUser'] == 1){ ?>
                <article class="analytics-panel analytics-panel-lg">
                    <div class="analytics-panel-head">
                        <div>
                            <h2>Tendencia de ventas, ganancia bruta y rentabilidad</h2>
                            <p>Serie mensual del periodo actual para detectar aceleraciones y caidas.</p>
                        </div>
                        <span class="badge text-bg-light"><?php echo htmlspecialchars($filters['label']); ?></span>
                    </div>
                    <div style="height: 350px;">
                        <canvas id="indexTrendChart"></canvas>
                    </div>
                </article>
            <?php } ?>

            <article class="analytics-panel">
                <div class="analytics-panel-head">
                    <div>
                        <h2>Estado de caja</h2>
                        <p>Control operativo rapido para apertura y cierre.</p>
                    </div>
                    <span class="badge <?php echo $estadoCaja === 'Abierto' ? 'text-bg-success' : 'text-bg-secondary'; ?>"><?php echo htmlspecialchars($estadoCaja); ?></span>
                </div>
                <div class="analytics-list">
                    <div><span>Efectivo del dia</span><strong>S/. <?php echo number_format((float)$totalEfectivoDia, 2); ?></strong></div>
                    <div><span>Tarjeta del dia</span><strong>S/. <?php echo number_format((float)$totalTarjetaDia, 2); ?></strong></div>
                    <div><span>Caja del dia</span><strong>S/. <?php echo number_format((float)$totalCajaDia, 2); ?></strong></div>
                    <div><span>Utilidad del dia</span><strong>S/. <?php echo number_format((float)$utilidadDia, 2); ?></strong></div>
                    <?php if ($_SESSION['rol'] == 1) { ?>
                        <div><span>Efectivo acumulado</span><strong>S/. <?php echo number_format((float)$totalEfectivo, 2); ?></strong></div>
                        <div><span>Tarjeta acumalado</span><strong>S/. <?php echo number_format((float)$totalTarjeta, 2); ?></strong></div>
                        <div><span>Caja acumulada</span><strong>S/. <?php echo number_format((float)$totalCaja, 2); ?></strong></div>
                        <div><span>Utilidad acumulada</span><strong>S/. <?php echo number_format((float)$utilidadTotal, 2); ?></strong></div>
                    <?php } ?>
                </div>
                <div class="analytics-actions mt-3">
                    <a href="caja/actividad_caja_diaria.php" class="btn btn-outline-primary btn-sm">Actividad diaria</a>
                    <?php if ($_SESSION['rol'] == 1) { ?>
                        <a href="caja/lista_caja.php" class="btn btn-outline-secondary btn-sm">Historial de caja</a>
                    <?php } ?>
                </div>
                <div class="analytics-actions mt-3">
                    <?php if ($_SESSION['rol'] == 1) { ?>
                        <button class="btn btn-primary btn-sm" type="button" onclick="mostrarFormulario('abrirCaja', <?php echo $user; ?>)">Abrir caja</button>
                        <button class="btn btn-outline-dark btn-sm" type="button" onclick="mostrarFormulario('cerrarCaja', <?php echo $user; ?>)">Cerrar caja</button>
                    <?php } ?>
                </div>
            </article>

            <article class="analytics-panel">
                <div class="analytics-panel-head">
                    <div>
                        <h2>Clientes con mayor compra</h2>
                        <p>Ranking del periodo sin considerar Cliente General.</p>
                    </div>
                    <?php if ($_SESSION['rol'] == 1) { ?>
                        <a href="reportes/reportes.php?top_metric=monto&top_limit=20" class="btn btn-link btn-sm">Ver mas</a>
                    <?php } ?>
                </div>
                <div class="analytics-table-wrap">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Compras</th>
                                <?php if ($_SESSION['rol'] == 1) { ?>
                                    <th>Monto</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($topClients) { ?>
                                <?php foreach ($topClients as $client) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($client['Nombre']); ?></td>
                                        <td><?php echo (int)$client['cantidadCompras']; ?></td>
                                        <?php if ($_SESSION['rol'] == 1) { ?>
                                            <td>S/. <?php echo number_format((float)$client['montoCompras'], 2); ?></td>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr><td colspan="3">No hay ventas registradas en el periodo.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <?php if($_SESSION['rol'] == 1 ) {?>
                <article class="analytics-panel">
                    <div class="analytics-panel-head">
                        <div>
                            <h2>Productos mas vendidos</h2>
                            <p>Articulos con mayor movimiento dentro del periodo.</p>
                        </div>
                        <?php if ($_SESSION['rol'] == 1) { ?>
                            <a href="reportes/estadisticas.php" class="btn btn-link btn-sm">Ir al reporte</a>
                        <?php } ?>
                    </div>
                    <div class="analytics-table-wrap">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Unidades</th>
                                    <?php if ($_SESSION['rol'] == 1) { ?>
                                        <th>Utilidad</th>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($topProducts) { ?>
                                    <?php foreach ($topProducts as $product) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['Nombre']); ?></td>
                                            <td><?php echo (int)$product['cantidadVendida']; ?></td>
                                            <?php if ($_SESSION['rol'] == 1) { ?>
                                                <td>S/. <?php echo number_format((float)$product['utilidadGenerada'], 2); ?></td>
                                            <?php } ?>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr><td colspan="3">No hay movimientos de productos en el periodo.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            <?php } ?>
        </div>

        <?php if ($_SESSION['rol'] == 1) { ?>
            <div class="analytics-hero mt-5">
                <div>
                    <span class="analytics-eyebrow">Monitoreo proactivo</span>
                    <h1>⚠️ Alertas del sistema</h1>
                    <p>Artículos por debajo del stock mínimo y cuotas de préstamos fuera de fecha. Actualizado cada minuto.</p>
                </div>
                <div class="analytics-actions">
                    <a href="alertas/lista_alertas.php" class="btn btn-primary">Ver todas las alertas</a>
                    <button class="btn btn-outline-primary" id="btnGenerarAlertasIndex" onclick="generarAlertasManual()">
                        <i class="fas fa-bolt me-1"></i> Generar ahora
                    </button>
                </div>
            </div>
            <?php include __DIR__ . "/alertas/alertas_widget_index.php"; ?>
        <?php } ?>

        <div id="cerrarCaja" class="formulario">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cerrarCajaLabel">Cerrar caja</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idCerrarCaja" name="idCerrarCaja">
                        <div class="mb-3">
                            <p class="bg-danger text-white p-2">¿Esta seguro de cerrar la caja?, esta operación no se puede revertir.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="button" onclick="cerrarCaja()">Confirmar</button>
                        <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('cerrarCaja')">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="abrirCaja" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="abrirCajaLabel">Abrir caja</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idAbrirCaja" name="idAbrirCaja">
                        <div class="mb-3">
                            <label for="montoAbrirCaja">Monto inicial</label>
                            <input type="number" id="montoAbrirCaja" name="montoAbrirCaja" step="0.01" placeholder="Ingrese el monto inicial">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="button" onclick="abrirCaja()">Confirmar</button>
                        <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('abrirCaja')">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if ($_SESSION['rol'] == 1) { ?>
        <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasAlertas" aria-labelledby="offcanvasAlertasLabel">
            <div class="offcanvas-header border-bottom border-secondary">
                <h5 class="offcanvas-title" id="offcanvasAlertasLabel">
                    <i class="fas fa-bell me-2"></i> Últimas alertas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body px-0 py-0">
                <div id="offcanvasAlertasBody" class="list-group list-group-flush">
                    <div class="p-3 text-white-50 small">Cargando alertas...</div>
                </div>
                <div class="p-3 border-top border-secondary">
                    <a href="alertas/lista_alertas.php" class="btn btn-primary w-100">
                        <i class="fas fa-list me-1"></i> Ver todas
                    </a>
                </div>
            </div>
        </div>

        <div class="toast-container position-fixed bottom-0 end-0 p-3 z-3" style="z-index: 9999;">
            <div id="toastAlertasIndex" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body" id="toastAlertasIndexBody">Listo.</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php include "includes/footer.php"; ?>

    <?php if ($_SESSION['rol'] == 1) { ?>
    <script>
        (function(){
            const BASE = 'alertas/generar_alertas.php';

            function tipoBadge(tipo) {
                if (tipo === 'stock_bajo') return '<span class="badge text-bg-warning me-2"><i class="fas fa-boxes"></i></span>';
                if (tipo === 'cuota_vencida') return '<span class="badge text-bg-danger me-2"><i class="fas fa-calendar-times"></i></span>';
                return '<span class="badge text-bg-info me-2"><i class="fas fa-bell"></i></span>';
            }

            function inyectarBotonCampana() {
                const navRight = document.querySelector('.app-navbar .container-fluid > .d-none.d-xl-flex');
                if (!navRight) return;
                if (document.getElementById('btnNavAlertas')) return;
                const wrapper = document.createElement('div');
                wrapper.className = 'position-relative';
                wrapper.id = 'btnNavAlertas';
                wrapper.innerHTML = `
                    <button class="btn btn-outline-light btn-sm position-relative" type="button"
                            data-bs-toggle="offcanvas" data-bs-target="#offcanvasAlertas"
                            aria-controls="offcanvasAlertas" title="Alertas">
                        <i class="fas fa-bell"></i>
                        <span id="badgeNavAlertas"
                              class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger border border-dark d-none">
                            0
                            <span class="visually-hidden">alertas sin leer</span>
                        </span>
                    </button>
                `;
                navRight.insertBefore(wrapper, navRight.firstChild);
            }

            function actualizarBadgeYOffcanvas() {
                fetch(`${BASE}?accion=count`)
                    .then(r => r.json())
                    .then(res => {
                        if (!res.resultado) return;
                        const badge = document.getElementById('badgeNavAlertas');
                        if (badge) {
                            const cnt = (res.count || 0);
                            badge.textContent = cnt > 99 ? '99+' : cnt;
                            if (cnt > 0) {
                                badge.classList.remove('d-none');
                            } else {
                                badge.classList.add('d-none');
                            }
                        }
                        const body = document.getElementById('offcanvasAlertasBody');
                        if (body) {
                            if (!res.ultimas || res.ultimas.length === 0) {
                                body.innerHTML = '<div class="p-3 text-white-50 small">No hay alertas sin leer. 🎉</div>';
                            } else {
                                body.innerHTML = res.ultimas.map(a => `
                                    <div class="list-group-item list-group-item-action bg-transparent border-secondary text-white py-3">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">${tipoBadge(a.Tipo)}</div>
                                            <div class="flex-grow-1">
                                                <div class="small">${a.Mensaje}</div>
                                                <div class="text-white-50 small mt-1">${a.FechaGeneracion || ''}</div>
                                            </div>
                                        </div>
                                    </div>
                                `).join('');
                            }
                        }
                    })
                    .catch(() => {});
            }

            window.generarAlertasManual = function() {
                const btn = document.getElementById('btnGenerarAlertasIndex');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generando...';
                }
                fetch(`${BASE}?accion=run`)
                    .then(r => r.json())
                    .then(res => {
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-bolt me-1"></i> Generar ahora';
                        }
                        const toast = document.getElementById('toastAlertasIndex');
                        const body = document.getElementById('toastAlertasIndexBody');
                        if (toast && body && res.resultado) {
                            const adv = res.advertencia ? ` ⚠️ ${res.advertencia}` : '';
                            toast.classList.remove('text-bg-success','text-bg-warning');
                            toast.classList.add(res.advertencia ? 'text-bg-warning' : 'text-bg-success');
                            body.innerText = `Stock: ${res.alertasStock} · Cuotas: ${res.alertasCuotas} · Nuevas: ${res.nuevas}${adv}`;
                            bootstrap.Toast.getOrCreateInstance(toast).show();
                        }
                        actualizarBadgeYOffcanvas();
                        if (typeof poblarStockBajo === 'function') poblarStockBajo();
                        if (typeof poblarCuotas === 'function') poblarCuotas();
                    })
                    .catch(() => {
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-bolt me-1"></i> Generar ahora';
                        }
                    });
            };

            document.addEventListener('DOMContentLoaded', () => {
                inyectarBotonCampana();
                actualizarBadgeYOffcanvas();
                setInterval(actualizarBadgeYOffcanvas, 60000);
            });
        })();
    </script>
    <?php } ?>

    <script>
        const indexTrendCtx = document.getElementById('indexTrendChart');
        if (indexTrendCtx) {
            new Chart(indexTrendCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($dashboard['sales_series']['labels']); ?>,
                    datasets: [
                        {
                            label: 'Ventas',
                            data: <?php echo json_encode($dashboard['sales_series']['values']); ?>,
                            borderColor: '#0f7b8a',
                            backgroundColor: 'rgba(15, 123, 138, 0.12)',
                            fill: true,
                            tension: 0.32
                        },
                        {
                            label: 'Ganancia Bruta',
                            data: <?php echo json_encode($dashboard['profit_series']['values']); ?>,
                            borderColor: '#d97706',
                            backgroundColor: 'rgba(217, 119, 6, 0.10)',
                            fill: true,
                            tension: 0.32
                        },
                        {
                            label: 'Rentabilidad',
                            data: <?php echo json_encode($dashboard['rentability_series']['values']); ?>,
                            borderColor: '#0611d9ff',
                            backgroundColor: 'rgba(28, 11, 213, 0.1)',
                            fill: true,
                            tension: 0.32
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
