<?php
class Login {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function validarUsuarioSP($cuenta) {
        $stmt = $this->conn->prepare("CALL sp_login(?, NULL, ?, NULL, NULL, NULL, NULL)");
        $op = 1;
        $stmt->bind_param("is", $op, $cuenta);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function actualizarPasswordSP($idLogin, $nuevoHash) {
        $stmt = $this->conn->prepare("CALL sp_login(?, ?, NULL, ?, NULL, NULL, NULL)");
        $op = 2;
        $stmt->bind_param("iis", $op, $idLogin, $nuevoHash);
        return $stmt->execute();
    }

    public function insertarLoginSP($cuenta, $hash, $idUsuario) {
        $stmt = $this->conn->prepare("CALL sp_login(?, NULL, ?, ?, ?, NULL, NULL)");
        $op = 3;
        $stmt->bind_param("issi", $op, $cuenta, $hash, $idUsuario);
        return $stmt->execute();
    }

    public function registrarSesionSP($idLogin, $sesionID) {
        $stmt = $this->conn->prepare("CALL sp_login(?, ?, NULL, NULL, NULL, ?, NULL)");
        $op = 4;
        $stmt->bind_param("iss", $op, $idLogin, $sesionID);
        return $stmt->execute();
    }

    public function restaurarPws( $idLogin,$nuevoHash){
        $stmt =  $this->conn->prepare("CALL sp_login(?,?, null,?, null, null, NULL)");
        $op = 5;
        $stmt->bind_param("iissis",$op, $idLogin,$nuevoHash);
        return $stmt->execute();
    }
    

    /*public function crearTiketRestablecerContrasena($cuenta) {
        $stmt = $this->conn->prepare("CALL sp_login(?, NULL, ?, NULL, NULL, NULL, NULL)");
        $op = 7;
        $stmt->bind_param("is", $op, $cuenta);
        return $stmt->execute();
    }*/

    public function obtenerCuentaPorCorreo($correo) {
        $stmt = $this->conn->prepare("CALL sp_login(?, NULL, NULL, NULL, NULL, NULL, ?)");
        $op = 5;
        $stmt->bind_param("is", $op, $correo);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function guardarToken($sesion){
        $stmt = $this->conn->prepare("CALL sp_login(?, null, null, null, null, ?, NULL)");
        $op = 6;
        $stmt->bind_param("iissis",$op,$sesion);
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
 