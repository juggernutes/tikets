<?php
class Tiket
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Método reutilizable para ejecutar cualquier operación del SP
    private function ejecutarSP($op, $parametros = [])
    {
        $params = array_merge([$op], $parametros);
        $placeholders = implode(',', array_fill(0, count($params), '?'));

        $types = '';
        foreach ($params as $p) {
            if (is_int($p)) {
                $types .= 'i';
            } elseif (is_string($p)) {
                $types .= 's';
            } else {
                $types .= 's';
            }
        }

        $stmt = $this->conn->prepare("CALL sp_tiket($placeholders)");
        if (!$stmt) {
            throw new Exception("Error al preparar SP: " . $this->conn->error);
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        while ($this->conn->more_results() && $this->conn->next_result()) {
            $this->conn->use_result();
        }

        return $stmt->get_result();
    }

    // Operación 1: Crear nuevo ticket
    public function crearTiket($numEmpleado, $idSistema, $descripcion)
    {
        $op = 1;
        $params = [null, $numEmpleado, $idSistema, 29, $descripcion, null, null, null, null, null, null];

        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiiisiisiss', $op, ...$params);
        $stmt->execute();

        $id = 0;

        // Si tu SP hace: SELECT @ultimoID AS id_tiket;
        if ($res = $stmt->get_result()) {
            $row = $res->fetch_assoc();
            $id = (int)($row['id_tiket'] ?? 0);
            $res->free();
        }

        // Limpia resultados pendientes del SP
        while ($this->conn->more_results() && $this->conn->next_result()) {
            $junk = $this->conn->use_result();
            if ($junk) {
                $junk->free();
            }
        }

        $stmt->close();
        return $id;
    }




    // Operación personalizada: soporte toma un ticket
    public function tomarTiket($idTiket, $idSoporte)
    {
        $op = 3;
        $params = [$idTiket, null, null, null, null, $idSoporte, null, null, null, null, null];

        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiiisiisiss', $op, ...$params);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    // Operación 4: Resolver ticket asignando error y solución
    public function resolver($idTiket, $idSoporte, $idError, $idSolucion, $descripcionSolucion)
    {
        $op = 4;
        $params = [
            $idTiket,
            null,
            null,
            $idError,
            null,
            $idSoporte,
            $idSolucion,
            $descripcionSolucion,
            null, null, null
        ];
        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiiisiisiss', $op, ...$params);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }


    public function updatetiket($idTiket, $idSoporte, $idError, $idSolucion, $descripcionSolucion)
    {
        $op = 11;
        $params = [
            $idTiket,
            null,
            null,
            $idError,
            null,
            $idSoporte,
            $idSolucion,
            $descripcionSolucion,
            null, null, null
        ];
        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiiisiisiss', $op, ...$params);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    // Operación 5: cerrar ticket
    public function cerrar($idTiket, $idUsuario)
    {
        $op = 5;
        $params = [
            $idTiket,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $idUsuario, null, null
        ];
        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiiisiisiss', $op, ...$params);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    //                                Operación 2: Obtener tickets por usuario
    public function getTiketsByUser($idUsuario)
    {
        return $this->ejecutarSP(2, [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $idUsuario, null, null
        ]);
    }

    public function getTicketbyProveedor($usuario)
    {
        $op = 12;
        $params = [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $usuario, null, null
        ];
        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiiisiisiss', $op, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            $this->conn->use_result();
        }
        return $res;
    }

    //                                  Operación 6: Obtener todos los tickets
    public function obtenerTodosTikets($idSoporte)
    {
        return $this->ejecutarSP(6, [
            null,
            null,
            null,
            null,
            null,
            $idSoporte,
            null,
            null,
            null, null, null
        ]);
    }

    public function getTiketById($idTiket)
    {
        $result = $this->ejecutarSP(7, [
            $idTiket,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null, null, null
        ]);
        return $result->fetch_assoc();
    }


    // Obtener un ticket específico
    /*public function getTiket($idTiket) {
        $result = $this->ejecutarSP(2, [
            $idTiket,
            null, null, null, null, null, null, null, null
        ]);
        return $result->fetch_assoc();
    }*/

    // Operación 3: Asignar soporte y marcar como EN PROCESO
    public function asignarSoporte($idTiket, $idSoporte)
    {
        return $this->ejecutarSP(3, [
            $idTiket,
            null,
            null,
            null,
            null,
            null,
            $idSoporte,
            null,
            null, null, null
        ]) !== false;
    }

    public function getTicketsCerradosPorEmpleado($idUsuario)
    {
        $result = $this->ejecutarSP(8, [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $idUsuario, null, null
        ]);
        return $result;
    }

    public function getTicketsCerrados()
    {
        $result = $this->ejecutarSP(9, [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null, null, null
        ]);
        return $result;
    }

    public function activarTiket($idTiket)
    {
        $op = 10;
        $params = [$idTiket, null, null, null, null, null, null, null, null, null, null];
        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiiisiisiss', $op, ...$params);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    public function datosReportePorProveedor($idProveedor, $fecha)
    {
        $op = 13;
        $data = [];
        $dt = DateTime::createFromFormat('Y-m', $fecha);
        if (!$dt) {
            throw new InvalidArgumentException("Formato de fecha inválido. Esperado: YYYY-MM");
        }

        $fechaInicio = $dt->format('Y-m-01 00:00:00');
        $fechaFin    = $dt->format('Y-m-t 23:59:59');

        file_put_contents(
            __DIR__ . '/../reporteProveedor_debug.log',
            date('Y-m-d H:i:s') . "PROVEEDOR: $idProveedor | FECHA INICIO: $fechaInicio, FECHA FIN: $fechaFin" . PHP_EOL,
            FILE_APPEND
        );

        $params = [
            null,
            null,
            null,
            null,
            null,
            $idProveedor,
            null,
            null,
            null,
            $fechaInicio,
            $fechaFin
        ];
        $stmt = $this->conn->prepare("CALL sp_tiket(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiiisiisiss', $op, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        while ($this->conn->more_results() && $this->conn->next_result()) {
            $this->conn->use_result();
        }
        return $data;
    }
}
