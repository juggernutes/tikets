<?php
require_once __DIR__ . '/../models/login.php';
require_once __DIR__ . '/../helpers/EmailHelper.php';
require_once __DIR__ . '/../helpers/herramientas.php';


class LoginController
{
    private $loginModel;
    private $timezone ;

    public function __construct($conn)
    {
        $this->loginModel = new Login($conn);
        $this->timezone = 'America/Tijuana';
        date_default_timezone_set($this->timezone);
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

        // Verificar si se encontró el usuario
        if ($Usuario) {
            // Generar el inicio del token con la fecha utilizando la letra indice de la fecha

            $ahora = new DateTime('now', new DateTimeZone($this->timezone));
            $token = generarToken($Usuario['ID_Usuario'], $ahora);
            $fechaExp= (clone $ahora)->modify('+20 minutes')->format('Y-m-d H:i');
            $idLogin = (int)$Usuario['ID_Usuario'];
            echo "Token: $token, Expiración: $fechaExpiracion";

            $ok = $this->loginModel->guardarToken($idLogin, $token, $fechaExpiracion);

            if ($ok) { // si guardó bien
                $enviado = $this->enviaCorreoParaRestablecerContrasena($correo, $token, $fechaExpiracion);
                if ($enviado) {
                    echo "Se ha enviado un enlace para restablecer la contraseña a su correo.";
                } else {
                    echo "Error al enviar el correo de restablecimiento.";
                }
            } else {
                echo "Error al guardar el token.";
            }

        } else {
            echo "No se encontró una cuenta asociada a ese correo.";
        }
    }

    public function enviaCorreoParaRestablecerContrasena($correo, $token, $fechaExpiracion)
    {
        $link = "http://localhost/tikets/public/nueva_contrasena.php?token=" . $token;

       $asunto = "Instrucciones para restablecer tu contraseña";
            $fechaFmt = date('d/m/Y H:i', strtotime($fechaExpiracion));
            $mensaje = <<<HTML
            <!DOCTYPE html>
            <html lang="es">
            <head>
            <meta charset="UTF-8">
            <title>Restablecimiento de contraseña</title>
            </head>
            <body style="margin:0;padding:0;background:#f7f7f9;font-family:Segoe UI,Arial,sans-serif;color:#222;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f7f9;padding:24px 0;">
                <tr>
                <td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #eee;">
                    <tr>
                        <td style="background:#c8102e;color:#ffffff;padding:16px 24px;font-size:18px;font-weight:600; text-align:center;">
                        Empacadora Rosarito — Tecnologías de la información
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                        <p style="margin:0 0 12px;">Hola,</p>
                        <p style="margin:0 0 12px;">Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.</p>
                        <p style="margin:0 0 16px;">Para continuar, haz clic en el siguiente botón:</p>

                        <p style="text-align:center;margin:24px 0;">
                            <a href="{$link}" style="display:inline-block;padding:12px 24px;background:#c8102e;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:600;">
                            Restablecer contraseña
                            </a>
                        </p>

                        <p style="margin:0 0 16px;">Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                        <p style="word-break:break-all;margin:0 0 16px;">
                            <a href="{$link}" style="color:#003366;">{$link}</a>
                        </p>

                        <p style="margin:0 0 16px;">Este enlace estará disponible hasta <strong>{$fechaFmt} (hora Tijuana)</strong>.</p>
                        <p style="margin:0 0 16px;color:#555;">Si tú no solicitaste este cambio, ignorar este mensaje. Tu contraseña actual seguirá siendo válida.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 24px;background:#fafafa;color:#555;font-size:13px;line-height:1.5;">
                        <p style="margin:0 0 8px;"><strong>Contacto de soporte</strong></p>
                        <p style="margin:0;">Manuel Díaz — <a href="mailto:m.diaz@empacadorarosarito.com.mx">m.diaz@empacadorarosarito.com.mx</a> (Ext. 1120)</p>
                        <p style="margin:0;">Emilio Zamora — <a href="mailto:e.zamora@empacadorarosarito.com.mx">e.zamora@empacadorarosarito.com.mx</a> (Ext. 1153)</p>
                        <p style="margin:0;">Edgar Gómez — <a href="mailto:e.gomez@empacadorarosarito.com.mx">e.gomez@empacadorarosarito.com.mx</a> (Ext. 1140)</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:12px 24px;background:#ffffff;color:#999;font-size:12px;border-top:1px solid #eee;">
                        Este mensaje fue enviado automáticamente; por favor, no respondas a este correo.<br>
                        © 2025 Empacadora Rosarito. Todos los derechos reservados.
                        </td>
                    </tr>
                    </table>
                </td>
                </tr>
            </table>
            </body>
            </html>
            HTML;

            $altBody = "Hola,

            Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.

            Enlace: {$link}
            Válido hasta: {$fechaFmt} (hora local)

            Si tú no solicitaste este cambio, ignora este correo.

            Contacto de soporte:
            - Manuel Díaz (Ext. 1120) – m.diaz@empacadorarosarito.com.mx
            - Emilio Zamora (Ext. 1153) – e.zamora@empacadorarosarito.com.mx
            - Edgar Gómez (Ext. 1140) – e.gomez@empacadorarosarito.com.mx

            © 2025 Empacadora Rosarito.";

        $email = new EmailHelper();
        return $email->enviarCorreo($correo, $asunto, $mensaje, $altBody);
    }

    public function validarToken($token)
    {
        // $row = $this->loginModel->validarToken($token);
        list($prefix, $secret) = parcearToken($token) ?? [null, null];
        if(!$prefix){
            return ['ok' => false, 'user_id' => 0];
        }

        $row = $this->loginModel->validarToken($token);
        if (is_array($row) && !empty($row['user_id'])) {
            return ['ok' => true, 'user_id' => (int)($row['user_id'])];
        }

        return ['ok' => false, 'user_id' => 0];
    }

    public function cambiarPasswordConToken($token, $nuevaPassword)
    {
        $hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
        $idLogin = $this->validarToken($token);
        if ($idLogin) {
            $this->cambiarPassword($idLogin, $hash);
        } else {
            echo "Token inválido o expirado.";
        }
    }
}
