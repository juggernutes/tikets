<?php
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../models/login.php';

session_start();

class LoginController {
    private $loginModel;

    public function __construct($conn) {
        $this->loginModel = new Login($conn);
    }

    public function login($cuenta, $passwordIngresado) {
        $usuario = $this->loginModel->validarUsuarioSP($cuenta);

        if ($usuario && password_verify($passwordIngresado, $usuario['PasswordHash'])) {
            if ($usuario['DebeCambiarPassword']) {
                // Redirigir a formulario de cambio de contraseña
                header("Location: ../views/cambiar_password.php?id=" . $usuario['ID_Login']);
                exit;
            } else {
                // Registrar sesión y redirigir al dashboard
                $_SESSION['login_id'] = $usuario['ID_Login'];
                $this->loginModel->registrarSesionSP($usuario['ID_Login'], session_id());
                header("Location: ../views/dashboard.php");
                exit;
            }
        } else {
            echo "Usuario o contraseña incorrectos.";
        }
    }

    public function cambiarPassword($idLogin, $nuevoPassword) {
        $hash = password_hash($nuevoPassword, PASSWORD_DEFAULT);
        $resultado = $this->loginModel->actualizarPasswordSP($idLogin, $hash);

        if ($resultado) {
            echo "Contraseña actualizada correctamente. <a href='../public/index.php'>Iniciar sesión</a>";
        } else {
            echo "Hubo un error al actualizar la contraseña.";
        }
    }
}
