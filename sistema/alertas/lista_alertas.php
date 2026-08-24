<?php
session_start();
include "../../conexion.php";
if (empty($_SESSION['active']) || $_SESSION['rol'] != 1) {
    header('location: ../../');
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

    <section id="container" class="container-fluid" style="padding-top:110px;">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Alertas del sistema</h1>
                <small class="text-muted">Controla los avisos de stock bajo, cuotas vencidas y otros eventos</small>
            </div>
            <div class="d-flex gap-2 mt-3 mt-lg-0">
                <span class="badge text-bg-info fs-6 px-3 py-2" id="badgeSinLeer" style="cursor:pointer;" title="Filtrar solo sin leer" onclick="filtrarSoloSinLeer()">
                    <i class="fas fa-eye-slash me-1"></i> Sin leer: <span id="countBadge">0</span>
                </span>
                <button class="btn btn-success" id="btnGenerar" onclick="generarAhora()">
                    <i class="fas fa-bolt me-1"></i> Generar alertas ahora
                </button>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Id</th>
                                <th>Tipo</th>
                                <th>Mensaje</th>
                                <th>Fecha generación</th>
                                <th>Leída</th>
                                <th>Fecha lectura</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tablaAlertas">
                            <tr><td colspan="7" class="text-center py-4 text-muted">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <small id="infoPaginacion" class="text-muted"></small>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="paginador"></ul>
                </nav>
            </div>
        </div>
    </section>

    <div class="toast-container position-fixed bottom-0 end-0 p-3 z-3" style="z-index: 9999;">
        <div id="toastAlertas" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastBody">Listo.</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <?php include "../includes/footer_2.php"; ?>

    <script>
        const BASE = 'generar_alertas.php';
        let pageActual = 1;
        let totalPaginas = 1;
        let soloSinLeer = false;

        function tipoBadge(tipo) {
            if (tipo === 'stock_bajo') return '<span class="badge text-bg-warning"><i class="fas fa-boxes me-1"></i> Stock bajo</span>';
            if (tipo === 'cuota_vencida') return '<span class="badge text-bg-danger"><i class="fas fa-calendar-times me-1"></i> Cuota vencida</span>';
            return '<span class="badge text-bg-info"><i class="fas fa-bell me-1"></i> Otro</span>';
        }

        function mostrarToast(msg, tipo = 'success') {
            const t = document.getElementById('toastAlertas');
            const body = document.getElementById('toastBody');
            t.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning');
            t.classList.add(tipo === 'success' ? 'text-bg-success' : (tipo === 'danger' ? 'text-bg-danger' : 'text-bg-warning'));
            body.innerText = msg;
            const bs = bootstrap.Toast.getOrCreateInstance(t);
            bs.show();
        }

        function filtrarSoloSinLeer() {
            soloSinLeer = !soloSinLeer;
            pageActual = 1;
            const badge = document.getElementById('badgeSinLeer');
            if (soloSinLeer) {
                badge.classList.remove('text-bg-info');
                badge.classList.add('text-bg-primary');
            } else {
                badge.classList.remove('text-bg-primary');
                badge.classList.add('text-bg-info');
            }
            cargarLista();
        }

        function pintarDatos(datos, total) {
            const perPage = 20;
            totalPaginas = Math.max(1, Math.ceil(total / perPage));
            const tbody = document.getElementById('tablaAlertas');
            if (!datos || datos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No hay alertas para mostrar.</td></tr>';
            } else {
                tbody.innerHTML = datos.map(a => {
                    const leida = parseInt(a.Leida) === 1;
                    return `
                    <tr>
                        <td>${a.IdAlerta}</td>
                        <td>${tipoBadge(a.Tipo)}</td>
                        <td>${a.Mensaje}</td>
                        <td>${a.FechaGeneracion}</td>
                        <td>${leida ? '<span class="badge text-bg-success"><i class="fas fa-check me-1"></i> Sí</span>' : '<span class="badge text-bg-secondary">No</span>'}</td>
                        <td>${a.FechaLectura || '-'}</td>
                        <td>
                            ${leida
                                ? '<span class="text-muted small">-</span>'
                                : `<button class="btn btn-sm btn-outline-primary" onclick="marcarLeida(${a.IdAlerta}, this)">Marcar leída</button>`
                            }
                        </td>
                    </tr>`;
                }).join('');
            }
            document.getElementById('infoPaginacion').innerText = `Total: ${total} alerta(s) · Página ${pageActual} de ${totalPaginas}`;
            renderPaginador();
        }

        function renderPaginador() {
            const ul = document.getElementById('paginador');
            let html = '';
            const addBtn = (label, page, disabled, active) => {
                html += `<li class="page-item ${active ? 'active' : ''} ${disabled ? 'disabled' : ''}">
                    <a class="page-link" href="#" ${disabled ? '' : `onclick="event.preventDefault(); irPagina(${page})"`}>${label}</a>
                </li>`;
            };
            addBtn('Anterior', Math.max(1, pageActual - 1), pageActual === 1, false);
            for (let i = 1; i <= totalPaginas; i++) {
                if (totalPaginas > 7) {
                    if (i === 1 || i === totalPaginas || (i >= pageActual - 1 && i <= pageActual + 1)) {
                        addBtn(i, i, false, i === pageActual);
                    } else if (i === pageActual - 2 || i === pageActual + 2) {
                        html += `<li class="page-item disabled"><a class="page-link">...</a></li>`;
                    }
                } else {
                    addBtn(i, i, false, i === pageActual);
                }
            }
            addBtn('Siguiente', Math.min(totalPaginas, pageActual + 1), pageActual === totalPaginas, false);
            ul.innerHTML = html;
        }

        function irPagina(p) {
            pageActual = p;
            cargarLista();
        }

        function cargarLista() {
            const url = `${BASE}?accion=list&page=${pageActual}${soloSinLeer ? '&solo_sin_leer=1' : ''}`;
            fetch(url).then(r => r.json()).then(res => {
                if (res.resultado) pintarDatos(res.datos, res.total);
            });
            fetch(`${BASE}?accion=count`).then(r => r.json()).then(res => {
                if (res.resultado) document.getElementById('countBadge').innerText = res.count;
            });
        }

        function marcarLeida(id, btn) {
            fetch(`${BASE}?accion=markread`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ IdAlerta: id })
            }).then(r => r.json()).then(res => {
                if (res.resultado) {
                    mostrarToast('Alerta marcada como leída.');
                    cargarLista();
                } else {
                    mostrarToast('No se pudo marcar la alerta.', 'danger');
                }
            });
        }

        function generarAhora() {
            const btn = document.getElementById('btnGenerar');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generando...';
            fetch(`${BASE}?accion=run`).then(r => r.json()).then(res => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-bolt me-1"></i> Generar alertas ahora';
                if (res.resultado) {
                    const adv = res.advertencia ? ` (${res.advertencia})` : '';
                    mostrarToast(`Procesado: ${res.alertasStock} stock, ${res.alertasCuotas} cuotas. Nuevas: ${res.nuevas}${adv}`, res.advertencia ? 'warning' : 'success');
                    cargarLista();
                } else {
                    mostrarToast('Error al generar alertas.', 'danger');
                }
            }).catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-bolt me-1"></i> Generar alertas ahora';
                mostrarToast('Error de conexión.', 'danger');
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarLista();
            setInterval(cargarLista, 60000);
        });
    </script>
</body>
</html>
