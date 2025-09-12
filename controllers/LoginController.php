<?php
require_once __DIR__ . '/../models/login.php';
require_once __DIR__ . '/../helpers/EmailHelper.php';
require_once __DIR__ . '/../helpers/herramientas.php';


class LoginController
{
    private $loginModel;
    private $timezone;

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

    /*public function restablecerContrasena(string $correo): array
    {
        $resp = ['ok' => false, 'message' => ''];
        try {
            $correo = trim(mb_strtolower($correo));
            $ahora = new DateTime('now', new DateTimeZone($this->timezone ?? 'America/Tijuana'));
            $exireaDT = (clone $ahora)->modify('+20 minutes');
            $usuario = $this->loginModel->obtenerCuentaPorCorreo($correo);
            $resp['message'] = 'Si la cuenta existe, se enviara un enlace para restablecer la contraseña';
            if ($usuario) {
                $idlogin = (int)$usuario['ID_Usuario'];
                $tokenPlano = generarToken($idlogin, $ahora);
                $tokenHash = hash('sha256', $tokenPlano);
                $fechaExp = $exireaDT->format('Y-m-d H:i');
                $ok = $this->loginModel->guardarToken($idlogin, $tokenHash, $fechaExp);
                if ($ok) {
                    $enviado = $this->enviaCorreoParaRestablecerContrasena($correo, $tokenPlano, $fechaExp);
                    if ($enviado) {
                        $resp['ok'] = true;
                        $resp['message'] = "Si la cuenta existe, te enviaremos un correo…";
                    } else {
                        $this->loginModel->invalidarToken($idlogin, $tokenHash);
                        $resp['message'] = "Error al enviar el correo de restablecimiento.";
                    }
                } else {
                    $resp['message'] = "Ocurrió un problema al procesar la solicitud.";
                    return $resp;
                }
            }
            return $resp;
        } catch (Throwable $e) {
            error_log('[ERROR] ' . $e->getMessage());
            $resp['message'] = 'Error al procesar el correo.';
            return $resp;
        } /*$Usuario = $this->loginModel->obtenerCuentaPorCorreo($correo); if ($Usuario) {// Verificar si se encontró el usuario // Generar el inicio del token con la fecha utilizando la letra indice de la fecha $ahora = new DateTime('now', new DateTimeZone($this->timezone)); $token = generarToken($Usuario['ID_Usuario'], $ahora); $fechaExp= (clone $ahora)->modify('+20 minutes')->format('Y-m-d H:i'); $idLogin = (int)$Usuario['ID_Usuario']; $ok = $this->loginModel->guardarToken($idLogin, $token, $fechaExp); if ($ok) { // si guardó bien $enviado = $this->enviaCorreoParaRestablecerContrasena($correo, $token, $fechaExp); if ($enviado) { echo "Si la cuenta existe, te enviaremos un correo…"; } else { echo "Error al enviar el correo de restablecimiento."; } } else { echo "Error al guardar el token.";} } else { echo "No se encontró una cuenta asociada a ese correo.";} */
    /*}*/

    public function restablecerContrasena(string $correo)
    {
        // Respuesta uniforme para evitar enumeración de cuentas
        $resp = false;

        try {
            $correo = trim(mb_strtolower($correo));

            // Valida email antes de seguir
            //if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                // Mismo mensaje neutro al usuario
                //return $resp;
            //}

            $tz = $this->timezone ?? 'America/Tijuana';
            $ahora = new DateTime('now', new DateTimeZone($tz));
            $expiraDT = (clone $ahora)->modify('+20 minutes');

            // Busca cuenta
            $usuario = $this->loginModel->obtenerCuentaPorCorreo($correo);

            // No reveles si existe o no; continúa solo si existe
            if ($usuario) {
                $idLogin = (int)$usuario['ID_Usuario'];
                if ($idLogin <= 0) {
                    return $resp; // seguridad extra
                }
                // Genera token (plano para el link), hashea para guardar
                $tokenPlano = generarToken($idLogin, $ahora);        // asegúrate que esta firma exista
                //$tokenHash  = hash('sha256', $tokenPlano);
                $fechaExp   = $expiraDT->format('Y-m-d H:i:s');

                // Guarda token (hash) + expiración
                $ok = $this->loginModel->guardarToken($idLogin, $tokenPlano, $fechaExp);

                if ($ok) {
                    // Envía correo con el token PLANO
                    $enviado = $this->enviaCorreoParaRestablecerContrasena($correo, $tokenPlano, $fechaExp);

                    if (!$enviado) {
                        // Revierte si no se pudo enviar
                        try {
                            $this->loginModel->marcarTokenUsado($tokenPlano);
                        } catch (\Throwable $e2) {
                            error_log('[ERROR marcarTokenUsado] ' . $e2->getMessage());
                        }
                        // Mantén respuesta neutra al usuario
                        return $resp;

                    } else {
                        $resp = true;
                        return $resp;
                    }
                } else {
                    // Mantén respuesta neutra, log interno
                    error_log('[ERROR guardarToken] No se pudo guardar el token para ID_Login=' . $idLogin);
                }
            }
        } catch (\Throwable $e) {
            error_log('[ERROR restablecerContrasena] ' . $e->getMessage());
            // Mantén respuesta neutra para el usuario
            return $resp;
        }
    }



