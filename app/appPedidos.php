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
            header('Content-Type: application/json; charset=utf-8');
            date_default_timezone_set('America/Tijuana');

            // 1) Validar sesión
            if (empty($_SESSION['login_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'No autenticado']);
                exit;
            }

            // 2) Validar método
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
                exit;
            }

            // 3) Validar Content-Type (opcional pero recomendable)
            $ct = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
            if (stripos($ct, 'application/json') === false) {
                // No bloquea, pero avisa en el log
                file_put_contents(__DIR__ . '/debug_pedido.log', date('Y-m-d H:i:s') . " | WARN: Content-Type no es JSON: $ct\n", FILE_APPEND);
            }

            // 4) Leer cuerpo
            $raw = file_get_contents('php://input');
            if ($raw === '' || $raw === false) {
                http_response_code(400);
                echo json_encode(['error' => 'Cuerpo vacío']);
                exit;
            }

            // 5) Decodificar JSON con flags seguros
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

            // 6) Validar estructura esperada
            $encabezado = $data['header'] ?? null;
            $contenido  = $data['items']  ?? null;

            if (!is_array($encabezado) || !is_array($contenido) || count($contenido) === 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Payload inválido: se requieren "header" (objeto) e "items" (arreglo no vacío)']);
                exit;
            }

            // 7) Log
            file_put_contents(
                __DIR__ . '/debug_pedido.log',
                date('Y-m-d H:i:s')
                    . " | ENCABEZADO: " . json_encode($encabezado, JSON_UNESCAPED_UNICODE)
                    . " | ITEMS: " . json_encode($contenido, JSON_UNESCAPED_UNICODE) . "\n",
                FILE_APPEND
            );

            // 8) Ejecutar
            try {
                $response = $pedidoController->guardarPedido($encabezado, $contenido);

                // Normaliza respuesta y código HTTP
                if (is_array($response) && isset($response['error'])) {
                    // El controlador reportó error
                    http_response_code(500);
                } else {
                    // Éxito
                    http_response_code(200);
                }

                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit;
            } catch (Throwable $e) {
                // Log del error
                file_put_contents(
                    __DIR__ . '/debug_pedido.log',
                    date('Y-m-d H:i:s') . " | EXCEPTION: " . $e->getMessage() . "\n",
                    FILE_APPEND
                );

                http_response_code(500);
                echo json_encode(['error' => 'Error interno al guardar el pedido']);
                exit;
            }

        case 'surtir':

            // Cambia estado a SURTIDO (IdEstado=3)
            $payload = json_decode(file_get_contents('php://input'), true) ?? [];
            $folio   = trim($payload['folio'] ?? '');
            if ($folio === '') {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Folio requerido']);
                exit;
            }

            $pedidoController = new PedidoController($conn);
            // Implementa el método en tu controller/model o llama tu SP WHEN 10
            $ok = $pedidoController->marcarSurtidoPorFolio($folio, $_SESSION['login_id'] ?? null);

            if ($ok) {
                echo json_encode(['ok' => true, 'folio' => $folio]);
            } else {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'No se pudo actualizar el estado']);
            }
            exit;

        case 'csv':
            // Descargar CSV del pedido
            $folio = trim($_GET['folio'] ?? '');
            if ($folio === '') {
                http_response_code(400);
                echo 'Folio requerido';
                exit;
            }

            $pedidoController = new PedidoController($conn);

            // Encabezado y detalle (ajusta a tus métodos reales)
            $header  = $pedidoController->getPedidoByFolio($folio);           // Debe devolver 1 fila
            $detalle = $pedidoController->getDetallePedido($folio);           // Array de renglones

            if (!$header) {
                http_response_code(404);
                echo 'Pedido no encontrado';
                exit;
            }

            // Headers para descarga
            $filename = 'pedido-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $folio) . '.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $out = fopen('php://output', 'w');

            // BOM UTF-8 para Excel
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Cabecera del CSV
            fputcsv($out, [
                'Folio',
                'Fecha',
                'UnidadVenta',
                'Supervisor',
                'Registros',
                'IdArticulo',
                'Articulo',
                'Cantidad',
                'PesoUnit',
                'PesoTotal'
            ]);

            // Volcar filas (una por detalle)
            $folioCsv   = $header['FOLIO']        ?? $header['FolioPedido'] ?? $folio;
            $fechaCsv   = $header['FECHA']        ?? $header['FechaPedido'] ?? '';
            $unidadCsv  = $header['UNIDAD_VENTA'] ?? $header['Unidad']      ?? '';
            $supCsv     = $header['SUPERVISOR']   ?? '';
            $regsCsv    = $header['REGISTROS']    ?? $header['Registro']    ?? '';

            foreach ($detalle as $d) {
                $idArt   = $d['ID_ARTICULO']   ?? $d['IdArticulo'] ?? '';
                $artNom  = $d['NOMBRE_CORTO']  ?? $d['ARTICULO']   ?? '';
                $cant    = $d['CANTIDAD']      ?? $d['CanPzPed']   ?? 0;
                $pUnit   = $d['PESO_UNIT']     ?? $d['PesoUnit']   ?? 0;
                $pTotal  = $d['PESO_ARTICULO'] ?? $d['VolPed']     ?? ($cant * $pUnit);

                fputcsv($out, [
                    $folioCsv,
                    $fechaCsv,
                    $unidadCsv,
                    $supCsv,
                    $regsCsv,
                    $idArt,
                    $artNom,
                    $cant,
                    $pUnit,
                    $pTotal
                ]);
            }

            fclose($out);
            exit;



        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción inválida']);
            exit;
    }
}
