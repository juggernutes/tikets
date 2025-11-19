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
    public function guardarPedido($capacidadUV, $unidadOpe, $supervisor, $almacen, $registros, $dia, $semana, $volumen, $obser, $usuario)
    {
        $op = 1;
        $idEstadoPedido = 1; // por defecto
        $params = [
            $idEstadoPedido,
            $capacidadUV,
            $unidadOpe,
            $supervisor,
            $almacen,
            $registros,
            $dia,
            $semana,
            $volumen,
            null,
            $obser,
            $usuario,
            null,
            null,
            null,
            null,
            null
        ];

        $sql = "CALL sp_pedido(
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,     -- 13 IN (encabezado)
        ?, ?, ?, ?, ?,                              -- 5 IN (detalle) nulos
        @out_IdPedido, @out_Folio                  -- 2 OUT
        )";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iiiiiiisidssiisiid', $op, ...$params);
        $stmt->execute();
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

    
    public function guardarDetallePedido($idpedido, $folio, $idArticulo, $registro, $cantidad, $volArt, $IdUsuario) 
    {
        $op2 = 2;
        file_put_contents(
            __DIR__ . '/debug_pedido.log',
            date('Y-m-d H:i:s') . " | SP DETALLE CALL with: operacion=$op2, IdPedido=$idpedido, Folio=$folio, IdArticulo=$idArticulo, Registro=$registro, Cantidad=$cantidad, VolumenArt=$volArt, IdUsuario=$IdUsuario\n",
            FILE_APPEND
        );
        $params = [
            null,
            null,
            null,
            null,
            null,
            $registro,
            null,
            null,
            null,
            null,
            null,
            $IdUsuario,
            $idpedido,
            $folio,
            $idArticulo,
            $cantidad,
            $volArt
        ];

        // 18 IN en el orden del SP + 2 OUT (como variables de usuario)
        $sql = "CALL sp_pedido(
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,   -- 13 header
            ?, ?, ?, ?, ?,                           -- 5 detalle
            @out_IdPedido, @out_Folio
        )";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            $this->drainResults();
            return ['ok' => false, 'msg' => 'Prepare failed: ' . $this->conn->error];
        }

        $types = 'iiiiiiisidssi' . 'isiid';
        $stmt->bind_param($types, $op2, ...$params);
        $stmt->execute();
        $stmt->close();

         // Drenar cualquier result set pendiente del CALL
        $this->drainResults();


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

    public function getPedidosAbiertosByAlmacen(int $IdAlmacen): array
    {
        $op = 8; // operación para obtener pedidos abiertos por almacen
        $params = [
            null,
            null,
            null,
            null,
            $IdAlmacen,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null
        ];
        $stmt = $this->conn->prepare("CALL sp_pedido(
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,   -- 13 header
            ?, ?, ?, ?, ?,                           -- 5 detalle
            @out_IdPedido, @out_Folio
        )");
        $stmt->bind_param('iiiiiiisidssiisiid', $op, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $open = [];

        while ($row = $result->fetch_assoc()) {
            $open[] = $row;
        }
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            $this->conn->use_result();
        }
        return $open;
    }

    public function getDetallePedido(string $Folio): array
    {
        $op = 9; // operación para obtener detalle de pedido por folio
        $params = [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $Folio,
            null,
            null,
            null
        ];
        $stmt = $this->conn->prepare("CALL sp_pedido(
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,   -- 13 header
            ?, ?, ?, ?, ?,                           -- 5 detalle
            @out_IdPedido, @out_Folio
        )");
        $stmt->bind_param('iiiiiiisidssiisiid', $op, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $detalle = [];

        while ($row = $result->fetch_assoc()) {
            $detalle[] = $row;
        }
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            $this->conn->use_result();
        }
        return $detalle;
    }

    public function PedidoSurtido(int $idpedido, int $usuario, float $volumen): bool
    {
        $stmt = $this->conn->prepare("CALL sp_pedido(
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,   -- 13 header
            ?, ?, ?, ?, ?,                           -- 5 detalle
            @out_IdPedido, @out_Folio
        )");

        $op = 5; // operación para marcar como surtido
        $comments = '';
        $params = [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $volumen,
            null,
            $comments,
            $usuario,
            $idpedido,
            null,
            null,
            null,
            null
        ];
        $stmt = $this->conn->prepare("CALL sp_pedido(
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,   -- 13 header
            ?, ?, ?, ?, ?,                           -- 5 detalle
            @out_IdPedido, @out_Folio
        )");
        $stmt->bind_param('iiiiiiisidssiisiid', $op, ...$params);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    function getPedidoByFolio($Folio)
    {
        $op = 11; // operación para obtener pedido por folio
        $params = [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $Folio,
            null,
            null
        ];
        $stmt = $this->conn->prepare("CALL sp_pedido(
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,   -- 13 header
            ?, ?, ?, ?, ?,                           -- 5 detalle
            @out_IdPedido, @out_Folio
        )");
        $stmt->bind_param('iiiiiiisidssiisiid', $op, ...$params);
        $stmt->execute();

        $result = $stmt->get_result();
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            $this->conn->use_result();
        }
        return $result;
    }

    public function csv_surtir(string $Folio, int $IdUsuario): array
    {
        $CSV = [];
        $op = 13; // operación para generar CSV y marcar como surtido
        $params = [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $IdUsuario,
            null,
            $Folio,
            null,
            null,
            null
        ];
        $stmt = $this->conn->prepare("CALL sp_pedido(
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            @out_IdPedido, @out_Folio
        )");
        $stmt->bind_param('iiiiiiisidssiisiid', $op, ...$params);
        $stmt->execute();
        do {
            if ($res = $stmt->get_result()) {
                while ($row = $res->fetch_assoc()) {
                    $CSV[] = $row;
                }
                $res->free();
            }
        } while ($stmt->more_results() && $stmt->next_result());

        $stmt->close();

        return $CSV;;
    }

    public function getPedidosAbiertosBySupervisor(int $IdSupervisor): array
    {
        $op = 14; // operación para obtener pedidos abiertos por supervisor
        $params = [
            null,
            null,
            null,
            $IdSupervisor,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null
        ];

        $stmt = $this->conn->prepare("CALL sp_pedido(
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,   -- 13 header
            ?, ?, ?, ?, ?,                           -- 5 detalle
            @out_IdPedido, @out_Folio
        )");
        $stmt->bind_param('iiiiiiisidssiisiid', $op, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $detalle = [];

        while ($row = $result->fetch_assoc()) {
            $detalle[] = $row;
        }
        return $detalle;
    }

    public function cancelarItemPedido($idpedido, $idItem)
    {
        $op = 15;
        $params = [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $idpedido,
            null,
            $idItem,
            null,
            null
        ];
        $stmt = $this->conn->prepare("CALL sp_pedido(
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,   -- 13 header
            ?, ?, ?, ?, ?,                           -- 5 detalle
            @out_IdPedido, @out_Folio
        )");
        $stmt->bind_param('iiiiiiisidssiisiid', $op, ...$params);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    public function actualizarDetallePedido($idpedido, $idItem, $cantidad, $VolArt, $usuario, $op)
    {
        //$op = 4;

        $params = [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,   // Header vacío
            $usuario,
            $idpedido,
            null,           // folio null
            $idItem,
            $cantidad,
            $VolArt
        ];

        // 1) Ejecutar SP
        $stmt = $this->conn->prepare("
        CALL sp_pedido(
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, 
            @out_IdPedido, @out_Folio
        )
    ");
        $stmt->bind_param('iiiiiiisidssiisiid', $op, ...$params);
        $stmt->execute();
        $stmt->close();
        // IMPORTANTE: cerrar antes de llamar otra query

        // 2) Consultar OUT params
        $res = $this->conn->query("SELECT @out_IdPedido AS idp, @out_Folio AS folio");
        $row = $res->fetch_assoc();

        // 3) Validar resultado
        if (!empty($row['idp'])) {
            return true;
        } else {
            return false;
        }
    }


    public function autorizarPedido($Volumen, $idpedido, $usuario)
    {
        $op = 3;
        $comments = "Aprobado por supervisor";
        $params = [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $Volumen,
            null,
            $comments,
            $usuario,
            $idpedido,
            null,
            null,
            null,
            null
        ];
        $stmt = $this->conn->prepare("CALL sp_pedido(
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,   -- 13 header
            ?, ?, ?, ?, ?,                           -- 5 detalle
            @out_IdPedido, @out_Folio
        )");
        $stmt->bind_param('iiiiiiisidssiisiid', $op, ...$params);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }
}
