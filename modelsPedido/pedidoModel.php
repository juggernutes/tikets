<?php
class PedidoModel
{
    
    /** @var mysqli */
    private $conn;

    public function __construct(mysqli $db)
    {
        $this->conn = $db;
    }

    public function getAllPedidos(): array
    {
        $res = $this->conn->query("SELECT * FROM pedidos");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getPedidoById(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM pedidos WHERE IdPedido = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $r = $stmt->get_result();
        return $r ? $r->fetch_assoc() : null;
    }

    /**
     * Orquesta la creación del pedido completo:
     *  - op=1: crea encabezado (SP devuelve @out_IdPedido y @out_Folio)
     *  - op=2: inserta cada detalle
     *
     * Retorna SIEMPRE el shape:
     * ['ok'=>bool, 'msg'=>string, 'data'=>mixed]
     */
    public function guardarPedido(array $encabezado, array $contenido): array
    {
        // ===== 1) Validaciones mínimas =====
        $reqHeader = ['ID_CAPUV', 'ID_UNIDAD', 'ID_SUPERVISOR_UO', 'ID_ALMACEN_UO', 'Registro', 'Volumen', 'Obser', 'ID_USUARIO'];
        foreach ($reqHeader as $k) {
            if (!array_key_exists($k, $encabezado)) {
                return ['ok' => false, 'msg' => "Falta '$k' en header", 'data' => null];
            }
        }
        if (!is_array($contenido) || count($contenido) === 0) {
            return ['ok' => false, 'msg' => 'items vacío o no es arreglo', 'data' => null];
        }

        file_put_contents(
            __DIR__ . '/debug_pedido.log',
            date('Y-m-d H:i:s') . " | GUARDAR PEDIDO called\n"
                . " | ENCABEZADO: " . json_encode($encabezado, JSON_UNESCAPED_UNICODE) . "\n"
                . " | ITEMS: " . json_encode($contenido, JSON_UNESCAPED_UNICODE) . "\n",
            FILE_APPEND
        );

        $now = new DateTime('now', new DateTimeZone('America/Tijuana'));
        $mapDia = [1 => 'lu', 2 => 'ma', 3 => 'mi', 4 => 'ju', 5 => 'vi', 6 => 'sa', 7 => 'do'];

        // Normalización de encabezado
        $IdCapacidadUV = (int)$encabezado['ID_CAPUV'];
        $IdUnidad      = (int)$encabezado['ID_UNIDAD'];
        $IdSupervisor  = (int)$encabezado['ID_SUPERVISOR_UO'];
        $IdAlmacen     = (int)$encabezado['ID_ALMACEN_UO'];
        $Registros     = (int)$encabezado['Registro'];
        $Dia    = $mapDia[(int)$now->format('N')] ?? null;
        $Semana = (int)$now->format('W');
        $Volumen       = (float)$encabezado['Volumen'];
        $Fecha  = $now->format('Y-m-d H:i:s');
        $Obser         = (string)$encabezado['Obser'];
        $IdUsuario     = (int)$encabezado['ID_USUARIO'];

        // ===== 2) Encabezado (op=1) =====
        $head = $this->sp_pedido_header(
            $IdCapacidadUV,
            $IdUnidad,
            $IdSupervisor,
            $IdAlmacen,
            $Registros,
            $Dia,
            $Semana,
            $Volumen,
            $Fecha,
            $Obser,
            $IdUsuario
        );

        if (!$head['ok']) {
            return ['ok' => false, 'msg' => "Error encabezado: " . $head['msg'], 'data' => null];
        }

        $IdPedido = (int)$head['IdPedido'];
        $Folio    = (string)$head['Folio'];

        // ===== 3) Detalles (op=2) =====
        $okItems = 0;
        $fail    = [];
        $Res     = 0;

        for ($idx = 0; $idx < count($contenido); $idx++) {
            $it = $contenido[$idx];

            $IdArticulo = (int)($it['idArticulo'] ?? 0);
            $Cantidad   = (int)($it['cantidad'] ?? 0);
            $VolArt     = (float)($it['volArt'] ?? 0);
            ++$Res; // <=== aquí SÍ incrementa

            if ($IdArticulo <= 0 || $Cantidad <= 0) {
                $fail[] = ['index' => $idx, 'error' => 'IdArticulo/Cantidad inválidos'];
                continue;
            }

            $det = $this->sp_pedido_detalle(
                $IdPedido,
                $Folio,
                $IdArticulo,
                $Res,
                $Cantidad,
                $VolArt,
                $IdUsuario
            );

            if ($det['ok']) {
                $okItems++;
            } else {
                $fail[] = ['index' => $idx, 'error' => $det['msg']];
            }
        }

        // ===== 4) Respuesta =====
        return [
            'ok'  => (count($fail) === 0),
            'msg' => (count($fail) ? 'Pedido creado con errores en algunos items' : 'Pedido creado correctamente'),
            'data' => [
                'IdPedido'  => $IdPedido,
                'Folio'     => $Folio,
                'items_ok'  => $okItems,
                'items_err' => $fail
            ]
        ];
    }

    /* ===================== Helpers privados ===================== */

    /**
     * Llama al SP con op=1 (crear encabezado).
     * Retorna ['ok'=>bool, 'msg'=>string, 'IdPedido'=>int, 'Folio'=>string]
     */
    private function sp_pedido_header(
        int $IdCapacidadUV,
        int $IdUnidad,
        int $IdSupervisor,
        int $IdAlmacen,
        int $Registros,
        string $Dia,
        int $Semana,
        float $Volumen,
        string $Fecha,
        string $Obser,
        int $IdUsuario
    ): array {
        $op             = 1;
        $idEstadoPedido = 1; // por defecto

        // Log de entrada
        file_put_contents(
            __DIR__ . '/debug_pedido.log',
            date('Y-m-d H:i:s') . " | SP HEADER CALL with: " .
                "operacion=$op, estado=$idEstadoPedido, IdCapacidadUV=$IdCapacidadUV, IdUnidad=$IdUnidad, IdSupervisor=$IdSupervisor, IdAlmacen=$IdAlmacen, " .
                "Registros=$Registros, Dia=$Dia, Semana=$Semana, Volumen=$Volumen, Fecha=$Fecha, Obser=$Obser, IdUsuario=$IdUsuario\n",
            FILE_APPEND
        );

        $sql = "CALL sp_pedido(
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,     -- 13 IN (encabezado)
        NULL, NULL, NULL, NULL, NULL,              -- 5 IN (detalle) nulos
        @out_IdPedido, @out_Folio                  -- 2 OUT
    )";

        // 1) Preparar
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return ['ok' => false, 'msg' => 'Prepare failed: ' . $this->conn->error, 'IdPedido' => null, 'Folio' => null];
        }

