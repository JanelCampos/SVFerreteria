<?php
    include "../../conexion.php";
    session_start();
    include "../includes/paginador.php";

    $query_proveedor = mysqli_query($conexionDB,"SELECT IdProveedor, Nombre FROM proveedores ORDER BY Nombre ASC");
    $result_proveedor = mysqli_num_rows($query_proveedor);

    $query_categoria = mysqli_query($conexionDB,"SELECT IdCategoria, Nombre FROM categorias WHERE Estado = 1 ORDER BY Nombre ASC");
    $result_categoria = mysqli_num_rows($query_categoria);

    $results_per_page = 10;
    $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $consulta = "
        SELECT a.IdArticulo, a.Nombre as nombreA, a.Cantidad, a.Stock_Alerta, a.Precio_Compra, a.Precio_Unitario,
               a.Precio_Minimo, a.Unidad_Presentacion, p.Nombre as nombreP, c.Nombre as nombreC
        FROM articulos a
        INNER JOIN proveedores p ON a.Cod_Proveedor = p.IdProveedor
        LEFT JOIN categorias c ON a.Cod_Categoria = c.IdCategoria
        ORDER BY a.IdArticulo DESC
    ";
    $where = "
        articulos a
        INNER JOIN proveedores p ON a.Cod_Proveedor = p.IdProveedor
        LEFT JOIN categorias c ON a.Cod_Categoria = c.IdCategoria
        ORDER BY a.IdArticulo DESC
    ";
    list($result, $total_records) = getPaginatedDataArticulos($conexionDB, $consulta, $where, $current_page, $results_per_page);

    $row = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<?php include "../includes/scripts_2.php"; ?>
    <?php include "../includes/title.php"; ?>
