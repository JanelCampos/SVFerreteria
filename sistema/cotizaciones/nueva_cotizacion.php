<?php
    session_start();
    include "../../conexion.php";

    if (empty($_SESSION['active']) || ($_SESSION['rol'] != 1 && $_SESSION['rol'] != 2)) {
        header('location: ../');
        exit;
    }

    $fechaVigenciaDefault = date('Y-m-d', strtotime('+7 days'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "../includes/scripts_2.php"; ?>
    <?php include "../includes/title.php"; ?>
</head>
<body>
    <?php include "../includes/header_2.php"; ?>
    <div id="rol" data-rol="<?= $_SESSION['rol']; ?>"></div>

    <div class="container-fluid" style="padding-top: 110px;">
        <div class="row g-3">
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm h-60 bg-white">
                    <div class="card-header text-center">
                        <h5 class="mb-0">Datos cliente</h5>
                    </div>
                    <div class="card-body">
                        <form action="" class="form_venta">
                            <input type="hidden" id="idClienteCotizacion" name="idClienteCotizacion">
                            <label for="dniClienteCotizacion">Dni:</label>
                            <input type="number" id="dniClienteCotizacion" name="dniClienteCotizacion" oninput="limitarDigitos(this,8)">
                            <label for="nombreCliente">Nombre:</label>
                            <input type="text" id="nombreCliente" name="nombreCliente">
                            <label for="direccionCliente">Dirección:</label>
                            <input type="text" id="direccionCliente" name="direccionCliente">
                            <label for="telefonoCliente">Teléfono:</label>
                            <input type="number" id="telefonoCliente" name="telefonoCliente" oninput="limitarDigitos(this,9)">
                            <label for="fechaVigencia">Fecha vigencia:</label>
                            <input type="date" id="fechaVigencia" name="fechaVigencia" value="<?= $fechaVigenciaDefault; ?>">
                            <label for="observaciones">Observaciones:</label>
                            <textarea id="observaciones" name="observaciones" class="form-control" rows="3"></textarea>
                            <br>
                            <button class="btn btn-secondary" type="button" onclick="limpiarClienteCotizacion()">Limpiar cliente</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm h-100 bg-white">
                    <div class="card-header text-center">
                        <h5 class="mb-0">Buscar artículo</h5>
                    </div>
                    <div class="card-body">
                        <form action="" class="form_venta">
                            <label for="palabraClave">Buscar producto(nombre/C. barra)</label>
                            <input type="text" id="palabraClave" name="palabraClave">
                            <div id="resultados" class="list-group overflow-auto mt-2" style="max-height:28rem;"></div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12">
                <div class="card shadow-sm h-100 bg-white">
                    <div class="card-header text-center">
                        <h5 class="mb-0">Detalle cotización</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Cant.</th>
                                        <th>UdM</th>
                                        <th>Precio</th>
                                        <th>Dto%</th>
                                        <th>SubTotal</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="datosCotizacion">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                        <td colspan="2">
                                            <div class="input-group">
                                                <span class="input-group-text">S/.</span>
                                                <input type="text" id="totalCotizacion" class="form-control" readonly value="0.00">
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-end">
                                            <button class="btn btn-success" type="button" onclick="guardarCotizacion()">Guardar cotización</button>
                                            <button class="btn btn-warning" type="button" onclick="limpiarDetalleCotizacion()">Limpiar todo</button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="añadirProductoCotizacion" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Añadir articulo a cotización</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="idProductoCot" name="idProductoCot">
                    <input type="hidden" id="factorAplicado" name="factorAplicado" value="1">
                    <input type="hidden" id="porcentajeDescuento" name="porcentajeDescuento" value="0">
                    <input type="hidden" id="precioConDescuento" name="precioConDescuento" value="0">
                    <input type="hidden" id="precioMinimoArticulo" name="precioMinimoArticulo" value="0">
                    <input type="hidden" id="unidadSeleccionada" name="unidadSeleccionada" value="">
                    <input type="hidden" id="cantidadActual" name="cantidadActual" value="">
                    <div class="mb-3">
                        <label for="nombreProductoCot" class="form-label">Nombre del producto</label>
                        <input type="text" id="nombreProductoCot" name="nombreProductoCot" class="form-control" readonly>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="stockProductoCot" class="form-label">Stock disponible</label>
                            <input type="number" id="stockProductoCot" name="stockProductoCot" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="unidadVenta" class="form-label">Unidad de venta</label>
                            <select id="unidadVenta" name="unidadVenta" class="form-select" onchange="cambiarUnidadVentaCotizacion()">
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="precioVenta" class="form-label">Precio de venta (UdM)</label>
                            <input type="number" id="precioVenta" name="precioVenta" step="0.01" class="form-control" required oninput="calcularDescuentoVentaCotizacion()">
                        </div>
                        <div class="col-md-6">
                            <label for="precioMinimoMostrar" class="form-label">Precio mínimo</label>
                            <input type="number" id="precioMinimoMostrar" name="precioMinimoMostrar" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="stockVenta" class="form-label">Cantidad</label>
                            <input type="number" id="stockVenta" name="stockVenta" required value="1" min="0.01" step="0.01" class="form-control" oninput="calcularDescuentoVentaCotizacion()">
                        </div>
                    </div>
                    <div id="equivalenteVentaInfo" class="mb-3 small text-muted fw-semibold" style="display:none;"></div>
                    <div class="card bg-light mb-3">
                        <div class="card-header py-2">
                            <strong>Previsualización de descuentos y totales</strong>
                        </div>
                        <div class="card-body py-2">
                            <div class="row g-2 text-sm">
                                <div class="col-md-4">
                                    <label class="form-label mb-1">Descuento aplicable</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" id="descuentoMostrar" class="form-control" readonly value="0">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1">Precio c/descuento</label>
                                    <input type="number" id="precioConDescuentoMostrar" class="form-control form-control-sm" readonly step="0.01">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1">Subtotal</label>
                                    <input type="text" id="subTotalMostrar" class="form-control form-control-sm" readonly>
                                </div>
                            </div>
                            <div id="infoDescuentosEscalonados" class="mt-2 text-muted small"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-check form-switch">
                        <label class="form-check-label" for="aplicarDescuento">
                            Aplicar Descuento
                        </label>
                        <input class="form-check-input" type="checkbox" value="" id="aplicarDescuento" switch>
                    </div>
                    <button class="btn btn-primary" type="button" onclick="añadirProductoCotizacion()">Añadir</button>
                    <button class="btn btn-secondary" type="button" onclick="ocultarFormularioCotizacion('añadirProductoCotizacion')">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include "../includes/footer_2.php"; ?>

    <script>
        window._esCotizacion = true;

        $(document).ready(function() {
            localStorage.removeItem('detalleCotizacionTemp');
            buscarProductoTempCotizacion();

            $('#palabraClave').on('input', function() {
                var palabraClave = $(this).val();
                buscarProductoCotizacion(palabraClave);
            });

            $('#dniClienteCotizacion').on('keydown', function(e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                    traerClienteCotizacion();
                }
            });

            $('#dniClienteCotizacion').on('input', function() {
                if($(this).val().length === 8) {
                    traerClienteCotizacion();
                } else {
                    document.getElementById('idClienteCotizacion').value = '';
                    document.getElementById('nombreCliente').value = '';
                    document.getElementById('direccionCliente').value = '';
                    document.getElementById('telefonoCliente').value = '';
                    document.getElementById('observaciones').value = '';
                    document.getElementById('fechaVigencia').value = '<?= $fechaVigenciaDefault; ?>';
                    $('#nombreCliente').removeAttr('disabled');
                    $('#direccionCliente').removeAttr('disabled');
                    $('#telefonoCliente').removeAttr('disabled');
                    document.getElementById('dniClienteCotizacion').focus();
                }
            });
        });

        function traerClienteCotizacion() {
            var dniCliente = $('#dniClienteCotizacion').val();
            if (dniCliente.length !== 8) {
                mostrarAlertaErrorTiempo('El DNI debe tener 8 dígitos');
                return;
            }
            fetch('../operaciones/get_cliente.php?dni=' + dniCliente)
                .then(response => response.json())
                .then(data => {
                    if (data === null) {
                        document.getElementById('idClienteCotizacion').value = '';
                        $('#nombreCliente').removeAttr('disabled');
                        $('#direccionCliente').removeAttr('disabled');
                        $('#telefonoCliente').removeAttr('disabled');
                    } else {
                        document.getElementById('idClienteCotizacion').value = data.Id_Cliente;
                        document.getElementById('dniClienteCotizacion').value = data.Dni;
                        document.getElementById('nombreCliente').value = data.Nombre;
                        document.getElementById('direccionCliente').value = data.Direccion;
                        document.getElementById('telefonoCliente').value = data.Telefono;
                        $('#nombreCliente').attr('disabled', 'disabled');
                        $('#direccionCliente').attr('disabled', 'disabled');
                        $('#telefonoCliente').attr('disabled', 'disabled');
                    }
                })
                .catch(() => {
                    mostrarAlertaErrorTiempo('Error al buscar cliente');
                });
        }

        function limpiarClienteCotizacion() {
            document.getElementById('idClienteCotizacion').value = '';
            document.getElementById('dniClienteCotizacion').value = '';
            document.getElementById('nombreCliente').value = '';
            document.getElementById('direccionCliente').value = '';
            document.getElementById('telefonoCliente').value = '';
            document.getElementById('observaciones').value = '';
            document.getElementById('fechaVigencia').value = '<?= $fechaVigenciaDefault; ?>';
            $('#nombreCliente').removeAttr('disabled');
            $('#direccionCliente').removeAttr('disabled');
            $('#telefonoCliente').removeAttr('disabled');
            document.getElementById('dniClienteCotizacion').focus();
        }

        function buscarProductoCotizacion(palabraClave) {
            if (palabraClave.length >= 3) {
                fetch('../operaciones/get_articulos.php?palabra=' + palabraClave)
                    .then(response => response.json())
                    .then(data => {
                        mostrarResultadosCotizacion(data);
                    })
                    .catch(() => {
                        $('#resultados').html('');
                    });
            } else {
                $('#resultados').html('');
            }
        }

        function mostrarResultadosCotizacion(data) {
            const rolUsuario = document.getElementById('rol').dataset.rol;
            let resultadosHtml = '<ul> <h5>Resultados encontrados: ' + data.length + '</h5>';
            data.forEach(item => {
                resultadosHtml += `
                    <button type="button" class="list-group-item list-group-item-action list-group-item-info mt-1 rounded-end" onclick="mostrarFormularioCotizacion('añadirProductoCotizacion',${item.IdArticulo})">${item.Nombre} - ${item.Cantidad} - ${rolUsuario == 1 ? item.Precio_Compra : ""}</button>
                `;
            });
            resultadosHtml += '</ul>';
            $('#resultados').html(resultadosHtml);
        }

        function mostrarFormularioCotizacion(idFormulario, id) {
            if (window.showLegacyModal) {
                window.showLegacyModal(idFormulario);
            } else {
                var modalEl = document.getElementById(idFormulario);
                if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                    var bsModal = new bootstrap.Modal(modalEl);
                    bsModal.show();
                } else if (modalEl) {
                    modalEl.style.display = 'block';
                }
            }

            if (idFormulario === 'añadirProductoCotizacion') {
                fetch('../operaciones/get_producto.php?id=' + id)
                    .then(response => response.json())
                    .then(resp => {
                        if (!resp.resultado) {
                            mostrarAlertaErrorTiempo('No se pudo cargar el producto');
                            return;
                        }
                        var data = resp.datos;
                        var unidades = resp.unidades || [];
                        var descuentos = resp.descuentos || [];
                        document.getElementById('idProductoCot').value = data.IdArticulo;
                        document.getElementById('nombreProductoCot').value = data.Nombre;
                        document.getElementById('stockProductoCot').value = data.Cantidad;
                        document.getElementById('cantidadActual').value = data.Cantidad;
                        var precioMinimo = parseFloat(data.Precio_Minimo) || 0;
                        document.getElementById('precioMinimoArticulo').value = precioMinimo.toFixed(2);
                        document.getElementById('precioMinimoMostrar').value = precioMinimo.toFixed(2);
                        var unidadBase = data.Unidad_Base || 'unidad';
                        var selectUdM = document.getElementById('unidadVenta');
                        selectUdM.innerHTML = '';
                        unidades.forEach(function(ud) {
                            var opt = document.createElement('option');
                            opt.value = ud.IdUnidad;
                            opt.textContent = ud.Unidad + ' (x' + parseFloat(ud.Factor).toFixed(2) + ')';
                            opt.dataset.unidad = ud.Unidad;
                            opt.dataset.factor = parseFloat(ud.Factor);
                            opt.dataset.predeterminada = ud.EsPredeterminada;
                            opt.dataset.precVenta = parseFloat(ud.PrecioVenta || 0).toFixed(2);
                            opt.dataset.precMinimo = parseFloat(ud.PrecioMinimo || 0).toFixed(2);
                            if (ud.EsPredeterminada == 1) opt.selected = true;
                            selectUdM.appendChild(opt);
                        });
                        if (selectUdM.options.length == 0) {
                            var opt = document.createElement('option');
                            opt.value = 0;
                            opt.textContent = unidadBase + ' (x1.00)';
                            opt.dataset.unidad = unidadBase;
                            opt.dataset.factor = 1;
                            selectUdM.appendChild(opt);
                        }
                        window._descuentosArticuloActual = descuentos;
                        var infoHtml = '';
                        if (descuentos.length > 0) {
                            infoHtml = '<strong>Escalas de descuento configuradas:</strong><br>';
                            descuentos.forEach(function(d) {
                                infoHtml += '• ≥ ' + d.CantidadMinima + ' unidad(es) → ' + parseFloat(d.PorcentajeDescuento).toFixed(2) + '%<br>';
                            });
                        } else {
                            infoHtml = 'Sin descuentos escalonados configurados para este artículo.';
                        }
                        document.getElementById('infoDescuentosEscalonados').innerHTML = infoHtml;
                        document.getElementById('precioVenta').value = parseFloat(data.Precio_Unitario || 0).toFixed(2);
                        document.getElementById('stockVenta').value = 1;
                        cambiarUnidadVentaCotizacion();
                    })
                    .catch(() => {
                        mostrarAlertaErrorTiempo('Error al cargar el producto');
                    });
            }
        }

        function ocultarFormularioCotizacion(idFormulario) {
            var modalEl = document.getElementById(idFormulario);
            if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                var bsModal = bootstrap.Modal.getInstance(modalEl);
                if (bsModal) bsModal.hide();
            } else if (modalEl) {
                modalEl.style.display = 'none';
            }

            if (idFormulario === 'añadirProductoCotizacion') {
                document.getElementById('idProductoCot').value = '';
                document.getElementById('nombreProductoCot').value = '';
                document.getElementById('stockProductoCot').value = '';
                document.getElementById('precioVenta').value = '';
                document.getElementById('stockVenta').value = 1;
                document.getElementById('palabraClave').focus();
                document.getElementById('palabraClave').value = '';
                document.getElementById('aplicarDescuento').checked = false;
                if (document.getElementById('unidadVenta')) {
                    document.getElementById('unidadVenta').innerHTML = '<option value="">Seleccione</option>';
                    document.getElementById('unidadVenta').value = '';
                }
                if (document.getElementById('factorAplicado')) document.getElementById('factorAplicado').value = 1;
                if (document.getElementById('precioMinimoMostrar')) document.getElementById('precioMinimoMostrar').value = '';
                if (document.getElementById('precioMinimoArticulo')) document.getElementById('precioMinimoArticulo').value = 0;
                if (document.getElementById('descuentoMostrar')) document.getElementById('descuentoMostrar').value = 0;
                if (document.getElementById('porcentajeDescuento')) document.getElementById('porcentajeDescuento').value = 0;
                if (document.getElementById('precioConDescuentoMostrar')) document.getElementById('precioConDescuentoMostrar').value = '';
                if (document.getElementById('precioConDescuento')) document.getElementById('precioConDescuento').value = 0;
                if (document.getElementById('subTotalMostrar')) document.getElementById('subTotalMostrar').value = '';
                if (document.getElementById('unidadSeleccionada')) document.getElementById('unidadSeleccionada').value = '';
                if (document.getElementById('infoDescuentosEscalonados')) document.getElementById('infoDescuentosEscalonados').innerHTML = '';
                $('#resultados').html('');
            }
        }

        function cambiarUnidadVentaCotizacion() {
            var selectUdM = document.getElementById('unidadVenta');
            var cantidadActual = document.getElementById('cantidadActual');
            if (!selectUdM || !selectUdM.value) return;
            var opt = selectUdM.options[selectUdM.selectedIndex];
            var factor = parseFloat(opt.dataset.factor) || 1;
            var unidad = opt.dataset.unidad || '';
            var precioVentaUDM = parseFloat(opt.dataset.precVenta) || 0;
            var precioMinimo = parseFloat(opt.dataset.precMinimo) || 0;
            document.getElementById('factorAplicado').value = factor.toFixed(4);
            document.getElementById('precioMinimoMostrar').value = precioMinimo.toFixed(2);
            document.getElementById('precioMinimoArticulo').value = precioMinimo.toFixed(2);
            document.getElementById('precioVenta').value = precioVentaUDM.toFixed(2);
            document.getElementById('unidadSeleccionada').value = unidad;
            document.getElementById('stockVenta').value = 1;
            document.getElementById('stockVenta').focus();
            document.getElementById('stockProductoCot').value = (parseFloat(cantidadActual.value) * factor).toFixed(2);
            calcularDescuentoVentaCotizacion();
        }

        function calcularDescuentoVentaCotizacion() {
            var precioUdM = parseFloat(document.getElementById('precioVenta').value) || 0;
            var cantidad = parseFloat(document.getElementById('stockVenta').value) || 0;
            var factor = parseFloat(document.getElementById('factorAplicado').value) || 1;
            var descuentos = window._descuentosArticuloActual || [];
            var porcentajeDto = 0;
            var cantidadEnBase = cantidad / factor;
            for (var i = descuentos.length - 1; i >= 0; i--) {
                if (cantidadEnBase >= parseFloat(descuentos[i].CantidadMinima)) {
                    porcentajeDto = parseFloat(descuentos[i].PorcentajeDescuento);
                    break;
                }
            }
            var precioConDto = precioUdM * (1 - (porcentajeDto / 100));
            var subTotal = precioConDto * cantidad;
            document.getElementById('descuentoMostrar').value = porcentajeDto.toFixed(2);
            document.getElementById('porcentajeDescuento').value = porcentajeDto.toFixed(4);
            document.getElementById('precioConDescuentoMostrar').value = precioConDto.toFixed(2);
            document.getElementById('precioConDescuento').value = precioConDto.toFixed(4);
            document.getElementById('subTotalMostrar').value = 'S/. ' + subTotal.toFixed(2);
        }

        function añadirProductoCotizacion() {
            var idArticulo = $('#idProductoCot').val();
            var precioVenta = $('#precioVenta').val();
            var stockVenta = $('#stockVenta').val();
            var unidad = $('#unidadSeleccionada').val();
            var factorAplicado = $('#factorAplicado').val();
            var porcentajeDescuento = $('#porcentajeDescuento').val();
            var precioConDescuento = $('#precioConDescuentoMostrar').val();
            var nombreArticulo = $('#nombreProductoCot').val();
            var aplicarDescuento = $('#aplicarDescuento').is(':checked');

            if (parseFloat(precioVenta) <= 0 || precioVenta == '') {
                mostrarAlertaErrorTiempo('El monto tiene que ser mayor a cero');
                return;
            }
            if (parseFloat(stockVenta) <= 0 || stockVenta == '') {
                mostrarAlertaErrorTiempo('La cantidad debe ser mayor a cero');
                return;
            }
            if(!aplicarDescuento){
                precioConDescuento = precioVenta;
                porcentajeDescuento = 0.00;
            }

            var tempCot = JSON.parse(localStorage.getItem('detalleCotizacionTemp') || '[]');
            var correlativo = tempCot.length > 0 ? Math.max.apply(null, tempCot.map(function(x) { return x.correlativo; })) + 1 : 1;
            var item = {
                correlativo: correlativo,
                codArticulo: idArticulo,
                nombreArticulo: nombreArticulo,
                cantidad: stockVenta,
                Unidad: unidad,
                precio_venta: precioVenta,
                PorcentajeDescuento: porcentajeDescuento,
                PrecioConDescuento: precioConDescuento,
                FactorAplicado: factorAplicado,
                AplicarDescuento: aplicarDescuento
            };
            tempCot.push(item);
            localStorage.setItem('detalleCotizacionTemp', JSON.stringify(tempCot));

            mostrarDatosCotizacion(tempCot);
            ocultarFormularioCotizacion('añadirProductoCotizacion');
            buscarProductoCotizacion('');
            mostrarAlertaExitoTiempo('Producto añadido a cotización');
        }

        function buscarProductoTempCotizacion() {
            var tempCot = JSON.parse(localStorage.getItem('detalleCotizacionTemp') || '[]');
            mostrarDatosCotizacion(tempCot);
        }

        function mostrarDatosCotizacion(data) {
            let resultadosHtml = '';
            let totalCotizacion = 0;
            data.forEach(item => {
                const nombreArticulo = item.nombreArticulo || '';
                const cantidad = parseFloat(item.cantidad) || 0;
                const precioUnitario = parseFloat(item.PrecioConDescuento != null && item.PrecioConDescuento > 0 ? item.PrecioConDescuento : item.precio_venta) || 0;
                const unidad = item.Unidad || '-';
                const porcentajeDto = parseFloat(item.PorcentajeDescuento) || 0;
                const subTotal = cantidad * precioUnitario;

                totalCotizacion += subTotal;
                resultadosHtml += `
                    <tr>
                        <td class="text-truncate" style="max-width:180px;" title="${nombreArticulo}">${nombreArticulo}</td>
                        <td>${cantidad}</td>
                        <td>${unidad}</td>
                        <td>S/. ${precioUnitario.toFixed(2)}</td>
                        <td>${porcentajeDto > 0 ? porcentajeDto.toFixed(1) + '%' : '-'}</td>
                        <td>S/. ${subTotal.toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" onclick="quitarProductoCotizacion(${item.correlativo})"><i class="far fa-trash-alt"></i></button>
                        </td>
                    </tr>
                `;
            });
            $('#datosCotizacion').html(resultadosHtml);
            $('#totalCotizacion').val(totalCotizacion.toFixed(2));
        }

        function quitarProductoCotizacion(correlativo) {
            var tempCot = JSON.parse(localStorage.getItem('detalleCotizacionTemp') || '[]');
            document.getElementById('aplicarDescuento').checked = false;
            tempCot = tempCot.filter(function(item) {
                return item.correlativo !== correlativo;
            });
            localStorage.setItem('detalleCotizacionTemp', JSON.stringify(tempCot));
            mostrarDatosCotizacion(tempCot);
            mostrarAlertaExitoTiempo('Producto quitado');
        }

        function limpiarDetalleCotizacion() {
            localStorage.removeItem('detalleCotizacionTemp');
            mostrarDatosCotizacion([]);
            limpiarClienteCotizacion();
            mostrarAlertaExitoTiempo('Cotización limpiada');
        }

        function guardarCotizacion() {
            var tempCot = JSON.parse(localStorage.getItem('detalleCotizacionTemp') || '[]');
            if (tempCot.length === 0) {
                mostrarAlertaErrorTiempo('Debe añadir al menos un artículo a la cotización');
                return;
            }

            var dniCliente = $('#dniClienteCotizacion').val();
            var nombreCliente = $('#nombreCliente').val();

            if (!dniCliente || dniCliente.length !== 8) {
                mostrarAlertaErrorTiempo('El DNI del cliente debe tener 8 dígitos');
                return;
            }
            if (!nombreCliente || nombreCliente.trim() === '') {
                mostrarAlertaErrorTiempo('El nombre del cliente es obligatorio');
                return;
            }

            var payload = {
                dniCliente: dniCliente,
                nombreCliente: nombreCliente,
                direccionCliente: $('#direccionCliente').val(),
                telefonoCliente: $('#telefonoCliente').val(),
                fechaVigencia: $('#fechaVigencia').val(),
                observaciones: $('#observaciones').val(),
                detalle: tempCot
            };

            fetch('procesar_cotizacion.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                if (data.resultado) {
                    localStorage.removeItem('detalleCotizacionTemp');
                    mostrarAlertaExito(data.mensaje + ' N° ' + data.idCotizacion);
                    setTimeout(() => {
                        var url = 'imprimir_cotizacion.php?idCotizacion=' + data.idCotizacion + '&nPdf=1';
                        window.open(url, '_blank', 'width=1000,height=800');
                        window.location.replace('lista_cotizaciones.php');
                    }, 10);
                } else {
                    mostrarAlertaErrorTiempo(data.mensaje || 'Error al guardar la cotización');
                }
            })
            .catch(() => {
                mostrarAlertaErrorTiempo('Error de conexión al guardar');
            });
        }
    </script>
</body>
</html>
