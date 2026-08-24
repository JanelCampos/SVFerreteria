<?php
if (!defined('_SVP_INCLUDED_WIDGET_ALERTAS')) {
    define('_SVP_INCLUDED_WIDGET_ALERTAS', 1);
}
if (empty($_SESSION['active']) || $_SESSION['rol'] != 1) {
    return;
}
?>
<?php echo '<div class="analytics-grid mt-4">'; ?>
<?php echo '<article class="analytics-panel">'; ?>
<?php echo '<div class="analytics-panel-head">'; ?>
<?php echo '<div>'; ?>
<?php echo '<h2><i class="fas fa-boxes text-warning me-2"></i> Artículos con stock bajo</h2>'; ?>
<?php echo '<p>Primeros 5 artículos por debajo del stock de alerta.</p>'; ?>
<?php echo '</div>'; ?>
<?php echo '<a href="alertas/lista_alertas.php?tipo=stock_bajo" class="btn btn-link btn-sm">Ver todas</a>'; ?>
<?php echo '</div>'; ?>
<?php echo '<div class="analytics-table-wrap">'; ?>
<?php echo '<table class="table table-sm align-middle mb-0" id="tblStockBajoWidget">'; ?>
<?php echo '<thead><tr><th>Artículo</th><th>Stock actual</th><th>Alerta</th></tr></thead>'; ?>
<?php echo '<tbody><tr><td colspan="3" class="text-center text-muted small">Cargando...</td></tr></tbody>'; ?>
<?php echo '</table>'; ?>
<?php echo '</div>'; ?>
<?php echo '</article>'; ?>

<?php echo '<article class="analytics-panel">'; ?>
<?php echo '<div class="analytics-panel-head">'; ?>
<?php echo '<div>'; ?>
<?php echo '<h2><i class="fas fa-calendar-times text-danger me-2"></i> Cuotas vencidas</h2>'; ?>
<?php echo '<p>Últimas 5 cuotas pendientes fuera de fecha.</p>'; ?>
<?php echo '</div>'; ?>
<?php echo '<a href="alertas/lista_alertas.php?tipo=cuota_vencida" class="btn btn-link btn-sm">Ver todas</a>'; ?>
<?php echo '</div>'; ?>
<?php echo '<div class="analytics-table-wrap">'; ?>
<?php echo '<table class="table table-sm align-middle mb-0" id="tblCuotasVencidasWidget">'; ?>
<?php echo '<thead><tr><th>Cliente / Detalle</th><th>Monto</th><th>Venc.</th></tr></thead>'; ?>
<?php echo '<tbody><tr><td colspan="3" class="text-center text-muted small">Cargando...</td></tr></tbody>'; ?>
<?php echo '</table>'; ?>
<?php echo '</div>'; ?>
<?php echo '</article>'; ?>
<?php echo '</div>'; ?>

<script>
(function(){
    function poblarStockBajo() {
        fetch('alertas/generar_alertas.php?accion=list&tipo=stock_bajo&solo_sin_leer=1&limit=5')
            .then(r=>r.json()).then(res=>{
                const tb = document.querySelector('#tblStockBajoWidget tbody');
                if (!res.resultado || !res.datos || res.datos.length===0) {
                    tb.innerHTML = '<tr><td colspan="3" class="text-center text-muted small">Sin alertas de stock bajo.</td></tr>';
                    return;
                }
                tb.innerHTML = res.datos.map(a=>{
                    const m = a.Mensaje || '';
                    return `<tr>
                        <td style="max-width:220px;">
                            <div class="text-truncate" title="${a.Mensaje.replace(/"/g,'&quot;')}">${m}</div>
                        </td>
                        <td><span class="badge text-bg-warning">${(m.match(/quedan\s+(\d+)/)||['','?'])[1]}</span></td>
                        <td><span class="badge text-bg-secondary">≤${(m.match(/≤(\d+)/)||['','?'])[1]}</span></td>
                    </tr>`;
                }).join('');
            }).catch(()=>{});
    }
    function poblarCuotas() {
        fetch('alertas/generar_alertas.php?accion=list&tipo=cuota_vencida&solo_sin_leer=1&limit=5')
            .then(r=>r.json()).then(res=>{
                const tb = document.querySelector('#tblCuotasVencidasWidget tbody');
                if (!res.resultado || !res.datos || res.datos.length===0) {
                    tb.innerHTML = '<tr><td colspan="3" class="text-center text-muted small">Sin cuotas vencidas.</td></tr>';
                    return;
                }
                tb.innerHTML = res.datos.map(a=>{
                    const m = a.Mensaje || '';
                    const monto = (m.match(/S\/\.\s*([0-9.,]+)/)||['','?'])[1];
                    const venc = (m.match(/Venc\.\s*([0-9\- :]+)/)||['','?'])[1];
                    const parts = m.split(' - ');
                    const cliente = parts[0] ? parts[0].replace('Cuota vencida: ','') : m;
                    return `<tr>
                        <td style="max-width:220px;">
                            <div class="text-truncate" title="${a.Mensaje.replace(/"/g,'&quot;')}">${cliente}</div>
                        </td>
                        <td><span class="badge text-bg-danger">S/. ${monto}</span></td>
                        <td><small class="text-muted">${venc}</small></td>
                    </tr>`;
                }).join('');
            }).catch(()=>{});
    }
    document.addEventListener('DOMContentLoaded', () => {
        poblarStockBajo();
        poblarCuotas();
        setInterval(() => { poblarStockBajo(); poblarCuotas(); }, 60000);
    });
})();
</script>
