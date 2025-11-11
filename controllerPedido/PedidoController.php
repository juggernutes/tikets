<?php

require_once __DIR__ . '/../modelsPedido/pedidoModel.php';

class PedidoController
{
    private $pedidoModel;

    public function __construct($conn)
    {
        $this->pedidoModel = new PedidoModel($conn);
    }

    public function getAllPedidos()
    {
        return $this->pedidoModel->getAllPedidos();
    }

    public function getPedidoById($id)
    {
        return $this->pedidoModel->getPedidoById($id);
    }

    /*//public function createPedido($nombre, $descripcion)
    {
        //return $this->pedidoModel->createPedido($nombre, $descripcion);
    }

    public function guardarPedido($encabezado, $contenido): array
    {
        if (!$encabezado || !isset($encabezado['header']) || !isset($contenido) || !is_array($contenido)) {
            http_response_code(400);
            return ['error' => 'Payload inválido'];
        }
        file_put_contents(
            __DIR__ . '/../debug_guardar_pedido.log',
            date('Y-m-d H:i:s') . " | ENCABEZADO: " . json_encode($encabezado, JSON_UNESCAPED_UNICODE) .
                " | ITEMS: " . json_encode($contenido, JSON_UNESCAPED_UNICODE) . "\n",
            FILE_APPEND
        );

        // Debug output
        echo '<pre>';
        print_r($encabezado);
        echo '</pre>';

        $h     = $encabezado;
        $items = $contenido;

        // Normaliza nombres (por si cambian mayúsculas/minúsculas en el front)
        $IdCapacidadUV = (int)($h['IdCapacidadUV'] ?? $h['ID_CAPUV'] ?? 0);
        $IdUnidad      = (int)($h['IdUnidad']      ?? $h['IDUO'] ?? 0);
        $IdSupervisor  = (int)($h['IdSupervisor']  ?? $h['ID_SUPERVISOR_UO'] ?? 0);
        $IdAlmacen     = (int)($h['IdAlmacen']     ?? $h['ID_ALMACEN_UO'] ?? 0);
        $Registros     = (int)($h['Registro']      ?? count($items));
        $Volumen       = (float)($h['Volumen']     ?? 0);
        $Obser         = (string)($h['Obser']      ?? '');
        $IdUsuario     = (int)($h['IdUsuario']     ?? $h['ID_USUARIO'] ?? 0);

        // Fecha, Dia, Semana desde servidor (Tijuana)
        date_default_timezone_set('America/Tijuana');
        $now = new DateTime('now', new DateTimeZone('America/Tijuana'));
        $Fecha  = $now->format('Y-m-d H:i:s');
        $mapDia = [1 => 'lu', 2 => 'ma', 3 => 'mi', 4 => 'ju', 5 => 'vi', 6 => 'sa', 7 => 'do'];
        $Dia    = $mapDia[(int)$now->format('N')] ?? null; // si tu ENUM no acepta 'do', decide cómo tratar domingo
        $Semana = (int)$now->format('W');

        // Validaciones mínimas
        if (!$IdUnidad || !$IdUsuario || $Registros <= 0) {
            http_response_code(422);
            return ['error' => 'Faltan datos requeridos del encabezado'];
        }


        // === 1) Encabezado ===
        $resHead = $this->pedidoModel->guardarPedidoEncabezado($IdCapacidadUV, $IdUnidad, $IdSupervisor, $IdAlmacen, $Registros, $Dia, $Semana, $Volumen, $Fecha, $Obser, $IdUsuario);
        $folio = $resHead['Folio'] ?? '';
        $idPedido = (int)($resHead['IdPedido'] ?? 0);
        $Registros = count($items);
        $registro = 0;
        $okdet = 0;
        $otro = [];

        // === 2) Detalles ===
        foreach ($items as $it) {
            $idArticulo = (int)($it['idArticulo'] ?? 0);
            $cantidad   = (float)($it['cantidad']   ?? 0);
            $volArt     = (float)($it['volArt']     ?? 0);
            $registro++;
            $otro = $this->pedidoModel->guardarPedidoDetalle($idPedido, $folio, $idArticulo, $registro, $cantidad, $volArt, $IdUsuario);
            $okdet += $otro['Resultado'] ?? 0;

        }

        // Validar que todos los detalles se guardaron
        if ($okdet != $Registros) {
            http_response_code(500);
            return ['error' => 'Error al guardar los detalles del pedido'];
        }       


        return [
            'Folio'       => $folio,
            'IdPedido'    => $idPedido,
            'Registros'   => $Registros,
            'DetallesOk'  => $okdet,
        ];
    }*/

    public function guardarPedido(array $encabezado, array $contenido): array
    {

        
        try {
            // Validación mínima aquí; el Model hará la validación completa
            if (!is_array($encabezado) || !is_array($contenido) || count($contenido) === 0) {
                return ['ok' => false, 'msg' => 'Payload inválido: encabezado/items ausentes o vacíos', 'data' => null];
            }

            $res = $this->pedidoModel->guardarPedido($encabezado, $contenido);

            file_put_contents(
                __DIR__ . '/debug_pedido.log',
                date('Y-m-d H:i:s') . " | GUARDAR PEDIDO result: " . json_encode($res, JSON_UNESCAPED_UNICODE) . "\n",
                FILE_APPEND
            );

            // Normaliza por si el model no cumplió el contrato
            if (!is_array($res) || !array_key_exists('ok', $res)) {
                return ['ok' => false, 'msg' => 'Respuesta inválida del modelo', 'data' => $res];
            }

            return $res;
        } catch (\Throwable $e) {
            // Log mínimo (opcional)
            @file_put_contents(
                __DIR__ . '/../debug_guardar_pedido.log',
                date('Y-m-d H:i:s') . ' | CONTROLLER EXCEPTION: ' . $e->getMessage() . PHP_EOL,
                FILE_APPEND
            );

            return ['ok' => false, 'msg' => 'Error interno al guardar el pedido', 'data' => null];
        }
    }

    function getPedidosAbiertosByAlmacen($IdAlmacen): array
    {
        return $this->pedidoModel->getPedidosAbiertosByAlmacen($IdAlmacen);
    }

    function getDetallePedido($Folio): array
    {
        return $this->pedidoModel->getDetallePedido($Folio);
    }

    function marcarSurtidoPorFolio($IdPedido, $IdUsuario)
    {
        return $this->pedidoModel->marcarSurtidoPorFolio($IdPedido, $IdUsuario);
    }

    function getPedidoByFolio($Folio)
    {
        return $this->pedidoModel->getPedidoByFolio($Folio);
    }

    function csv_surtir($Folio, $IdUsuario)
    {
        return $this->pedidoModel->csv_surtir($Folio, $IdUsuario);
    }
}
