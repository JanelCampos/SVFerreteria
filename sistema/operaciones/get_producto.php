<?php 
    include "../../conexion.php";

    $idArticulo = isset($_GET['id']) ? $_GET['id'] : null;

    if ($idArticulo !== null) {
        $query = $conexionDB->prepare("
            SELECT a.*, c.Nombre as nombreCategoria
            FROM articulos a
            LEFT JOIN categorias c ON a.Cod_Categoria = c.IdCategoria
            WHERE a.IdArticulo = ?
        ");

        if ($query) {
            $query->bind_param("i", $idArticulo);

            if ($query->execute()) {
                $result = $query->get_result();
                $data = $result->fetch_assoc();
                $query->close();

                $unidades = [];
                $queryUnidades = $conexionDB->prepare("
                    SELECT IdUnidad, Unidad, FactorEquivalencia, PrecioVenta, PrecioMinimo, EsPredeterminada
                    FROM articulo_unidades
                    WHERE Cod_Articulo = ?
                    ORDER BY EsPredeterminada DESC, IdUnidad ASC
                ");
                if ($queryUnidades) {
                    $queryUnidades->bind_param("i", $idArticulo);
                    if ($queryUnidades->execute()) {
                        $resultUnidades = $queryUnidades->get_result();
                        while ($row = $resultUnidades->fetch_assoc()) {
                            $precioUdM = floatval($row['PrecioVenta'] ?? 0);
                            $precioMinimo = floatval($row['PrecioMinimo'] ?? 0);
                            if ($precioUdM <= 0) {
                                $factor = floatval($row['FactorEquivalencia'] ?? 1);
                                $precioUdM = floatval($data['Precio_Unitario'] ?? 0) * max($factor, 0.0001);
                            }
                            $unidades[] = [
                                'IdUnidad' => intval($row['IdUnidad']),
                                'Unidad'   => $row['Unidad'],
                                'Factor'   => floatval($row['FactorEquivalencia']),
                                'PrecioVenta' => $precioUdM,
                                'PrecioMinimo' => $precioMinimo,
                                'EsPredeterminada' => intval($row['EsPredeterminada']),
                                'Predeterminada' => intval($row['EsPredeterminada'])
                            ];
                        }
                    }
                    $queryUnidades->close();
                }

                if (empty($unidades)) {
                    $precioBase = floatval($data['Precio_Unitario'] ?? 0);
                    $unidades[] = [
                        'IdUnidad' => 0,
                        'Unidad' => !empty($data['Unidad_Base']) ? $data['Unidad_Base'] : 'unidad',
                        'Factor' => 1.00,
                        'PrecioVenta' => $precioBase,
                        'PrecioMinimo' => $precioBase * 0.5,
                        'EsPredeterminada' => 1,
                        'Predeterminada' => 1
                    ];
                }

                $descuentos = [];
                $queryDescuentos = $conexionDB->prepare("
                    SELECT IdDescuento, CantidadMinima, PorcentajeDescuento
                    FROM articulo_descuentos_cantidad
                    WHERE Cod_Articulo = ?
                    ORDER BY CantidadMinima ASC
                ");
                if ($queryDescuentos) {
                    $queryDescuentos->bind_param("i", $idArticulo);
                    if ($queryDescuentos->execute()) {
                        $resultDescuentos = $queryDescuentos->get_result();
                        while ($row = $resultDescuentos->fetch_assoc()) {
                            $cantMin = floatval($row['CantidadMinima']);
                            $porc = floatval($row['PorcentajeDescuento']);
                            $descuentos[] = [
                                'IdDescuento' => intval($row['IdDescuento']),
                                'CantidadMinima' => $cantMin,
                                'CantMinima'   => $cantMin,
                                'PorcentajeDescuento' => $porc,
                                'Porcentaje'   => $porc
                            ];
                        }
                    }
                    $queryDescuentos->close();
                }

                header('Content-Type: application/json');
                echo json_encode([
                    'resultado' => true,
                    'datos' => $data,
                    'unidades' => $unidades,
                    'descuentos' => $descuentos
                ]);

            } else {
                header('Content-Type: application/json');
                echo json_encode(["resultado" => false, "error" => "Error en la ejecución de la consulta"]);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(["resultado" => false, "error" => "Error en la preparación de la consulta"]);
        }

        $conexionDB->close();
    } else {
        header('Content-Type: application/json');
        echo json_encode(["resultado" => false, "error" => "idArticulo no especificado"]);
    }
?>