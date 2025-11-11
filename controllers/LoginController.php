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
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['login_id'] = $usuario['ID_Login'];
            $_SESSION['rol']      = strtoupper(trim($usuario['Rol']));
            $_SESSION['nombre']   = $usuario['Nombre'];

            // Si debe cambiar contraseña
            if ($usuario['DebeCambiarPassword']) {
                header("Location: ../views/cambiar_password.php?id=" . $usuario['ID_Login']);
                exit;
            }

            // Redirección según rol
            switch ($_SESSION['rol']) {
                case 'VENDEDOR':
                    header("Location: ../views/pedido.php");
                    break;
                case 'SUPERVISOR':
                    header("Location: ../views/supervisor_dashboard.php");
                    break;
                case 'ALMACEN':
                    header("Location: ../views/dashboardPedidos.php");
                    break;
                default:
                    header("Location: ../views/dashboard.php");
                    break;
            }
            exit;
        } else {
            header("Location: ../helpers/401.php");
            exit;
        }
    }

    public function cambiarPassword($idLogin, $nuevoPassword)
    {
        $hash = password_hash($nuevoPassword, PASSWORD_DEFAULT);
        $row  = $this->loginModel->actualizarPasswordSP($idLogin, $hash); // <-- recibe array o null

        if ($row && (int)($row['rows_affected'] ?? 0) > 0) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            session_regenerate_id(true);

            // Refresca/asegura sesión con los datos devueltos por el SP
            $_SESSION['login_id']            = (int)($row['ID_Login'] ?? $idLogin);
            $_SESSION['rol']                 = strtoupper(trim($row['Rol'] ?? ($_SESSION['rol'] ?? '')));
            $_SESSION['nombre']              = $row['Nombre'] ?? ($_SESSION['nombre'] ?? '');
            $_SESSION['DebeCambiarPassword'] = 0;

            // Redirección por rol
            switch ($_SESSION['rol']) {
                case 'VENDEDOR':
                    header("Location: ../views/pedido.php");
                    break;
                case 'SUPERVISOR':
                    header("Location: ../views/dashboardPedidos.php");
                    break;
                case 'ALMACEN':
                    header("Location: ../views/dashboardPedidos.php");
                    break;
                default:
                    header("Location: ../views/dashboard.php");
                    break;
            }
            exit;
        }

        // Si falla:
        header("Location: ../views/cambiar_password.php?id={$idLogin}&error=1");
        exit;
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

    /*public function restablecerContrasena(string $correo): array
    {
        $resp = ['ok' => false, 'message' => 'Si la cuenta existe, se enviará un enlace para restablecer la contraseña.'];

        try {
            $correo = trim(mb_strtolower($correo));
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                error_log("[RESET] Email inválido: $correo");
                return $resp;
            }

            $tz = $this->timezone ?? 'America/Tijuana';
            $ahora = new DateTime('now', new DateTimeZone($tz));
            $expiraDT = (clone $ahora)->modify('+20 minutes');
            error_log("[RESET] TZ=$tz ahora={$ahora->format('Y-m-d H:i:s')} expira={$expiraDT->format('Y-m-d H:i:s')}");

            $usuario = $this->loginModel->obtenerCuentaPorCorreo($correo);
            $idLogin = $usuario ? (int)$usuario['ID_Usuario'] : 0;
            error_log("[RESET] Usuario? ".($usuario ? "SI id=$idLogin" : "NO"));

            if ($usuario) {
                // Asegúrate que la firma de generarToken($idLogin, $ahora) exista
                $tokenPlano = generarToken($idLogin, $ahora);
                if (!$tokenPlano) {
                    error_log("[RESET] generarToken devolvió vacío");
                    return $resp;
                }
                $fechaExp = $expiraDT->format('Y-m-d H:i:s');
                error_log("[RESET] TokenPlano=$tokenPlano expira=$fechaExp");

                // OJO: aquí decides si guardas token plano ; sé consistente con tu tabla y con marcarTokenUsado()
                $ok = $this->loginModel->guardarToken($idLogin, $tokenPlano, $fechaExp);
                error_log("[RESET] guardarToken => ".var_export($ok, true));

                if ($ok) {
                    $emailRes = $this->enviaCorreoParaRestablecerContrasena($correo, $tokenPlano, $fechaExp);
                    error_log("[RESET] enviarCorreo => ".var_export($emailRes, true));

                    if(is_array($emailRes) && !empty($emailRes)){
                        if($emailRes['ok'] === true){
                            $resp['ok']      = true;
                            $resp['message'] = 'Se envió el correo con el enlace de restablecimiento.';
                        } else {
                            try {
                                $this->loginModel->marcarTokenUsado($tokenPlano);
                                error_log("[RESET] marcarTokenUsado tras fallo de envío => OK");
                            } catch (\Throwable $e2) {
                                error_log("[RESET][ERROR marcarTokenUsado] {$e2->getMessage()}");
                            }
                            // Adjunta detalle del fallo de correo
                            $resp['ok']      = false;
                            $resp['message'] = 'No se pudo enviar el correo de restablecimiento.';
                            $resp['mail']    = $mailRes; // para inspección arriba
                        }
                    } else {
                        // Seguridad: si por alguna razón no es array, trátalo como fallo
                        $resp['ok']      = false;
                        $resp['message'] = 'Error desconocido al enviar el correo.';
                    }
                } else {
                    error_log("[RESET][ERROR guardarToken] No se pudo guardar el token (ID_Login=$idLogin)");
                }
            }
            return $resp;
        } catch (\Throwable $e) {
            error_log("[RESET][EXCEPTION] {$e->getMessage()} en {$e->getFile()}:{$e->getLine()}");
            return $resp;
        }
    }*/

    public function restablecerContrasena(string $correo): bool
    {
        // Respuesta uniforme para evitar enumeración de cuentas
        $resp = false;

        try {
            $tz    = $this->timezone ?? 'America/Tijuana';
            $ahora = new DateTime('now', new DateTimeZone($tz));

            // Normaliza y valida email
            $correo = trim(mb_strtolower($correo, 'UTF-8'));
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                // Mensaje neutro hacia fuera; false
                return false;
            }

            // Busca cuenta
            $usuario = $this->loginModel->obtenerCuentaPorCorreo($correo);

            // No reveles si existe o no; solo procede si existe
            if (!$usuario) {
                return false; // respuesta neutra
            }

            $idLogin = (int)($usuario['ID_Usuario'] ?? 0);
            if ($idLogin <= 0) {
                return false; // seguridad extra
            }

            // Opcional pero recomendado: invalida tokens previos vigentes de este usuario
            try {
                //$this->loginModel->invalidarTokensVigentesPorUsuario($idLogin);
            } catch (\Throwable $eInv) {
                error_log('[WARN invalidarTokensVigentesPorUsuario] ' . $eInv->getMessage());
                // No abortamos: no es crítico para seguir
            }

            // Genera token (PLANO para el link, según tu decisión actual)
            // Asegúrate que generarToken($idLogin, DateTime $ahora) exista y sea estable
            $tokenPlano = generarToken($idLogin, $ahora);

            // Expiración (+20 minutos)
            $expiraDT = (clone $ahora)->modify('+20 minutes');
            $fechaExp = $expiraDT->format('Y-m-d H:i:s');

            // Guarda token + expiración (estás guardando PLANO)
            $okGuardar = $this->loginModel->guardarToken($idLogin, $tokenPlano, $fechaExp);
            if (!$okGuardar) {
                error_log('[ERROR guardarToken] No se pudo guardar el token para ID_Login=' . $idLogin);
                return false; // neutro
            }

            // Envía correo con token PLANO
            $enviado = $this->enviaCorreoParaRestablecerContrasena($correo, $tokenPlano, $fechaExp);

            if (!$enviado) {
                // Revertir: marca token como usado para que quede inválido
                try {
                    $this->loginModel->marcarTokenUsado($tokenPlano);
                } catch (\Throwable $e2) {
                    error_log('[ERROR marcarTokenUsado] ' . $e2->getMessage());
                }
                return false; // neutro
            }

            // Todo OK
            return true;
        } catch (\Throwable $e) {
            error_log('[ERROR restablecerContrasena] ' . $e->getMessage());
            return false; // neutro
        }
    }



    public function enviaCorreoParaRestablecerContrasena($correo, $token, $fechaExpiracion): bool
    {
        $link = "http://172.30.11.8:8080/tikets/public/nueva_contrasena.php?token=" . $token;

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

                        <p style="margin:0 0 16px;"></p>
                        <p style="word-break:break-all;margin:0 0 16px;">
                            <a href="{$link}" style="color:#003366;"></a>
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

            Válido hasta: {$fechaFmt} (hora local)

            Si tú no solicitaste este cambio, ignora este correo.

            Contacto de soporte:
            - Manuel Díaz (Ext. 1120) – m.diaz@empacadorarosarito.com.mx
            - Emilio Zamora (Ext. 1153) – e.zamora@empacadorarosarito.com.mx
            - Edgar Gómez (Ext. 1140) – e.gomez@empacadorarosarito.com.mx

            © 2025 Empacadora Rosarito.";

        $email = new EmailHelper();
        $res = $email->enviarCorreo($correo, $asunto, $mensaje, $altBody);

        // Normaliza a booleano según respuesta del helper
        $ok = is_array($res) ? (bool)($res['ok'] ?? false) : (bool)$res;

        if (!$ok) {
            error_log("[RESET][MAIL] ERROR: no se pudo enviar el correo a $correo");
        }

        return $ok;
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
    /*public function validarToken(string $tokenPlano)
    {
        //echo $tokenPlano;
        //$row = $this->loginModel->buscarToken($tokenPlano);
        if (!is_array($row) || !isset($row['user_id'])) {
            error_log('[validarToken] Respuesta inesperada de buscarToken: ' . var_export($row, true));
            return 0;
        }
    
        $user_id = (int)$row['user_id'];
        return $user_id;
    }*/

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


    /*public function cambiarPasswordConToken($token, $nuevoPassword)
    {
        $hash = password_hash($nuevoPassword, PASSWORD_DEFAULT);
        $pre = $this->loginModel->buscarToken($token);
        $idLogin = (int)$pre['user_id'];
        
        //echo "\n idlogin: $idLogin";

        if ($idLogin <= 0) {
            return false;
        }

        $this->loginModel->actualizarPasswordSP($idLogin, $hash);
        $this->loginModel->marcarTokenUsado($token);
        echo "\n ok: $ok";
        if(!$ok){
            return false;
        }

        $marcarUso = 
        echo "\n marcarUso: $marcarUso";
        return true;
    }*/
}
