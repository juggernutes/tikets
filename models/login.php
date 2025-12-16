<?php
class Login
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    public function validarUsuarioSP($cuenta): ?array
    {
        $stmt = $this->conn->prepare("CALL sp_login(?, NULL, ?, NULL, NULL, NULL, NULL, NULL, NULL)");
        $op = 1;
        $stmt->bind_param("is", $op, $cuenta);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }


    public function actualizarPasswordSP(int $idLogin, string $hash): ?array
    {
        $sql = "CALL sp_Login(?, ?, NULL, ?, NULL, NULL, NULL, NULL, NULL)";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            error_log("Error preparando sp_Login: " . $this->conn->error);
            return null;
        }

        $op = 2;
        $stmt->bind_param('iis', $op, $idLogin, $hash);

        if (!$stmt->execute()) {
            error_log("Error ejecutando sp_Login (Operacion=2): " . $stmt->error);
            $stmt->close();
            return null;
        }

        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;

        $stmt->close();

        // Limpieza de posibles result sets adicionales (importante con procedimientos)
        while ($this->conn->more_results() && $this->conn->next_result()) {
            $this->conn->use_result();
        }

        return $row ?: null;
    }



    public function insertarLoginSP($cuenta, $hash, $idUsuario): bool
    {
        $stmt = $this->conn->prepare("CALL sp_login(?, NULL, ?, ?, ?, NULL, NULL)");
        $op = 3;
        $stmt->bind_param("issi", $op, $cuenta, $hash, $idUsuario);
        return $stmt->execute();
    }

    public function registrarSesionSP($idLogin, $sesionID): bool
    {
        $stmt = $this->conn->prepare("CALL sp_login(?, ?, NULL, NULL, NULL, ?, NULL)");
        $op = 4;
        $stmt->bind_param("iss", $op, $idLogin, $sesionID);
        return $stmt->execute();
    }

    /*public function restaurarPws($idLogin, $nuevoHash){
        $stmt =  $this->conn->prepare("CALL sp_login(?,?, null,?, null, null, NULL)");
        $op = 5;
        $stmt->bind_param("iissis", $op, $idLogin, $nuevoHash);
        return $stmt->execute();
    }


    public function crearTiketRestablecerContrasena($cuenta) {
            $stmt = $this->conn->prepare("CALL sp_login(?, NULL, ?, NULL, NULL, NULL, NULL)");
            $op = 7;
            $stmt->bind_param("is", $op, $cuenta);
            return $stmt->execute();
        }*/


    public function obtenerCuentaPorCorreo($correo): ?array
    {
        $stmt = $this->conn->prepare("CALL sp_login(?, NULL, NULL, NULL, NULL, NULL, ?, NULL, NULL)");
        $op = 5;
        $stmt->bind_param("is", $op, $correo);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->Close();
        $this->_flushResults();
        return $res;
    }

    public function guardarToken($idLogin, $token, $fechaExpiracion): bool
    {
        $stmt = $this->conn->prepare("CALL sp_login(?, ?, NULL, NULL, NULL, NULL, NULL, ?, ?)");
        $op = 6;
        $stmt->bind_param("iiss", $op, $idLogin, $token, $fechaExpiracion);
        return $stmt->execute();
    }

    public function buscarToken($token): ?array
    {
        $stmt = $this->conn->prepare("CALL sp_login(?, NULL, NULL, NULL, NULL, NULL, NULL, ?, NULL)");
        $op = 7;
        $stmt->bind_param("is", $op, $token);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();


        return $res;
    }

    public function cambiarPasswordConToken($token, $nuevaPassword): bool
    {
        $stmt = $this->conn->prepare("CALL sp_login(?, NULL, NULL, ?, NULL, NULL, NULL, ? ,NULL)");
        $op = 8;
        $stmt->bind_param("iss", $op, $nuevaPassword, $token);
        return $stmt->execute();
    }

    /*public function invalidarToken(int $idLogin, string $tokenHash): bool
    {
        $stmt = $this->conn->prepare("CALL sp_login(?, NULL, NULL, NULL, NULL, NULL, NULL, ?, NULL)");
        $op = 9; // Suponiendo que la operación 9 es para invalidar el token
        $stmt->bind_param("is", $op, $tokenHash);
        return $stmt->execute();
    }*/

    public function marcarTokenUsado($token): bool
    {
        $stmt = $this->conn->prepare("CALL sp_login(?, NULL, NULL, NULL, NULL, NULL, NULL, ?, NULL)");
        $op = 8; // Suponiendo que la operación 8 es para marcar el token como usado
        $stmt->bind_param("is", $op, $token);
        return $stmt->execute();
    }
    // En class Login
    private function _flushResults(): void
    {
        // Cierra el result actual si existe
        while ($this->conn->more_results()) {
            $this->conn->next_result();
            // Opcional: consumir cualquier result set pendiente
            if ($res = $this->conn->store_result()) {
                $res->free();
            }
        }
    }

    public function obtenerTodosLosUsuarios(){
        $stmt = $this->conn->prepare("CALL sp_login(?, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)");
        $op = 9;
        $stmt->bind_param("i", $op);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
        $stmt->close();
        $this->_flushResults();
        return $usuarios;
    }

    // Model
    public function resetearPassword(int $idLogin, string $hash): bool
    {
        $sql = "CALL sp_Login(?, ?, NULL, ?, NULL, NULL, NULL, NULL, NULL)";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Error prepare resetearPassword: " . $this->conn->error);
            return false;
        }
    
        $op = 10;
    
        if (!$stmt->bind_param('iis', $op, $idLogin, $hash)) {
            error_log("Error bind_param resetearPassword: " . $stmt->error);
            return false;
        }
    
        if (!$stmt->execute()) {
            error_log("Error execute resetearPassword: " . $stmt->error);
            return false;
        }
    
        $ok = $stmt->affected_rows > 0;
    
        $stmt->close();
    
        return $ok;
    }


}
