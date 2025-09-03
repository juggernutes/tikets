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


    public function actualizarPasswordSP($idLogin, $nuevoHash): bool
    {
        $stmt = $this->conn->prepare("CALL sp_login(?, ?, NULL, ?, NULL, NULL, NULL, NULL, NULL)");
        $op = 2;
        $stmt->bind_param("iis", $op, $idLogin, $nuevoHash);
        $ok = $stmt->execute();
        $err = $stmt->error;
        $stmt->close();
        $this->_flushResults();
        return $ok && $err === 0;
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
        return $stmt->get_result()->fetch_assoc();
    }

    public function guardarToken($idLogin, $token, $fechaExpiracion): bool
    {
        $stmt = $this->conn->prepare("CALL sp_login(?, ?, NULL, NULL, NULL, NULL, NULL, ?, ?)");
        $op = 6;
        $stmt->bind_param("iiss", $op, $idLogin, $token, $fechaExpiracion);
        return $stmt->execute();
    }

    public function buscarTokenPorHash($token): ?array
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
}
