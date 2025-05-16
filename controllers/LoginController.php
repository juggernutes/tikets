<?php
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
            $_SESSION['login_id'] = $usuario['ID_Login'];
            $_SESSION['rol'] = $usuario['Rol'];

            if ($usuario['DebeCambiarPassword']) {
                header("Location: ../views/cambiar_password.php?id=" . $usuario['ID_Login']);
            } else {
                header("Location: ../views/dashboard.php");
            }
            exit;
        } else {
            echo "Usuario o contraseña incorrectos.";
        }
    }

    public function cambiarPassword($idLogin, $nuevoPassword) {
        $hash = password_hash($nuevoPassword, PASSWORD_DEFAULT);
        $ok = $this->loginModel->actualizarPasswordSP($idLogin, $hash);

        if ($ok) {
            echo "Contraseña actualizada correctamente. <a href='../public/index.php'>Iniciar sesión</a>";
        } else {
            echo "Error al cambiar la contraseña.";
        }
    }
}
