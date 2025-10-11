<?php

class Sistema
{
    private mysqli $conn;

    public function __construct(mysqli $db)
    {
        $this->conn = $db;
    }

    /* ============================
       Helpers internos
       ============================ */
    private function runSP(string $sql, string $types = '', array $params = []): ?mysqli_result
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->conn->error);
        }

        if ($types !== '') {
            // bind_param necesita referencias
            $bind = [];
            $bind[] = &$types;
            foreach ($params as $k => $v) {
                $bind[] = &$params[$k];
            }
            call_user_func_array([$stmt, 'bind_param'], $bind);
        }

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            $this->flushResults();
            throw new RuntimeException("Execute failed: " . $err);
        }

        $res = $stmt->get_result(); // puede ser null si el SP no devuelve SELECT
        $stmt->close();

        // Drenar cualquier result set remanente por procedimientos
        $this->flushResults();
        return $res;
    }

    private function flushResults(): void
    {
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($extra = $this->conn->use_result()) {
                $extra->free();
            }
        }
    }

    /* ============================
       Operaciones con sp_Sistema
       ============================ */

    /** 1: Listar activos */
    public function listarActivos(): array
    {
        $res = $this->runSP("CALL sp_Sistema(?, ?, ?, ?, ?)", "iisss", [
            $op = 1,
            $p_id = null,
            $p_nombre = null,
            $p_descripcion = null,
            $p_active = null
        ]);
        $out = [];
        if ($res) { while ($row = $res->fetch_assoc()) { $out[] = $row; } }
        // Ordena por Nombre (natural, case-insensitive)
        usort($out, fn($a,$b)=>strnatcasecmp($a['Nombre']??'', $b['Nombre']??''));
        return $out;
    }

    /** 2: Obtener por ID (solo si está activo, según el SP) */
    public function obtenerPorId(int $id): ?array
    {
        $res = $this->runSP("CALL sp_Sistema(?, ?, ?, ?, ?)", "iisss", [
            $op = 2,
            $id,
            $p_nombre = null,
            $p_descripcion = null,
            $p_active = null
        ]);
        return $res ? ($res->fetch_assoc() ?: null) : null;
    }

    /** 3: Crear. Devuelve el ID_Sistema creado (o null si no vino). */
    public function crear(string $nombre, ?string $descripcion = null): ?int
    {
        $res = $this->runSP("CALL sp_Sistema(?, ?, ?, ?, ?)", "iisss", [
            $op = 3,
            $p_id = 0,
            $nombre,
            $descripcion,
            $p_active = null
        ]);
        // El SP hace SELECT LAST_INSERT_ID() AS ID_Sistema;
        if ($res) {
            $row = $res->fetch_assoc();
            return isset($row['ID_Sistema']) ? (int)$row['ID_Sistema'] : null;
        }
        return null;
    }

    /**
     * 4: Actualizar. Solo actualiza campos provistos.
     * $active: null = no cambiar; 0/1 para setear.
     */
    public function actualizar(
        int $id,
        ?string $nombre = null,
        ?string $descripcion = null,
        ?int $active = null
    ): int {
        $res = $this->runSP("CALL sp_Sistema(?, ?, ?, ?, ?)", "iisss", [
            $op = 4,
            $id,
            $nombre,
            $descripcion,
            // OJO: sp_Sistema espera TINYINT en p_Active; pasar null o '0'/'1' como string funciona, pero mejor:
            $active === null ? null : (string)$active
        ]);
        // El SP retorna SELECT ROW_COUNT() AS rows_affected;
        if ($res) {
            $row = $res->fetch_assoc();
            return isset($row['rows_affected']) ? (int)$row['rows_affected'] : 0;
        }
        return 0;
    }

    /** 5: Desactivar (Active=0). Retorna filas afectadas. */
    public function desactivar(int $id): int
    {
        $res = $this->runSP("CALL sp_Sistema(?, ?, ?, ?, ?)", "iisss", [
            $op = 5,
            $id,
            $p_nombre = null,
            $p_descripcion = null,
            $p_active = null
        ]);
        if ($res) {
            $row = $res->fetch_assoc();
            return isset($row['rows_affected']) ? (int)$row['rows_affected'] : 0;
        }
        return 0;
    }

    /** 6: Reactivar (Active=1). Retorna filas afectadas. */
    public function reactivar(int $id): int
    {
        $res = $this->runSP("CALL sp_Sistema(?, ?, ?, ?, ?)", "iisss", [
            $op = 6,
            $id,
            $p_nombre = null,
            $p_descripcion = null,
            $p_active = null
        ]);
        if ($res) {
            $row = $res->fetch_assoc();
            return isset($row['rows_affected']) ? (int)$row['rows_affected'] : 0;
        }
        return 0;
    }

    /** 7: Listar todos (activos e inactivos) */
    public function listarTodos(): array
    {
        $res = $this->runSP("CALL sp_Sistema(?, ?, ?, ?, ?)", "iisss", [
            $op = 7,
            $p_id = null,
            $p_nombre = null,
            $p_descripcion = null,
            $p_active = null
        ]);
        $out = [];
        if ($res) { while ($row = $res->fetch_assoc()) { $out[] = $row; } }
        // Orden: activos primero (como en el SP), luego por Nombre
        usort($out, function($a,$b){
            $cmp = ($b['Active']??0) <=> ($a['Active']??0);
            if ($cmp !== 0) return $cmp;
            return strnatcasecmp($a['Nombre']??'', $b['Nombre']??'');
        });
        return $out;
    }

    /**
     * 8: Buscar por texto en Nombre/Descripcion.
     * $texto: cadena a buscar (LIKE %texto%).
     * $soloEstado: null = sin filtro; 1 = solo activos; 0 = solo inactivos.
     */
    public function buscar(string $texto, ?int $soloEstado = null): array
    {
        // p_Active acepta NULL para no filtrar
        $activeParam = $soloEstado === null ? null : (string)$soloEstado;

        $res = $this->runSP("CALL sp_Sistema(?, ?, ?, ?, ?)", "iisss", [
            $op = 8,
            $p_id = null,
            $texto,             // p_Nombre (se usa para LIKE)
            $texto,             // p_Descripcion (se usa para LIKE)
            $activeParam
        ]);
        $out = [];
        if ($res) { while ($row = $res->fetch_assoc()) { $out[] = $row; } }
        usort($out, fn($a,$b)=>strnatcasecmp($a['Nombre']??'', $b['Nombre']??''));
        return $out;
    }
}