</head>
<body>
    <div id="rol" data-rol="<?= $_SESSION['rol']; ?>"></div>
    <?php include "../includes/header_2.php"; ?>
	<section id="container">
        <div class="title_container">
            <h1>Listado de artículos</h1>
            <?php if($_SESSION['rol'] == 1){ ?>
                <a href="registro_articulo.php" class="btn_new"><i class="fas fa-plus"></i> Crear artículo</a>
            <?php } ?>
        </div>
        <div class="header_container">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="busquedaArticulo">Nombre de producto</label>
                    <input class="filtrosBusqueda" type="text" name="nombre" id="busquedaArticulo" placeholder="Ingrese nombre de producto">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="stockArticulo">Stock</label>
                    <select class="filtrosBusqueda" name="stockArticulo" id="stockArticulo">
                        <option value="">Seleccionar</option>
                        <option value="sinStock">Sin stock</option>
                        <option value="pocoStock">Poco stock (≤ alerta)</option>
                        <option value="conStock">Con stock</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="nombreProveedor">Proveedor</label>
                    <select class="filtrosBusqueda" id="nombreProveedor">
                        <option value="" class="option1" selected>Proveedor</option>
                        <?php if($_SESSION['rol'] == 1) { ?>
                            <?php
                                if($result_proveedor > 0){
                                    while ($proveedor = mysqli_fetch_array($query_proveedor)){
                            ?>
                                <option value="<?php echo $proveedor['IdProveedor']; ?>"><?php echo $proveedor['Nombre']; ?></option>
                            <?php
                                    }
                                }
                            ?>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="nombreCategoria">Categoría</label>
                    <select class="filtrosBusqueda" id="nombreCategoria">
                        <option value="" class="option1" selected>Categoría</option>
                        <?php
                            if($result_categoria > 0){
                                while ($cat = mysqli_fetch_array($query_categoria)){
                        ?>
                            <option value="<?php echo $cat['IdCategoria']; ?>"><?php echo $cat['Nombre']; ?></option>
                        <?php
                                }
                            }
                        ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" onclick="buscarArticulo()">Aplicar filtros</button>
                        <a href="lista_articulos.php" class="btn btn-outline-secondary">Restablecer</a>
                    </div>
                </div>
                <?php if($_SESSION['rol'] == 1){ ?>
                    <div class="col-12 col-md-4 col-lg-4 ms-lg-auto">
                        <div class="d-flex gap-2 justify-content-md-end">
                            <button class="btn btn-danger" type="button" onclick="exportarArticulosPDF()">Exportar PDF</button>
                            <button class="btn btn-success" type="button" onclick="exportarArticulosEXCEL()">Exportar Excel</button>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="containerTable">
            <table id="tablaArticulos">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Cantidad</th>
                        <th>Stock alerta</th>
                        <th>Unidad</th>
                        <?php if($_SESSION['rol'] == 1){ ?>
                            <th>Precio Compra</th>
                        <?php } ?>
                        <th>Precio Venta</th>
                        <th>Precio Mínimo</th>
                        <?php if($_SESSION['rol'] == 1 ){?>
                            <th>Proveedor</th>
                            <th>Acciones</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($row > 0){
                    while ($data = $result->fetch_assoc()){
                        $alerta_badge = '';
                        if ($data["Cantidad"] <= 0) {
                            $alerta_badge = ' <span class="badge bg-danger">Agotado</span>';
                        } elseif ($data["Cantidad"] <= $data["Stock_Alerta"]) {
                            $alerta_badge = ' <span class="badge bg-warning text-dark">Bajo</span>';
                        } else if($data["Cantidad"] > $data["Stock_Alerta"]){
                            $alerta_badge = ' <span class="badge bg-success">Bien</span>';
                        }
                    ?>
                    <tr id="fila-<?php echo $data['IdArticulo']; ?>">
                        <td><?php echo $data["IdArticulo"]; ?></td>
                        <td><?php echo $data["nombreA"]; ?></td>
                        <td><?php echo $data["nombreC"] ? $data["nombreC"] : '<span class="text-muted">Sin asignar</span>'; ?></td>
                        <td><?php echo $data["Cantidad"], $alerta_badge; ?></td>
                        <td><?php echo $data["Stock_Alerta"]; ?></td>
                        <td><?php echo $data["Unidad_Presentacion"]; ?></td>
                        <?php if($_SESSION['rol'] == 1){ ?>
                        <td>S/. <?php echo number_format($data["Precio_Compra"],2); ?></td>
                        <?php } ?>
                        <td>S/. <?php echo number_format($data["Precio_Unitario"],2); ?></td>
                        <td>S/. <?php echo number_format($data["Precio_Minimo"],2); ?></td>
                        <?php if($_SESSION['rol'] == 1){ ?>
                        <td><?php echo $data["nombreP"]; ?></td>
                        <td>
                            <div class="btn-group dropend">
                                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Acciones
                                </button>
                                <ul class="dropdown-menu">
                                    <?php if($_SESSION['rol'] == 1){ ?>
                                        <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('editarArticulo', <?php echo $data['IdArticulo']; ?>)">Editar</button></li>
                                        <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('añadirStock', <?php echo $data['IdArticulo']; ?>)">Añadir stock</button></li>
                                        <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('salidaStock', <?php echo $data['IdArticulo']; ?>)">Salida de stock</button></li>
                                    <?php } ?>
                                    <?php if($data['Cantidad'] <= 0){ ?>
                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('eliminarArticulo', <?php echo $data['IdArticulo']; ?>)">Eliminar</button></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </td>
                        <?php } ?>
                    </tr>
                    <?php
                    }
                    }
                    ?>
                </tbody>
            </table>
        </div>


<div id="salidaStock" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Salida de stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <input type="hidden" name="idArticuloSalida" id="idArticuloSalida">
            <div class="mb-3">
                <label for="nombreProductoSalida">Nombre de producto</label>
                <input type="text" name="nombreProductoSalida" id="nombreProductoSalida" disabled>
            </div>
            <div class="mb-3">
                <label for="cantidadActualSalida">Cantidad actual</label>
                <input type="number" name="cantidadActualSalida" id="cantidadActualSalida" disabled>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="cantidadOriginalSalida">Cantidad</label>
                    <input type="number" name="cantidadOriginalSalida" id="cantidadOriginalSalida" step="0.01" min="0.01" placeholder="Ej: 5" oninput="calcularEquivalenteSalida()">
                </div>
                <div class="col-md-6">
                    <label for="unidadSalidaSelect">Unidad</label>
                    <select name="unidadSalidaSelect" id="unidadSalidaSelect" class="form-select form-select-sm" onchange="calcularEquivalenteSalida()">
                        <option value="">(unidad base)</option>
                    </select>
                </div>
            </div>
            <div id="equivalenteSalidaInfo" class="mb-3 small text-muted fw-semibold" style="display:none;"></div>
            <div class="mb-3" style="display:none;">
                <label for="cantidadSalida">Cantidad de salida (Unidad base)</label>
                <input type="number" name="cantidadSalida" id="cantidadSalida" step="0.01">
            </div>
            <div class="mb-3">
                <label for="fechaSalida">Fecha</label>
                <input type="date" name="fechaSalida" id="fechaSalida" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="mb-3">
                <label for="descripcionSalida">Descripción</label>
                <textarea name="descripcionSalida" id="descripcionSalida"></textarea>
            </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="salidaStock()">Confirmar</button>
                <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('salidaStock')">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<div id="eliminarArticulo" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Eliminar artículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="idArticuloeliminar" name="idArticuloeliminar">
                <input type="hidden" id="cantidadEliminar" name="cantidadEliminar">
                <div class="mb-3">
                    <p>¿Está seguro de eliminar el artículo?</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="eliminarArticulo()">Confirmar</button>
                <button type="button" class="btn btn-secondary" onclick="ocultarFormulario('eliminarArticulo')">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<div id="editarArticulo" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Editar artículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="idArticuloeditar" id="idArticuloeditar">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="codigoBarrasEditar">Código de barras</label>
                            <input type="text" name="codigoBarrasEditar" id="codigoBarrasEditar">
                        </div>
                        <div class="mb-3">
                            <label for="nombreProductoEditar">Nombre del artículo</label>
                            <input type="text" name="nombreProductoEditar" id="nombreProductoEditar">
                        </div>
                        <div class="mb-3">
                            <label for="precioCompraEditar">Precio compra</label>
                            <input type="number" name="precioCompraEditar" id="precioCompraEditar" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="precioVentaEditar">Precio venta</label>
                            <input type="number" name="precioVentaEditar" id="precioVentaEditar" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="precioMinimoEditar">Precio mínimo</label>
                            <input type="number" name="precioMinimoEditar" id="precioMinimoEditar" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="stockAlertaEditar">Stock de alerta</label>
                            <input type="number" name="stockAlertaEditar" id="stockAlertaEditar">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="proveedorActual">Proveedor actual</label>
                            <input type="text" id="proveedorActual" name="proveedorActual" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="nuevoProveedor">Nuevo proveedor</label>
                            <select name="nuevoProveedor" id="nuevoProveedor"></select>
                        </div>
                        <div class="mb-3">
                            <label for="categoriaActual">Categoría actual</label>
                            <input type="text" id="categoriaActual" name="categoriaActual" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="nuevaCategoria">Nueva categoría</label>
                            <select name="nuevaCategoria" id="nuevaCategoria"></select>
                        </div>
                        <div class="mb-3">
                            <label for="unidadActual">Unidad actual</label>
                            <input type="text" id="unidadActual" name="unidadActual" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="nuevaUnidad">Nueva unidad de presentación</label>
                            <select name="nuevaUnidad" id="nuevaUnidad" onchange="mostrarOcultarOtraPres()">
                                <option value="">Seleccionar</option>
                                <option value="Und">Unidad (Und)</option>
                                <option value="Saco">Saco (Saco)</option>
                                <option value="Bol">Bolsa (Bol)</option>
                                <option value="Cja">Caja (Cja)</option>
                                <option value="Pap">Paquete (Pap)</option>
                                <option value="Bto">Bulto (Bto)</option>
                                <option value="Rllo">Rollo (Rllo)</option>
                                <option value="Cte">Carrete (Cte)</option>
                                <option value="Bob">Bobina (Bob)</option>
                                <option value="Fdo">Fardo (Fdo)</option>
                                <option value="Jgo">juego (Jgo)</option>
                                <option value="Par">Par (Par)</option>
                                <option value="Doc">Docena (Doc)</option>
                                <option value="Bld">Balde (Bld)</option>
                                <option value="Gal">Galón (Gal)</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="mb-3 d-none" id="contenedorOtraUnidad">
                            <label for="unidadOtro">Otra presentación</label>
                            <input type="text" name="unidadOtro" id="unidadOtro">
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <h6>Unidades variables</h6>
                        <div id="unidadesContainer"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="agregarFilaUnidad()"><i class="fas fa-plus"></i> Agregar unidad</button>
                    </div>
                    <div class="col-md-6">
                        <h6>Descuentos escalonados por cantidad</h6>
                        <div id="descuentosContainer"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="agregarFilaDescuento()"><i class="fas fa-plus"></i> Agregar descuento</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="editarArticulo()">Confirmar</button>
                <button type="button" class="btn btn-secondary" onclick="ocultarFormulario('editarArticulo')">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<div id="añadirStock" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Añadir stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="idArticuloAñadir" id="idArticuloAñadir">
                <div class="mb-3">
                    <label for="nombreProductoAñadir">Nombre de producto</label>
                    <input type="text" name="nombreProductoAñadir" id="nombreProductoAñadir" disabled>
                </div>
                <div class="mb-3">
                    <label for="precio_compraAñadir">Precio de compra</label>
                    <input type="number" name="precio_compraAñadir" id="precio_compraAñadir" step="0.01">
                </div>
                <div class="mb-3">
                    <label for="cantidadActualAñadir">Cantidad actual</label>
                    <input type="number" name="cantidadActualAñadir" id="cantidadActualAñadir" disabled>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="cantidadOriginalAñadir">Cantidad a añadir</label>
                        <input type="number" name="cantidadOriginalAñadir" id="cantidadOriginalAñadir" step="0.01" min="0.01" placeholder="Ej: 10" oninput="calcularEquivalenteAñadir()">
                    </div>
                    <div class="col-md-6">
                        <label for="unidadAñadirSelect">Unidad</label>
                        <select name="unidadAñadirSelect" id="unidadAñadirSelect" class="form-select form-select-sm" onchange="calcularEquivalenteAñadir()">
                            <option value="">(unidad base)</option>
                        </select>
                    </div>
                </div>
                <div id="equivalenteAñadirInfo" class="mb-3 small text-muted fw-semibold" style="display:none;"></div>
                <div class="mb-3" style="display:none;">
                    <label for="cantidadAñadir">Nuevo stock (Unidad de presentación)</label>
                    <input type="number" name="cantidadAñadir" id="cantidadAñadir" step="0.01">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="añadirStock()">Confirmar</button>
                <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('añadirStock')">Cancelar</button>
            </div>
        </div>
    </div>
</div>

        <?php
        renderPaginator($total_records, $results_per_page, $current_page, 'lista_articulos.php');
        ?>
        <div id="paginator"></div>
	</section>
	<?php include "../includes/footer_2.php"; ?>
    <script>
        
        function mostrarOcultarOtraPres() {
            var unidadPresentacion = document.getElementById('nuevaUnidad').value;
            var otraPresContainer = document.getElementById('contenedorOtraUnidad');
            if (unidadPresentacion === 'otro') {
                otraPresContainer.classList.remove('d-none');
                document.getElementById('unidadOtro').focus();
            } else {
                otraPresContainer.classList.add('d-none');
            }
        }
    </script>
</body>
</html>
