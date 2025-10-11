<?php
require_once __DIR__ . '/../helpers/encryption_helper.php';

class Empleado
{
    private mysqli $conn;

    public function __construct(mysqli $db)
    {
        $this->conn = $db;
    }

    /* ============================
       Helpers internos
       ============================ */
    private function runSP(string $sql, string $types = '', array $params = [])
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException("Prepare failed: " . $this->conn->error);
        }
        if ($types !== '') {
            // bind_param requiere referencias
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

        $result = $stmt->get_result();
        $stmt->close();
        // drenar cualquier result set adicional del SP
        $this->flushResults();
        return $result;
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
       Operaciones con sp_Empleado
       ============================ */

    /** 1: Listar empleados activos (con sucursal y puesto) */
    public function listarActivos(): array
    {
        $res = $this->runSP(
            "CALL sp_Empleado(?, ? , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            "ii" . str_repeat("s", 12),
            [
                $op = 1,
                $p_NumeroEmpleado = null,
                $p_Nombre = null,
                $p_Correo = null,
                $p_Extencion = null,
                $p_Telefono = null,
                $p_UsuarioAnyDesk = null,
                $p_ClaveAnyDesk = null,
                $p_UsuarioSAP = null,
                $p_ClaveSAP = null,
                $p_ID_Sucursal = null,
                $p_ID_Puesto = null,
                $p_ID_Usuario = null,
                $p_ID_Area = null,
                $p_Active = null
            ]
        );
        $out = [];
        while ($row = $res->fetch_assoc()) $out[] = $row;
        return $out;
    }

    /** 2: Obtener empleado activo por número (básico) */
    public function obtenerEmpleadoActivoPorNumero(int $numeroEmpleado): ?array
    {
        $res = $this->runSP(
            "CALL sp_Empleado(?, ? , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            "ii" . str_repeat("s", 12),
            [
                $op = 2,
                $p_NumeroEmpleado = $numeroEmpleado,
                $p_Nombre = null,
                $p_Correo = null,
                $p_Extencion = null,
                $p_Telefono = null,
                $p_UsuarioAnyDesk = null,
                $p_ClaveAnyDesk = null,
                $p_UsuarioSAP = null,
                $p_ClaveSAP = null,
                $p_ID_Sucursal = null,
                $p_ID_Puesto = null,
                $p_ID_Usuario = null,
                $p_ID_Area = null,
                $p_Active = null
            ]
        );
        $row = $res->fetch_assoc();
        return $row ?: null;
    }

    /** 3: Listado básico (activos) */
    public function listarBasico(): array
    {
        $res = $this->runSP(
            "CALL sp_Empleado( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            "iissssssssiiiii",
            [
                $op = 3,
                $p_NumeroEmpleado = null,
                $p_Nombre = null,
                $p_Correo = null,
                $p_Extencion = null,
                $p_Telefono = null,
                $p_UsuarioAnyDesk = null,
                $p_ClaveAnyDesk = null,
                $p_UsuarioSAP = null,
                $p_ClaveSAP = null,
                $p_ID_Sucursal = null,
                $p_ID_Puesto = null,
                $p_ID_Usuario = null,
                $p_ID_Area = null,
                $p_Active = null
            ]
        );
        $out = [];
        while ($row = $res->fetch_assoc()) $out[] = $row;
        return $out;
    }

    /** 4: Detalle completo por número (incluye usuario/área y credenciales encriptadas) */
    public function obtenerDetalle(int $numeroEmpleado): ?array
    {
        $res = $this->runSP(
            "CALL sp_Empleado(?, ? , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            "ii" . str_repeat("s", 12),
            [
                $op = 4,
                $p_NumeroEmpleado = $numeroEmpleado,
                $p_Nombre = null,
                $p_Correo = null,
                $p_Extencion = null,
                $p_Telefono = null,
                $p_UsuarioAnyDesk = null,
                $p_ClaveAnyDesk = null,
                $p_UsuarioSAP = null,
                $p_ClaveSAP = null,
                $p_ID_Sucursal = null,
                $p_ID_Puesto = null,
                $p_ID_Usuario = null,
                $p_ID_Area = null,
                $p_Active = null
            ]
        );
        $row = $res->fetch_assoc();
        if (!$row) return null;

        // Desencriptar si vienen valores
        $row['UsuarioSAP']         = !empty($row['UsuarioSAP'])         ? desencriptar($row['UsuarioSAP']) : null;
        $row['ClaveSAP']           = !empty($row['ClaveSAP'])           ? desencriptar($row['ClaveSAP']) : null;
        $row['ClaveUsuarioWindows']= !empty($row['ClaveUsuarioWindows'])? desencriptar($row['ClaveUsuarioWindows']) : null;
        $row['UsuarioAnyDesk']     = !empty($row['UsuarioAnyDesk'])     ? desencriptar($row['UsuarioAnyDesk']) : null;
        $row['ClaveAnyDesk']       = !empty($row['ClaveAnyDesk'])       ? desencriptar($row['ClaveAnyDesk']) : null;
        $row['ClaveCorreo']        = !empty($row['ClaveCorreo'])        ? desencriptar($row['ClaveCorreo']) : null;

        return $row;
    }

    /** 5: Sucursales activas (para combos) */
    public function obtenerSucursales(): array
    {
        $res = $this->runSP(
            "CALL sp_Empleado(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            "iissssssssiiiii",
            [
                $op = 5,
                $p_NumeroEmpleado = null,
                $p_Nombre = null,
                $p_Correo = null,
                $p_Extencion = null,
                $p_Telefono = null,
                $p_UsuarioAnyDesk = null,
                $p_ClaveAnyDesk = null,
                $p_UsuarioSAP = null,
                $p_ClaveSAP = null,
                $p_ID_Sucursal = null,
                $p_ID_Puesto = null,
                $p_ID_Usuario = null,
                $p_ID_Area = null,
                $p_Active = null
            ]
        );
        $out = [];
        while ($row = $res->fetch_assoc()) $out[] = $row;
        return $out;
    }

    /** 6: Alta (crear empleado). Encripta credenciales antes de enviar al SP. */
    public function crearEmpleado(array $emp): bool
    {
        $numero = (int)($emp['Numero_Empleado'] ?? 0);
        $nombre = $emp['Nombre']   ?? null;
        $correo = $emp['Correo']   ?? null;
        $ext    = $emp['Extencion']?? null;
        $tel    = $emp['Telefono'] ?? null;

        $userAD = !empty($emp['UsuarioAnyDesk'])     ? encriptar($emp['UsuarioAnyDesk'])     : null;
        $passAD = !empty($emp['ClaveAnyDesk'])       ? encriptar($emp['ClaveAnyDesk'])       : null;
        $userSAP= !empty($emp['UsuarioSAP'])         ? encriptar($emp['UsuarioSAP'])         : null;
        $passSAP= !empty($emp['ClaveSAP'])           ? encriptar($emp['ClaveSAP'])           : null;
        $passWin= !empty($emp['ClaveUsuarioWindows'])? encriptar($emp['ClaveUsuarioWindows']): null;
        $passMail=!empty($emp['ClaveCorreo'])        ? encriptar($emp['ClaveCorreo'])        : null;

        $idSuc  = isset($emp['ID_Sucursal']) ? (int)$emp['ID_Sucursal'] : null;
        $idPue  = isset($emp['ID_Puesto'])   ? (int)$emp['ID_Puesto']   : null;
        $idUser = isset($emp['ID_Usuario'])  ? (int)$emp['ID_Usuario']  : null;
        $idArea = isset($emp['ID_Area'])     ? (int)$emp['ID_Area']     : null;
        $active = isset($emp['Active'])      ? (int)$emp['Active']      : 1;

        // op = 6 (alta)
        $res = $this->runSP(
            "CALL sp_Empleado(?, ? , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            "ii" . str_repeat("s", 12),
            [
                $op = 6,
                $numero,
                $nombre,
                $correo,
                $ext,
                $tel,
                $userAD,
                $passAD,
                $userSAP,
                $passSAP,
                $idSuc,
                $idPue,
                $idUser,
                $idArea,
                $active
            ]
        );
        // Si el SP retorna algo, lo drenamos; el éxito lo inferimos por no lanzar excepción
        return true;
    }

    /** 7: Actualizar empleado por número. Solo actualiza campos provistos. */
    public function actualizarEmpleado(int $numeroEmpleado, array $emp): bool
    {
        $nombre = $emp['Nombre']   ?? null;
        $correo = $emp['Correo']   ?? null;
        $ext    = $emp['Extencion']?? null;
        $tel    = $emp['Telefono'] ?? null;

        $userAD = array_key_exists('UsuarioAnyDesk', $emp)     ? ($emp['UsuarioAnyDesk']     !== null ? encriptar($emp['UsuarioAnyDesk'])     : null) : null;
        $passAD = array_key_exists('ClaveAnyDesk', $emp)       ? ($emp['ClaveAnyDesk']       !== null ? encriptar($emp['ClaveAnyDesk'])       : null) : null;
        $userSAP= array_key_exists('UsuarioSAP', $emp)         ? ($emp['UsuarioSAP']         !== null ? encriptar($emp['UsuarioSAP'])         : null) : null;
        $passSAP= array_key_exists('ClaveSAP', $emp)           ? ($emp['ClaveSAP']           !== null ? encriptar($emp['ClaveSAP'])           : null) : null;
        $passWin= array_key_exists('ClaveUsuarioWindows', $emp)? ($emp['ClaveUsuarioWindows']!== null ? encriptar($emp['ClaveUsuarioWindows']) : null) : null;
        $passMail=array_key_exists('ClaveCorreo', $emp)        ? ($emp['ClaveCorreo']        !== null ? encriptar($emp['ClaveCorreo'])        : null) : null;

        $idSuc  = $emp['ID_Sucursal'] ?? null;
        $idPue  = $emp['ID_Puesto']   ?? null;
        $idUser = $emp['ID_Usuario']  ?? null;
        $idArea = $emp['ID_Area']     ?? null;
        $active = $emp['Active']      ?? null;

        // op = 7 (actualizar)
        $res = $this->runSP(
            "CALL sp_Empleado(?, ? , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            "ii" . str_repeat("s", 12),
            [
                $op = 7,
                $numeroEmpleado,
                $nombre,
                $correo,
                $ext,
                $tel,
                $userAD,
                $passAD,
                $userSAP,
                $passSAP,
                $idSuc,
                $idPue,
                $idUser,
                $idArea,
                $active
            ]
        );
        return true;
    }

    /* ============================
       Consultas adicionales
       ============================ */

    /** (Opcional) Empleados por sucursal (directo, fuera del SP) */
    public function obtenerEmpleadosPorSucursal(int $sucursalId): array
    {
        $sql = "SELECT e.Numero_Empleado, e.Nombre, e.Correo
                  FROM empleado e
                 WHERE e.Active = 1 AND e.ID_Sucursal = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $sucursalId);
        $stmt->execute();
        $res = $stmt->get_result();
        $out = [];
        while ($row = $res->fetch_assoc()) $out[] = $row;
        $stmt->close();
        return $out;
    }

    /** (Opcional) Empleados por puesto (directo, fuera del SP) */
    public function obtenerEmpleadosPorPuesto(int $puestoId): array
    {
        $sql = "SELECT e.Numero_Empleado, e.Nombre, e.Correo
                  FROM empleado e
                 WHERE e.Active = 1 AND e.ID_Puesto = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $puestoId);
        $stmt->execute();
        $res = $stmt->get_result();
        $out = [];
        while ($row = $res->fetch_assoc()) $out[] = $row;
        $stmt->close();
        return $out;
    }

    /** (Opcional) Empleados por área (directo, fuera del SP) */
    public function obtenerEmpleadosPorArea(int $areaId): array
    {
        $sql = "SELECT e.Numero_Empleado, e.Nombre, e.Correo
                  FROM empleado e
                 WHERE e.Active = 1 AND e.ID_Area = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $areaId);
        $stmt->execute();
        $res = $stmt->get_result();
        $out = [];
        while ($row = $res->fetch_assoc()) $out[] = $row;
        $stmt->close();
        return $out;
    }
}
