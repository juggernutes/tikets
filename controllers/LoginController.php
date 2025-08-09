<?php
require_once __DIR__ . '/../models/login.php';
require_once __DIR__ . '/../helpers/EmailHelper.php';
session_start();

class LoginController
{
    private $loginModel;

    public function __construct($conn)
    {
        $this->loginModel = new Login($conn);
    }

    public function login($cuenta, $passwordIngresado)
    {
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

    public function cambiarPassword($idLogin, $nuevoPassword)
    {
        $hash = password_hash($nuevoPassword, PASSWORD_DEFAULT);
        $ok = $this->loginModel->actualizarPasswordSP($idLogin, $hash);

        if ($ok) {
            header("Location: ../views/dashboard.php");
        } else {
            echo "Error al cambiar la contraseña.";
        }
    }

    public function restablecerContrasena($correo)
    {
        $Usuario = $this->loginModel->obtenerCuentaPorCorreo($correo);

        if ($Usuario) {

            $token = bin2hex(random_bytes(32));
            $idLogin = (int)$Usuario['ID_Login'];
            $fechaExpiracion = date('Y-m-d H:i:s', strtotime('+20 minutes'));

            echo $token;
            echo "<br>";
            echo $fechaExpiracion;
            echo "<br>";
            echo $idLogin;
            echo "<br>";
            print_r($Usuario);

            $this->loginModel->guardarToken($idLogin, $token, $fechaExpiracion);

            $this->enviaCorreoParaRestablecerContrasena($correo, $token, $fechaExpiracion);

            echo "Se ha enviado un enlace para restablecer la contraseña a su correo.";
        } else {
            echo "No se encontró una cuenta asociada a ese correo.";
        }
    }

    public function enviaCorreoParaRestablecerContrasena($correo, $token, $fechaExpiracion)
    {
        $link = "http://localhost/tikets/public/nueva_contrasena.php?token=" . $token;

        $asunto = "Restablecer contraseña";
        $mensaje = "
            <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
            <p>Haz clic en el siguiente enlace para restablecerla:</p>
            <p><a href='$link'>$link</a></p>
            <p>Este enlace es válido hasta: $fechaExpiracion</p>
            <p>Si no solicitaste esto, puedes ignorar este correo.</p>
        ";

        $email = new EmailHelper();
        $enviado = $email->enviarCorreo($correo, $asunto, $mensaje);

        if ($enviado) {
            echo "Correo de restablecimiento enviado correctamente.";
        } else {
            echo "Error al enviar el correo de restablecimiento.";
        }
    }

    public function validarToken($token)
    {
        $datos = $this->loginModel->validarToken($token);
        if ($datos && strtotime($datos['FechaExpiracion']) > time()) {
            return $datos['ID_Login'];
        }
        return false;
    }

    public function cambiarPasswordConToken($token, $nuevaPassword)
    {
        $idLogin = $this->validarToken($token);
        if ($idLogin) {
            $this->cambiarPassword($idLogin, $nuevaPassword);
        } else {
            echo "Token inválido o expirado.";
        }
    }
}