    public function enviaCorreoParaRestablecerContrasena($correo, $token, $fechaExpiracion): bool
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

    /*public function validarToken($token)
    {
        // $row = $this->loginModel->validarToken($token);
        list($prefix, $secret) = parcearToken($token) ?? [null, null];
        if (!$prefix) {
            return ['ok' => false, 'user_id' => 0];
        }

        $row = $this->loginModel->validarToken($token);
        if (is_array($row) && !empty($row['user_id'])) {
            return ['ok' => true, 'user_id' => (int)($row['user_id'])];
        }

        return ['ok' => false, 'user_id' => 0];
    }*/

    /**
     * Valida un token de restablecimiento enviado en el link (plano).
     * - Hashea el token y lo busca en BD.
     * - Verifica expiración y si ya fue usado.
     * - (Opcional) marca el token como usado.
     */
    public function validarToken(string $tokenPlano, bool $marcarUso = false): array
    {
        $resp = ['ok' => false, 'user_id' => 0, 'message' => 'Token inválido o expirado.', 'code' => 'invalido'];

        // 1) Formato rápido: [A-J]{14}[0-9]{6}-[0-9a-f]{32}  (total ~53 chars)
        //if (!self::parsearToken($tokenPlano)) {
        //    return $resp; // no revelar detalles
        //}

        // 2) Hash del token plano para consultar en BD
        //$tokenHash = hash('sha256', $tokenPlano);

        // 3) Buscar en BD por hash
        //    Devuelve: ['id','user_id','expires_at','used'] o null

        $row = $this->loginModel->buscarTokenPorHash($tokenPlano);
        if ($row) {
            $resp = [
                'ok'      => true,
                'user_id' => (int)$row['user_id'],
                'message' => 'OK',
                'code'    => 'OK'
            ];
            return $resp;
        }

        /*// 4) Verificar expiración y usado
        $tz   = $this->timezone ?? 'America/Tijuana';
        $ahora = new DateTimeImmutable('now', new DateTimeZone($tz));
        $exp   = new DateTimeImmutable($row['expires_at'] ?? '1970-01-01 00:00:00');

        if (!empty($row['used']) || $exp < $ahora) {
            return $resp;
        }

        // 5) (Opcional) marcar como usado ya mismo
        if ($marcarUso) {
            try {
                $this->loginModel->marcarTokenUsado($tokenPlano);
            } catch (\Throwable $e) {
                error_log('[WARN marcarTokenUsado] ' . $e->getMessage());
                // Aun así podemos permitir continuar si no fue posible marcar por ahora
            }
        }*/

        return $resp;
    }

    /**
     * Valida el patrón del token y retorna partes si aplica.
     * Prefijo: 14 letras (A-J) de fecha codificada + 6 dígitos de userId,
     * Guion, y 32 hex de secreto.
     
     *private static function parsearToken(string $token): ?array
     *{
     *   if (!preg_match('/^([A-J]{14}\d{6})-([0-9a-f]{32})$/i', $token, $m)) {
     *      return null;
     *  }
     * return ['prefix' => $m[1], 'secret' => $m[2]];
     *}
     */


    public function cambiarPasswordConToken($token, $nuevoPassword)
    {
        $hash = password_hash($nuevoPassword, PASSWORD_DEFAULT);
        $pre = $this->loginModel->buscarTokenPorHash($token);
        $idLogin = (int)$pre['user_id'];
        
        //echo "\n idlogin: $idLogin";

        if ($idLogin <= 0) {
            return false;
        }

        $this->loginModel->actualizarPasswordSP($idLogin, $hash);
        $this->loginModel->marcarTokenUsado($token);
        /*echo "\n ok: $ok";
        if(!$ok){
            return false;
        }

        $marcarUso = 
        echo "\n marcarUso: $marcarUso";*/
        return true;
        /*if ($idLogin) {
            $ok = $this->loginModel->actualizarPasswordSP($idLogin, $hash);
            if ($ok) {
                // Marca el token como usado
                try {
                    $this->loginModel->marcarTokenUsado($token);
                } catch (\Throwable $e) {
                    error_log('[WARN marcarTokenUsado] ' . $e->getMessage());
                    // Aun así podemos permitir continuar si no fue posible marcar por ahora
                }
            }else {

                echo "no se guardo la nueva contraseña";
                echo $idLogin;
            }
            return $ok;
        } else {
            return false;
        }
        //liberamos recursos
        $stmt->close();*/
    }
}
