<?php
require_once __DIR__ . '/../models/login.php';
/*require_once __DIR__ . '/../app/appTiket.php';*/
session_start();

class LoginController {
    private $loginModel;

    public function __construct($conn) {
        $this->loginModel = new Login($conn);
    }

    public function login($cuenta, $passwordIngresado) {
        $usuario = $this->loginModel->validarUsuarioSP($cuenta);

        if ($usuario && password_verify($passwordIngresado, $usuario['PasswordHash'])) {
            $_SESSION['login_id'] = $usuario['ID_Login'];
            $_SESSION['rol'] = $usuario['Rol'];
            $_SESSION['nombre'] = $usuario['Nombre'];

            if ($usuario['DebeCambiarPassword']) {
                header("Location: ../views/cambiar_password.php?id=" . $usuario['ID_Login']);
                exit;  
            } else {
                header("Location: ../views/dashboard.php");
                exit;  
            }
        } else {
            echo "Usuario o contraseña incorrectos.";
        }

    }

    public function cambiarPassword($idLogin, $nuevoPassword) {
        $hash = password_hash($nuevoPassword, PASSWORD_DEFAULT);
        $ok = $this->loginModel->actualizarPasswordSP($idLogin, $hash);

        if ($ok) {
            header("Location: ../views/dashboard.php");
        } else {
            echo "Error al cambiar la contraseña.";
        }
    }

    public function reestablecerContrasena($usuario){
        $nuevoPassword = "12345";
        $hash = password_hash($nuevoPassword, PASSWORD_DEFAULT);
        $ok = $this->loginModel->restaurarPws($usuario, $hash);
        if($ok) {
            echo "todo bien";
        } else {
            echo "no se encontro el usuario";
        }
    }
    public function crearTiketRestablecerContrasena($cuenta) {
        $ok = $this->loginModel->crearTiketRestablecerContrasena($cuenta);
        if ($ok) {
            echo "Ticket creado para restablecer la contraseña.";
        } else {
            echo "Error al crear el ticket.";
        }
    }

}
