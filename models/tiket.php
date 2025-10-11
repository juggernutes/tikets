<?php
class Tiket {
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    /** Ejecuta el SP con 10 parámetros: 
     *  Operacion, p_NumeroEmpleado, p_ID_Sistema, p_ID_Error, p_Descripcion, 
     *  p_ID_Usuario, p_ID_Soporte, p_ID_Tiket, p_ID_Solucion, p_DetalleSolucion
     */
    private function ejecutarSP($op, array $p) {
        // Asegura 9 params en orden (rellena con null)
        $p = array_pad($p, 9, null);
        array_unshift($p, $op); // Operacion como primer arg

        // Tipos:  i i i i s i i i i s   ->  "iiiisiiiis"
        $types = "iiiisiiiis";

        $placeholders = implode(',', array_fill(0, count($p), '?'));
        $stmt = $this->conn->prepare("CALL sp_tiket($placeholders)");
        if (!$stmt) throw new Exception("Error al preparar SP: ".$this->conn->error);

        $stmt->bind_param($types, ...$p);
        $stmt->execute();

        // 1) TOMA EL RESULTSET PRINCIPAL
        $res = $stmt->get_result();

        // 2) LUEGO LIMPIA CUALQUIER OTRO RESULTSET DEL SP
        while ($this->conn->more_results()) {
            $this->conn->next_result();
            $tmp = $this->conn->use_result();
            if ($tmp) { $tmp->free(); }
        }

        return $res; // Puede ser false si el SP no hace SELECT
    }

    /* 1. Crear nuevo ticket */
    public function crearTiket($numEmpleado, $idSistema, $descripcion, $idError = 29) {
        // op=1  => inserta y genera folio; el SP devuelve ID y folio
        $res = $this->ejecutarSP(1, [
            $numEmpleado,         // p_NumeroEmpleado (int)
            $idSistema,           // p_ID_Sistema (int)
            $idError,             // p_ID_Error (int) - usa 29 por default
            $descripcion,         // p_Descripcion (text)
            null,                 // p_ID_Usuario
            null,                 // p_ID_Soporte
            null,                 // p_ID_Tiket
            null,                 // p_ID_Solucion
            null                  // p_DetalleSolucion
        ]);
        return $res ? $res->fetch_assoc() : null; // ['ID_Tiket'=>..., 'SerieFolio'=>...]
    }

    /* 2. Tickets de un usuario (abiertos) */
    public function getTiketsByUser($idUsuario) {
        return $this->ejecutarSP(2, [
            null, null, null, null, null,
            null, null, null, $idUsuario
        ]);
    }

    /* 3. Asignar soporte (EN PROCESO) */
    public function asignarSoporte($idTiket, $idSoporte) {
        $res = $this->ejecutarSP(3, [
            null, null, null, null, null,
            $idSoporte,           
            $idTiket              
        ]);
        return $res !== false;
    }

    /* 3bis. Toma rápida */
    public function tomarTiket($idTiket, $idSoporte) {
        return $this->asignarSoporte($idTiket, $idSoporte);
    }

    /* 4. Resolver (pasa a Resuelto) */
    public function resolver($idTiket, $idSoporte, $idError, $idSolucion, $descripcionSolucion) {
        $res = $this->ejecutarSP(4, [
            null, null, $idError, null,     // err y desc (desc se manda al final)
            null,                            // p_ID_Usuario
            $idSoporte,                      // p_ID_Soporte
            $idTiket,                        // p_ID_Tiket
            $idSolucion,                     // p_ID_Solucion
            $descripcionSolucion             // p_DetalleSolucion
        ]);
        return $res !== false;
    }

    /* 11. Guardar avance (permanece en 2) */
    public function updatetiket($idTiket, $idSoporte, $idError, $idSolucion, $descripcionSolucion) {
        $res = $this->ejecutarSP(11, [
            null, null, $idError, null,
            null,
            $idSoporte,
            $idTiket,
            $idSolucion,
            $descripcionSolucion
        ]);
        return $res !== false;
    }

    /* 5. Cerrar */
    public function cerrar($idTiket, $idUsuario) {
        $res = $this->ejecutarSP(5, [
            null, null, null, null,
            $idUsuario,          // p_ID_Usuario (quien cierra)
            null,
            $idTiket
        ]);
        return $res !== false;
    }

    /* 6. Todos los tickets (no cerrados) */
    public function obtenerTodosTikets($idSoporte = null) {
        return $this->ejecutarSP(6, [
            null, null, null, null, null,
            $idSoporte
        ]);
    }

    /* 7. Ticket por ID */
    public function getTiketById($idTiket) {
        $res = $this->ejecutarSP(7, [
            null, null, null, null, null,
            null,
            $idTiket
        ]);
        return $res ? $res->fetch_assoc() : null;
    }

    /* 8. Cerrados por usuario */
    public function getTicketsCerradosPorEmpleado($idUsuario) {
        return $this->ejecutarSP(8, [
            null, null, null, null, null,
            null, null, null, $idUsuario
        ]);
    }

    /* 9. Cerrados (general) */
    public function getTicketsCerrados() {
        return $this->ejecutarSP(9, [ ]);
    }

    /* 10. Reabrir a estatus 2 */
    public function activarTiket($idTiket) {
        $res = $this->ejecutarSP(10, [
            null, null, null, null, null,
            null,
            $idTiket
        ]);
        return $res !== false;
    }
}