        // 2) Bind (tipos: i i i i i i i s i d s s i)
        if (!$stmt->bind_param(
            'iiiiiiisidssi',
            $op,
            $idEstadoPedido,
            $IdCapacidadUV,
            $IdUnidad,
            $IdSupervisor,
            $IdAlmacen,
            $Registros,
            $Dia,
            $Semana,
            $Volumen,
            $Fecha,
            $Obser,
            $IdUsuario
        )) {
            $stmt->close();
            return ['ok' => false, 'msg' => 'Bind failed: ' . $stmt->error, 'IdPedido' => null, 'Folio' => null];
        }

        // 3) Ejecutar
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            $this->drainResults();
            return ['ok' => false, 'msg' => 'Execute failed: ' . $err, 'IdPedido' => null, 'Folio' => null];
        }

        // No esperamos result set (OUT params), así que no uses get_result()
        $stmt->close();

        // 4) Drenar cualquier result set pendiente del CALL
        $this->drainResults();

        // 5) Leer OUT params
        $out = $this->conn->query("SELECT @out_IdPedido AS IdPedido, @out_Folio AS Folio");
        if (!$out) {
            return ['ok' => false, 'msg' => 'OUT select failed: ' . $this->conn->error, 'IdPedido' => null, 'Folio' => null];
        }
        $row = $out->fetch_assoc();
        $out->free();

        $IdPedido = $row['IdPedido'] ?? null;
        $Folio    = $row['Folio'] ?? null;

        if (!$IdPedido || !$Folio) {
            return ['ok' => false, 'msg' => 'OUT params vacíos (IdPedido/Folio)', 'IdPedido' => null, 'Folio' => null];
        }

        return ['ok' => true, 'msg' => 'OK', 'IdPedido' => (int)$IdPedido, 'Folio' => (string)$Folio];
    }


    /**
     * Llama al SP con op=2 (insertar detalle).
     * Retorna ['ok'=>bool, 'msg'=>string]
     */
        private function sp_pedido_detalle(
        int $IdPedido,
        string $Folio,
        int $IdArticulo,
        int $Registro,
        float $Cantidad,
        float $VolumenArt,
        int $IdUsuario
    ): array {
        $op2 = 2;

        file_put_contents(
            __DIR__ . '/debug_pedido.log',
            date('Y-m-d H:i:s') . " | SP DETALLE CALL with: operacion=$op2, IdPedido=$IdPedido, Folio=$Folio, IdArticulo=$IdArticulo, Registro=$Registro, Cantidad=$Cantidad, VolumenArt=$VolumenArt, IdUsuario=$IdUsuario\n",
            FILE_APPEND
        );

        // 18 IN en el orden del SP + 2 OUT (como variables de usuario)
        $sql = "CALL sp_pedido(
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,   -- 13 header
            ?, ?, ?, ?, ?,                           -- 5 detalle
            @out_IdPedido, @out_Folio
        )";

        // Variables NULL para header no usados en op=2
        $nullI = null; // int
        $nullS = null; // string
        $nullD = null; // double

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            $this->drainResults();
            return ['ok' => false, 'msg' => 'Prepare failed: ' . $this->conn->error];
        }

        // Tipos: 13 header (i i i i i i i s i d s s i) + 5 detalle (i s i i d)
        $types = 'iiiiiiisidssi' . 'isiid';

        $bound = $stmt->bind_param(
            $types,
            // --- Header (13) ---
            $op2,        // p_op
            $nullI,      // p_IdEstado
            $nullI,      // p_IdCapacidadUV
            $nullI,      // p_IdUnidad
            $nullI,      // p_IdSupervisor
            $nullI,      // p_IdAlmacen
            $Registro,   // p_Registro  (sí lo mandamos)
            $nullS,      // p_Dia
            $nullI,      // p_Semana
            $nullD,      // p_Volumen
            $nullS,      // p_fecha
            $nullS,      // p_Obser
            $IdUsuario,  // p_IdUsuario  (AUDITORÍA ACTIVA)
            // --- Detalle (5) ---
            $IdPedido,   // p_IdPedido
            $Folio,      // p_folio
            $IdArticulo, // p_IdArticulo
            $Cantidad,   // p_CantidadArticulo
            $VolumenArt  // p_VolumenArticulo
        );

        if (!$bound) {
            $stmt->close();
            $this->drainResults();
            return ['ok' => false, 'msg' => 'Bind failed: ' . $this->conn->error];
        }

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            $this->drainResults();
            return ['ok' => false, 'msg' => 'Execute failed: ' . $err];
        }

        $stmt->close();
        $this->drainResults();

        // Si quieres validar algo extra:
        // $out = $this->conn->query("SELECT @out_IdPedido, @out_Folio");
        // ...

        return ['ok' => true, 'msg' => 'OK'];
    } 

    /** Libera por completo cualquier result set pendiente de llamadas previas a CALL ... */
        private function drainResults(): void
    {
        while ($this->conn->more_results()) {
            $this->conn->next_result();
            if ($r = $this->conn->store_result()) {
                $r->free();
            }
        }
    }
}
