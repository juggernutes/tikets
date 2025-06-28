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
            $_SESSION['nombre'] = $usuario['Nombre'];

            if ($usuario['DebeCambiarPassword']) {
                header("Location: ../views/cambiar_password.php?id=" . $usuario['ID_Login']);
                exit;  // ✅ AGREGA ESTE EXIT AQUÍ
            } else {
                header("Location: ../views/dashboard.php");
                exit;  // ✅ Y TAMBIÉN AQUÍ
            }
        } else {
            echo "Usuario o contraseña incorrectos.";
        }

    }

    public function cambiarPassword($idLogin, $nuevoPassword) {
        $hash = password_hash($nuevoPassword, PASSWORD_DEFAULT);
        $ok = $this->loginModel->actualizarPasswordSP($idLogin, $hash);

        if ($ok) {
            echo "../public/index.php";
        } else {
            echo "Error al cambiar la contraseña.";
        }
    }
}
