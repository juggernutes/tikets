<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($ALLOW_PUBLIC) && !isset($_SESSION['login_id'])) {
    header("Location: ../public/index.php");
    exit;
}

//cargar la conexion a la base de datos
require_once __DIR__ . '/../config/db_connection.php';

//cargar los modelos
require_once __DIR__ . '/../modelsPedido/articuloModel.php';
require_once __DIR__ . '/../modelsPedido/pedidoModel.php';
require_once __DIR__ . '/../modelsPedido/unidadOperacionalModel.php';

//cargar los controladores
require_once __DIR__ . '/../controllerPedido/articuloController.php';
require_once __DIR__ . '/../controllerPedido/pedidoController.php';
require_once __DIR__ . '/../controllerPedido/unidadOperacionalController.php';

//crear instancias de los controladores

$articuloController = new ArticuloController($conn);
$pedidoController = new PedidoController($conn);
$unidadOperacionalController = new UnidadOperacionalController($conn);

if (isset($_GET['action'])) {
    switch ($_GET['action'] ?? '') {
        case 'guardar':
            /*========================================= GUARDAR PEDIDO =========================================*/
            header('Content-Type: application/json; charset=utf-8');
            date_default_timezone_set('America/Tijuana');

            try {
                // 1) Leer cuerpo crudo
                $raw = file_get_contents('php://input');

                // 2) Decodificar JSON con manejo de error
                try {
                    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    http_response_code(400);
                    echo json_encode([
                        'ok'    => false,
                        'error' => 'JSON inválido',
                        'msg'   => $e->getMessage()
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // 3) Validar estructura esperada
                $encabezado = $data['header'] ?? null;
                $contenido  = $data['items']  ?? null;

                if (!is_array($encabezado) || !is_array($contenido) || count($contenido) === 0) {
                    http_response_code(400);
                    echo json_encode([
                        'ok'    => false,
                        'error' => 'Payload inválido: se requieren "header" e "items" con al menos 1 elemento'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // 4) Armar datos del encabezado
                $now      = new DateTime('now', new DateTimeZone('America/Tijuana'));
                $mapDia   = [1 => 'lu', 2 => 'ma', 3 => 'mi', 4 => 'ju', 5 => 'vi', 6 => 'sa', 7 => 'do'];

                $capacidadUV = (int)($encabezado['ID_CAPUV'] ?? 0);
                $unidadOpe   = (int)($encabezado['ID_UNIDAD'] ?? 0);
                $supervisor  = (int)($encabezado['ID_SUPERVISOR_UO'] ?? 0);
                $almacen     = (int)($encabezado['ID_ALMACEN_UO'] ?? 0);
                $registros   = (int)($encabezado['Registro'] ?? 0);
                $dia         = $mapDia[(int)$now->format('N')] ?? null;
                $semana      = (int)$now->format('W');
                $volumen     = (float)($encabezado['Volumen'] ?? 0);
                $obser       = (string)($encabezado['Obser'] ?? '');
                $usuario     = $_SESSION['login_id'] ?? 0;

                // 5) Log rápido de encabezado
                file_put_contents(
                    __DIR__ . '/debug_pedido.log',
                    date('Y-m-d H:i:s')
                        . " | ENCAB: $capacidadUV, $unidadOpe, $supervisor, $almacen, $registros, $dia, $semana, $volumen, $obser, $usuario\n",
                    FILE_APPEND
                );

                // 6) Crear encabezado (OJO: el modelo regresa IdPedido y Folio DIRECTOS)
                $response = $pedidoController->guardarPedido(
                    $capacidadUV,
                    $unidadOpe,
                    $supervisor,
                    $almacen,
                    $registros,
                    $dia,
                    $semana,
                    $volumen,
                    $obser,
                    $usuario
                );

                // Esperamos: ['ok'=>bool,'msg'=>string,'IdPedido'=>...,'Folio'=>...]
                $idpedido = (int)($response['IdPedido'] ?? 0);
                $folio    = (string)($response['Folio']   ?? '');

                file_put_contents(
                    __DIR__ . '/debug_pedido_detalle.log',
                    date('Y-m-d H:i:s')
                        . " | ENCABEZADO GUARDADO: IDPEDIDO=$idpedido | FOLIO=$folio | RESP: "
                        . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n",
                    FILE_APPEND
                );

                if (empty($response['ok']) || $idpedido <= 0 || $folio === '') {
                    http_response_code(500);
                    echo json_encode([
                        'ok'    => false,
                        'error' => 'Error al guardar encabezado',
                        'msg'   => $response['msg'] ?? 'Encabezado sin IdPedido o Folio válido'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // 7) Guardar detalles
                $registro   = 0;
                $okDetalles = 0;

                foreach ($contenido as $item) {
                    $articulo  = (string)($item['idArticulo'] ?? '');
                    $cantidad  = (int)($item['cantidad']   ?? 0);
                    $volArt    = (float)($item['volArt']   ?? 0);
                    $idUsuario = (int)($encabezado['ID_USUARIO'] ?? 0);
                    $registro++;

                    if ($articulo === '' || $cantidad <= 0) {
                        file_put_contents(
                            __DIR__ . '/debug_pedido_detalles.log',
                            date('Y-m-d H:i:s')
                                . " | WARN_ITEM | FOLIO: $folio | IDPEDIDO: $idpedido | ARTICULO: $articulo | CANT: $cantidad\n",
                            FILE_APPEND
                        );
                        continue;
                    }

                    $ok = $pedidoController->guardarDetallePedido(
                        $idpedido,
                        $folio,
                        $articulo,
                        $registro,
                        $cantidad,
                        $volArt,
                        $idUsuario
                    );

                    if ($ok && (!is_array($ok) || ($ok['ok'] ?? true))) {
                        $okDetalles++;
                    } else {
                        file_put_contents(
                            __DIR__ . '/debug_pedido.log',
                            date('Y-m-d H:i:s')
                                . " | ERROR_DETALLE | FOLIO: $folio | IDPEDIDO: $idpedido | ARTICULO: $articulo\n",
                            FILE_APPEND
                        );
                    }
                }

                // 8) Resumen en log
                file_put_contents(
                    __DIR__ . '/debug_pedido.log',
                    date('Y-m-d H:i:s')
                        . " | RESUMEN_GUARDAR | FOLIO: $folio | IDPEDIDO: $idpedido | ENVIADOS: $registro | OK: $okDetalles\n",
                    FILE_APPEND
                );

                // 9) Respuesta al front (JSON LIMPIO)
                echo json_encode([
                    'ok'         => true,
                    'msg'        => 'Pedido guardado correctamente',
                    'IdPedido'   => $idpedido,
                    'Folio'      => $folio,
                    'Registros'  => $registro,
                    'DetallesOK' => $okDetalles
                ], JSON_UNESCAPED_UNICODE);
                exit;
            } catch (Throwable $e) {
                http_response_code(500);
                echo json_encode([
                    'ok'    => false,
                    'error' => 'Excepción interna',
                    'msg'   => $e->getMessage()
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            break;



        case 'surtir':

            /*========================================= surtir =========================================*/
            // Cambia estado a SURTIDO (IdEstado=3)
            $usuario      = $_SESSION['login_id'] ?? 0;
            $folio        = $_POST['folio']       ?? '';
            $idpedido     = intval($_POST['idPedido'] ?? 0);
            $peso_pedido  = floatval($_POST['peso_pedido'] ?? 0);

            // Cantidades autorizadas [idArticulo => cantidad]
            $cantidades   = $_POST['cant_aut']    ?? [];

            // Volumen por renglón [idArticulo => volumen]
            $pesoTotal    = $_POST['peso_total']  ?? [];

            if ($folio === '') {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Folio requerido']);
                exit;
            }
            file_put_contents(
                __DIR__ . '/debug_pedido_surtido.log',
                "\n\n==========================\n" .
                    date('Y-m-d H:i:s') . " | DATA RECIBIDA EN surtir\n" .
                    "FOLIO: "          . json_encode($folio, JSON_UNESCAPED_UNICODE) . "\n" .
                    "IDPEDIDO: "       . json_encode($idpedido, JSON_UNESCAPED_UNICODE) . "\n" .
                    "USUARIO: "        . json_encode($usuario, JSON_UNESCAPED_UNICODE) . "\n" .
                    "PESO_PEDIDO: "    . json_encode($peso_pedido, JSON_UNESCAPED_UNICODE) . "\n" .
                    "CANTIDADES: "     . json_encode($cantidades, JSON_UNESCAPED_UNICODE) . "\n" .
                    "PESO_TOTAL: "     . json_encode($pesoTotal, JSON_UNESCAPED_UNICODE) . "\n" .
                    "RAW_POST: "       . json_encode($_POST, JSON_UNESCAPED_UNICODE) . "\n" .
                    "==========================\n",
                FILE_APPEND
            );

            // Ejecuta SP y recoge datos
            $result = $pedidoController->csv_surtir($folio, $usuario);
            $pedidoController->marcarSurtidoPorFolio($idpedido, $usuario, $peso_pedido);
            // Contadores
            $totalEnviados = 0;   // renglones que intentamos actualizar
            $totalOK       = 0;   // renglones que sí se actualizaron bien
            foreach ($cantidades as $idArticulo => $cantAut) {
                $totalEnviados++;

                $idArticulo = intval($idArticulo);
                $cantAut    = intval($cantAut);

                if ($cantAut < 0) {
                    $cantAut = 0;
                }

                $volrenglon = isset($pesoTotal[$idArticulo])
                    ? floatval($pesoTotal[$idArticulo])
                    : 0.0;



                // <<< AQUÍ VA LA ACTUALIZACIÓN DEL DETALLE >>>
                $okDet = $pedidoController->surtirDetallePedido(
                    $idpedido,
                    $idArticulo,
                    $cantAut,
                    $volrenglon,
                    $usuario
                );

                if ($okDet) {
                    $totalOK++;
                } else {
                    file_put_contents(
                        __DIR__ . '/debug_pedido_autorizado.log',
                        date('Y-m-d H:i:s')
                            . " | ERROR_DETALLE | IDPEDIDO: " . json_encode($idpedido, JSON_UNESCAPED_UNICODE)
                            . " | ARTICULO: " . json_encode($idArticulo, JSON_UNESCAPED_UNICODE)
                            . "\n",
                        FILE_APPEND
                    );
                }
            }

            // Headers para descarga CSV (sin ningún echo antes)
            $filename = 'pedido-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $folio) . '.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $out = fopen('php://output', 'w');

            // BOM UTF-8 para Excel
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Cabecera del CSV (coincide con alias del SP)
            fputcsv($out, ['REGISTRO', 'ARTICULO', 'PIEZAS', 'PESO_UNIT', 'UNIDAD']);

            // Filas
            foreach ($result as $row) {
                fputcsv($out, [
                    $row['REGISTRO']   ?? '',
                    $row['ARTICULO']   ?? '',
                    $row['PIEZAS']     ?? '',
                    $row['PESO_UNIT']  ?? '',
                    substr($row['UNIDAD'] ?? '', 0, 5)
                ]);
            }

            fclose($out);
            exit;


        case 'autorizar_pedido':

            /*========================================= autorizar_pedido =========================================*/
            $usuario      = $_SESSION['login_id'] ?? 0;
            $folio        = $_POST['folio']       ?? '';
            $idpedido     = intval($_POST['idPedido'] ?? 0);
            $peso_pedido  = floatval($_POST['peso_pedido'] ?? 0);

            // Cantidades autorizadas [idArticulo => cantidad]
            $cantidades   = $_POST['cant_aut']    ?? [];

            // Volumen por renglón [idArticulo => volumen]
            $pesoTotal    = $_POST['peso_total']  ?? [];

            // ==== DEBUG GENERAL: ver TODO lo que llega en el POST ====
            file_put_contents(
                __DIR__ . '/debug_pedido_autorizado.log',
                date('Y-m-d H:i:s')
                    . " | RAW_POST: " . json_encode($_POST, JSON_UNESCAPED_UNICODE)
                    . "\n",
                FILE_APPEND
            );

            // Validaciones básicas
            if ($idpedido <= 0 || $folio === '' || !is_array($cantidades) || empty($cantidades)) {
                file_put_contents(
                    __DIR__ . '/debug_pedido_autorizado.log',
                    "\n\n==========================\n" .
                        date('Y-m-d H:i:s') . " | DATA RECIBIDA EN surtir\n" .
                        "FOLIO: "          . json_encode($folio, JSON_UNESCAPED_UNICODE) . "\n" .
                        "IDPEDIDO: "       . json_encode($idpedido, JSON_UNESCAPED_UNICODE) . "\n" .
                        "USUARIO: "        . json_encode($usuario, JSON_UNESCAPED_UNICODE) . "\n" .
                        "PESO_PEDIDO: "    . json_encode($peso_pedido, JSON_UNESCAPED_UNICODE) . "\n" .
                        "CANTIDADES: "     . json_encode($cantidades, JSON_UNESCAPED_UNICODE) . "\n" .
                        "PESO_TOTAL: "     . json_encode($pesoTotal, JSON_UNESCAPED_UNICODE) . "\n" .
                        "RAW_POST: "       . json_encode($_POST, JSON_UNESCAPED_UNICODE) . "\n" .
                        "==========================\n",
                    FILE_APPEND
                );
                header('Location: ../views/dashboardPedidos.php?error=autorizar');
                exit;
            }

            // Contadores
            $totalEnviados = 0;   // renglones que intentamos actualizar
            $totalOK       = 0;   // renglones que sí se actualizaron bien

            file_put_contents(
                __DIR__ . '/debug_pedido_autorizado.log',
                "\n\n==========================\n" .
                    date('Y-m-d H:i:s') . " | DATA RECIBIDA EN surtir\n" .
                    "FOLIO: "          . json_encode($folio, JSON_UNESCAPED_UNICODE) . "\n" .
                    "IDPEDIDO: "       . json_encode($idpedido, JSON_UNESCAPED_UNICODE) . "\n" .
                    "USUARIO: "        . json_encode($usuario, JSON_UNESCAPED_UNICODE) . "\n" .
                    "PESO_PEDIDO: "    . json_encode($peso_pedido, JSON_UNESCAPED_UNICODE) . "\n" .
                    "CANTIDADES: "     . json_encode($cantidades, JSON_UNESCAPED_UNICODE) . "\n" .
                    "PESO_TOTAL: "     . json_encode($pesoTotal, JSON_UNESCAPED_UNICODE) . "\n" .
                    "RAW_POST: "       . json_encode($_POST, JSON_UNESCAPED_UNICODE) . "\n" .
                    "==========================\n",
                FILE_APPEND
            );

            // 1) Actualizar cada renglón con cantidad y volumen por renglón
            foreach ($cantidades as $idArticulo => $cantAut) {
                $totalEnviados++;

                $idArticulo = intval($idArticulo);
                $cantAut    = intval($cantAut);

                if ($cantAut < 0) {
                    $cantAut = 0;
                }

                $volrenglon = isset($pesoTotal[$idArticulo])
                    ? floatval($pesoTotal[$idArticulo])
                    : 0.0;



                // <<< AQUÍ VA LA ACTUALIZACIÓN DEL DETALLE >>>
                $okDet = $pedidoController->actualizarDetallePedido(
                    $idpedido,
                    $idArticulo,
                    $cantAut,
                    $volrenglon,
                    $usuario
                );

                if ($okDet) {
                    $totalOK++;
                } else {
                    file_put_contents(
                        __DIR__ . '/debug_pedido_autorizado.log',
                        date('Y-m-d H:i:s')
                            . " | ERROR_DETALLE | IDPEDIDO: " . json_encode($idpedido, JSON_UNESCAPED_UNICODE)
                            . " | ARTICULO: " . json_encode($idArticulo, JSON_UNESCAPED_UNICODE)
                            . "\n",
                        FILE_APPEND
                    );
                }
            }

            // 2) Validar que todos los envíos fueron correctos
            file_put_contents(
                __DIR__ . '/debug_pedido_autorizado.log',
                date('Y-m-d H:i:s')
                    . " | RESUMEN_DETALLES | ENVIADOS: {$totalEnviados} | OK: {$totalOK}"
                    . "\n",
                FILE_APPEND
            );

            // Solo autorizamos si TODOS los renglones se actualizaron bien
            if ($totalEnviados > 0 && $totalOK === $totalEnviados) {

                $okAprovar = $pedidoController->aprovarPedido($peso_pedido, $idpedido, $usuario);

                if (!$okAprovar) {
                    file_put_contents(
                        __DIR__ . '/debug_pedido_autorizado.log',
                        date('Y-m-d H:i:s')
                            . " | ERROR_APROBAR | FOLIO: " . json_encode($folio, JSON_UNESCAPED_UNICODE)
                            . " | IDPEDIDO: " . json_encode($idpedido, JSON_UNESCAPED_UNICODE)
                            . " | VOLUMEN_TOTAL: " . json_encode($peso_pedido, JSON_UNESCAPED_UNICODE)
                            . "\n",
                        FILE_APPEND
                    );
                    header('Location: ../views/dashboardPedidos.php?error=aprobar');
                    exit;
                }

                // Si todo salió bien, puedes mandar éxito
                header('Location: ../views/dashboardPedidos.php?ok=autorizar');
                exit;
            } else {
                // Hubo al menos un detalle que falló o no hay detalles
                file_put_contents(
                    __DIR__ . '/debug_pedido_autorizado.log',
                    date('Y-m-d H:i:s')
                        . " | ERROR_DETALLE_GLOBAL | ENVIADOS: {$totalEnviados} | OK: {$totalOK}"
                        . "\n",
                    FILE_APPEND
                );
                header('Location: ../views/dashboardPedidos.php?error=detalle');
                exit;
            }

            break;


        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no reconocida']);
            exit;
    }
}
