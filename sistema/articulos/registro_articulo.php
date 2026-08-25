<?php

    include "../../conexion.php";
    session_start();

    if (empty($_SESSION['active']) || intval($_SESSION['rol']) !== 1) {
        header("Location: ../../index.php");
        exit;
    }

    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && stripos($contentType, 'application/json') !== false) {
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true);
        if (!is_array($body)) {
            echo json_encode(['resultado' => false, 'mensaje' => 'Cuerpo JSON inválido']);
            exit;
        }

        $codigoBarras = trim($body['codigoBarras'] ?? '');
        $nombre = trim($body['nombre'] ?? '');
        $categoria = intval($body['categoria'] ?? 0);
        $proveedor = intval($body['proveedor'] ?? 0);
        $cantidad = floatval($body['cantidad'] ?? 0);
        $stockAlerta = !empty($body['stockAlerta']) ? intval($body['stockAlerta']) : 5;
        $precioCompra = floatval($body['precioCompra'] ?? 0);
        $precioVenta = floatval($body['precioVenta'] ?? 0);
        $precioMinimo = !empty($body['precioMinimo']) ? floatval($body['precioMinimo']) : 0;
        $unidadPresentacion = !empty($body['unidadPresentacion']) ? trim($body['unidadPresentacion']) : 'unidad';
        $unidades = isset($body['unidades']) && is_array($body['unidades']) ? $body['unidades'] : [];
        $descuentos = isset($body['descuentos']) && is_array($body['descuentos']) ? $body['descuentos'] : [];

        if ($nombre === '' || $categoria <= 0 || $proveedor <= 0 || $precioVenta <= 0) {
            echo json_encode(['resultado' => false, 'mensaje' => 'Complete nombre, categoría, proveedor y precio de venta']);
            exit;
        }

        function tieneArticuloPorNombre($db, $nom, $excluir = 0) {
            $c = 0;
            $q = $db->prepare("SELECT COUNT(*) FROM articulos WHERE Nombre = ? AND IdArticulo <> ?");
            $q->bind_param("si", $nom, $excluir);
            $q->execute();
            $q->bind_result($c);
            $q->fetch();
            $q->close();
            return $c > 0;
        }
        function tieneArticuloPorCodigo($db, $cod, $excluir = 0) {
            $c = 0;
            if ($cod === '') return false;
            $q = $db->prepare("SELECT COUNT(*) FROM articulos WHERE codigoBarras = ? AND IdArticulo <> ?");
            $q->bind_param("si", $cod, $excluir);
            $q->execute();
            $q->bind_result($c);
            $q->fetch();
            $q->close();
            return $c > 0;
        }

        if (tieneArticuloPorNombre($conexionDB, $nombre, 0)) {
            echo json_encode(['resultado' => false, 'mensaje' => 'El nombre del artículo ya existe']);
            exit;
        }
        if ($codigoBarras !== '' && tieneArticuloPorCodigo($conexionDB, $codigoBarras, 0)) {
            echo json_encode(['resultado' => false, 'mensaje' => 'El código de barras ya existe']);
            exit;
        }

        $estado = 1;
        $ins = $conexionDB->prepare("INSERT INTO articulos (codigoBarras, Nombre, Cod_Categoria, Cantidad, Stock_Alerta, Precio_Compra, Precio_Unitario, Precio_Minimo, Unidad_Presentacion, Cod_Proveedor) VALUES (?,?,?,?,?,?,?,?,?,?)");
        if (!$ins) {
            echo json_encode(['resultado' => false, 'mensaje' => 'Error al preparar el INSERT: ' . $conexionDB->error]);
            exit;
        }
        $ins->bind_param("ssiiddddsi", $codigoBarras, $nombre, $categoria, $cantidad, $stockAlerta, $precioCompra, $precioVenta, $precioMinimo, $unidadPresentacion, $proveedor);
        if (!$ins->execute()) {
            echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo guardar el artículo: ' . $ins->error]);
            $ins->close();
            exit;
        }
        $idArticulo = $conexionDB->insert_id;
        $ins->close();

        $hayUdMPred = false;
        if (is_array($unidades) && count($unidades) > 0) {
            $insU = $conexionDB->prepare("INSERT INTO articulo_unidades (Cod_Articulo, Unidad, FactorEquivalencia, PrecioVenta, PrecioMinimo, EsPredeterminada) VALUES (?,?,?,?,?,?)");
            $countPred = 0;
            foreach ($unidades as $u) {
                $u_unidad = trim($u['Unidad'] ?? '');
                $u_factor = floatval($u['FactorEquivalencia'] ?? 0);
                $u_precio = floatval($u['PrecioVenta'] ?? 0);
                $u_precioMinimo = floatval($u['PrecioMinimo'] ?? 0);
                $u_pred = (!empty($u['EsPredeterminada']) && $countPred === 0) ? 1 : 0;
                if ($u_pred) { $hayUdMPred = true; $countPred++; }
                if ($u_unidad !== '' && $u_factor > 0) {
                    if ($u_precio <= 0) $u_precio = $precioVenta * max($u_factor, 0.0001);
                    $insU->bind_param("isdddi", $idArticulo, $u_unidad, $u_factor, $u_precio, $u_precioMinimo, $u_pred);
                    $insU->execute();
                }
            }
            $insU->close();
        }

        if (!$hayUdMPred) {
            $insFallback = $conexionDB->prepare("INSERT INTO articulo_unidades (Cod_Articulo, Unidad, FactorEquivalencia, PrecioVenta, PrecioMinimo, EsPredeterminada) VALUES (?,?,?,?,?,1)");
            $factorFb = 1.0;
            $insFallback->bind_param("isidd", $idArticulo, $unidadPresentacion, $factorFb, $precioVenta, $precioMinimo);
            $insFallback->execute();
            $insFallback->close();
        }

        if (is_array($descuentos) && count($descuentos) > 0) {
            $insD = $conexionDB->prepare("INSERT INTO articulo_descuentos_cantidad (Cod_Articulo, CantidadMinima, PorcentajeDescuento) VALUES (?,?,?)");
            foreach ($descuentos as $d) {
                $d_cant = intval($d['CantidadMinima'] ?? 0);
                $d_porc = floatval($d['PorcentajeDescuento'] ?? 0);
                if ($d_cant > 0 && $d_porc > 0 && $d_porc <= 100) {
                    $insD->bind_param("iid", $idArticulo, $d_cant, $d_porc);
                    $insD->execute();
                }
            }
            $insD->close();
        }

        echo json_encode(['resultado' => true, 'idArticulo' => $idArticulo, 'mensaje' => 'Artículo guardado correctamente']);
        $conexionDB->close();
        exit;
    }

    $alert = '';
    if (!empty($_POST) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['proveedor']) || empty($_POST['categoria']) || empty($_POST['nombre']) ||
           empty($_POST['cantidad']) || empty($_POST['precio_unitario']) ) {
               $alert = '<p class="alert alert-danger">Por favor, complete todos los campos.</p>';
        }
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
	<section id="container">

        <div class="form_register">
            <h1>Registro de Artículo</h1>
            <hr>
            <div><?php echo isset($alert) ? $alert : ''; ?></div>

            <form id="formNuevoArticulo" method="post" onsubmit="event.preventDefault(); guardarNuevoArticulo();">
                <input type="hidden" name="rol" id="rol" data-rol="<?php echo isset($_SESSION['rol']) ? intval($_SESSION['rol']) : 0; ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="codigoBarras" class="form-label">Código de barras</label>
                        <input type="text" name="codigoBarras" id="codigoBarras" class="form-control form-control-sm" placeholder="Código de barras">
                    </div>
                    <div class="col-md-3">
                        <label for="nombre" class="form-label">Nombre del artículo</label>
                        <input type="text" name="nombre" id="nombre" class="form-control form-control-sm" placeholder="Nombre del artículo" required>
                    </div>
                    <div class="col-md-3">
                        <label for="proveedor" class="form-label">Proveedor</label>
                        <?php
                            $query_proveedor = mysqli_query($conexionDB,"SELECT IdProveedor, Nombre FROM proveedores ORDER BY Nombre ASC");
                            $result_proveedor = mysqli_num_rows($query_proveedor);
                        ?>
                        <select name="proveedor" id="proveedor" class="form-select form-select-sm" required>
                            <option value="0">Seleccionar</option>
                        <?php
                            if($result_proveedor > 0){
                                while ($proveedor = mysqli_fetch_array($query_proveedor)){
                        ?>
                            <option value="<?php echo $proveedor['IdProveedor']; ?>"><?php echo $proveedor['Nombre']; ?></option>
                        <?php
                                }
                            }
                        ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <?php
                            $query_categoria = mysqli_query($conexionDB,"SELECT IdCategoria, Nombre FROM categorias WHERE Estado = 1 ORDER BY Nombre ASC");
                            $result_categoria = mysqli_num_rows($query_categoria);
                        ?>
                        <select name="categoria" id="categoria" class="form-select form-select-sm" required>
                            <option value="0">Seleccionar</option>
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
                    <div class="col-md-2">
                        <label for="cantidad" class="form-label">Cantidad</label>
                        <input type="number" name="cantidad" id="cantidad" class="form-control form-control-sm" placeholder="Cantidad" required min="0">
                    </div>
                    <div class="col-md-2">
                        <label for="stock_alerta" class="form-label">Stock alerta</label>
                        <input type="number" name="stock_alerta" id="stock_alerta" class="form-control form-control-sm" placeholder="Stock mínimo" value="5" min="0">
                    </div>
                    <div class="col-md-2">
                        <label for="precio_compra" class="form-label">Precio compra</label>
                        <input type="number" step="0.01" name="precio_compra" id="precio_compra" class="form-control form-control-sm" placeholder="Precio compra" min="0">
                    </div>
                    <div class="col-md-2">
                        <label for="precio_unitario" class="form-label">Precio venta</label>
                        <input type="number" step="0.01" name="precio_unitario" id="precio_unitario" class="form-control form-control-sm" placeholder="Precio venta base" required min="0">
                    </div>
                    <div class="col-md-2">
                        <label for="precio_minimo" class="form-label">Precio mínimo</label>
                        <input type="number" step="0.01" name="precio_minimo" id="precio_minimo" class="form-control form-control-sm" placeholder="Precio mínimo" min="0">
                    </div>
                    <div class="col-md-2">
                        <label for="unidad_presentacion" class="form-label">Unidad de presentración</label>
                        <select name="unidad_presentacion" id="unidad_presentacion" class="form-select form-select-sm" onchange="mostrarOcultarOtraPres()">
                            <option value="unidad">Unidad</option>
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
                    <div class="col-md-2 d-none" id="contenedorOtraPres">
                        <label for="otraPres" class="form-label">Otra presentación</label>
                        <input type="text" name="otraPres" id="otraPres" class="form-control form-control-sm" placeholder="otra presentación">
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                <span class="fw-semibold small"><i class="fa-solid fa-cubes"></i> Unidades equivalentes</span>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarFilaUnidad()"><i class="fa-solid fa-plus"></i> Agregar unidad</button>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 mb-2 px-2">
                                    <div class="col-3 small text-muted">Unidad Base</div>
                                    <div class="col-2 small text-muted">Equivalencia</div>
                                    <div class="col-2 small text-muted">Precio venta</div>
                                    <div class="col-2 small text-muted">Precio mínimo</div>
                                    <div class="col-1 small text-muted text-center">Pred.</div>
                                    <div class="col-1"></div>
                                </div>
                                <div id="unidadesContainer"></div>
                                <div class="small text-muted mt-1 fst-italic">Ej: saco 50kg → Unidad base = "KG", Equivalencia = 50, Precio venta = S/. 5.00, Precio mínimo = S/. 3.00.</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                <span class="fw-semibold small"><i class="fa-solid fa-percent"></i> Descuentos escalonados (en unidad base)</span>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarFilaDescuento()"><i class="fa-solid fa-plus"></i> Agregar escala</button>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 mb-2 px-2">
                                    <div class="col-5 small text-muted">Cantidad mínima (un. base)</div>
                                    <div class="col-5 small text-muted">% Descuento</div>
                                    <div class="col-2"></div>
                                </div>
                                <div id="descuentosContainer"></div>
                                <div class="small text-muted mt-1 fst-italic">Se aplica el % de la escala más alta alcanzada. Umbrales en UNIDAD BASE (ej: si unidad base = kg, 10 = 10 kg acumulados).</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-between align-items-center mt-4">
                    <div class="small text-muted">Los precios del detalle de venta se calculan sobre la UDM seleccionada, con dto proporcional a la cantidad en unidad base.</div>
                    <div>
                        <a href="lista_articulos.php" class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-minus-circle"></i> Cancelar</a>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="far fa-save"></i> Guardar Artículo</button>
                    </div>
                </div>
            </form>

        </div>

	</section>
	<?php include "../includes/footer_2.php"; ?>
    <?php mysqli_close($conexionDB); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof renderFilasUnidades === 'function') renderFilasUnidades([]);
            if (typeof renderFilasDescuentos === 'function') renderFilasDescuentos([]);
        });

        function mostrarOcultarOtraPres() {
            var unidadPresentacion = document.getElementById('unidad_presentacion').value;
            var otraPresContainer = document.getElementById('contenedorOtraPres');
            if (unidadPresentacion === 'otro') {
                otraPresContainer.classList.remove('d-none');
                document.getElementById('otraPres').focus();
            } else {
                otraPresContainer.classList.add('d-none');
            }
        }
    </script>
</body>
</html>
