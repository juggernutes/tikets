<?php
require_once __DIR__ . '/../models/login.php';
require_once __DIR__ . '/../helpers/EmailHelper.php';
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

    public function restablecerContrasena($correo) {
        $Userid = $this->loginModel->obtenerCuentaPorCorreo($correo);
        if ($Userid) {
            $token = bin2hex(random_bytes(32));
            $fechaExpiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $this->loginModel->guardarToken($Userid['ID_Login'], $token,$fechaExpiracion);
            $this->enviaCorreoParaRestablecerContrasena($correo, $token, $fechaExpiracion);
            echo "Se ha enviado un enlace para restablecer la contraseña a su correo.";
        } else {
            echo "No se encontró una cuenta asociada a ese correo.";
        }
    }
    /*public function enviaCorreoParaRestablecerContrasena($cuenta) {
        $ok = $this->loginModel->crearTiketRestablecerContrasena($cuenta);
        if ($ok) {
            echo "Ticket creado para restablecer la contraseña.";
        } else {
            echo "Error al crear el ticket.";
        }
    }*/

}
