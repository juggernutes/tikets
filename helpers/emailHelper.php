<?php
/*use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP; 

require __DIR__ . '/../vendor/autoload.php';

class EmailHelper
{

/**
 * Envía un correo y regresa un arreglo con detalles del resultado.
 * - ok: bool (true si se envió)
 * - message: string (explicación humana)
 * - error: string|null (excepción u observación)
 * - error_info: string|null (PHPMailer->ErrorInfo)
 * - debug: string|null (traza SMTP si habilitas debug)
 */
    /*public function enviarCorreo(string $correo, string $asunto, string $mensajeHtml, ?string $altBody = null): array
    {
        $mail = new PHPMailer(true);

        // Para capturar el debug en una variable (opcional)
        $smtpLog = '';
        $debugEnabled = getenv('MAIL_DEBUG') === '1'; // activa con MAIL_DEBUG=1

        try {
            // SMTP Office 365
            $mail->isSMTP();
            $mail->Host       = getenv('MAIL_HOST') ?: 'smtp.office365.com';
            $mail->Port       = (int)(getenv('MAIL_PORT') ?: 587);
            $mail->SMTPAuth   = true;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Username   = getenv('MAIL_USER') ?: 'ti@empacadorarosarito.com.mx';
            $mail->Password   = getenv('MAIL_PASS') ?: '***CAMBIA_ESTA_CONTRASEÑA***';
            $mail->CharSet    = 'UTF-8';

            // Timeouts y estabilidad
            $mail->Timeout       = 15;        // seg
            $mail->SMTPKeepAlive = false;

            // TLS fuerte (evita TLS1.0/1.1)
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => true,
                    'verify_peer_name'  => true,
                    'allow_self_signed' => false,
                    'ciphers'           => 'TLSv1.2+HIGH:!aNULL:!MD5'
                ]
            ];

            // From (usa la cuenta autenticada)
            $mail->setFrom($mail->Username, 'Soporte TI');
            // Opcional: dirección de respuesta
            // $mail->addReplyTo('no-reply@empacadorarosarito.com.mx', 'No responder');

            // Destinatario
            $mail->addAddress($correo);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $asunto;

            // (Opcional) Incrustar logo en el cuerpo con CID
            $logoPath = realpath(__DIR__ . '/../img/CentroG.png');
            if ($logoPath && is_readable($logoPath)) {
                $mail->addEmbeddedImage($logoPath, 'logoCID', 'logo.png');
                // Si quieres usar el logo, asegúrate de que $mensajeHtml contenga <img src="cid:logoCID">
            }

            $mail->Body    = $mensajeHtml;
            $mail->AltBody = $altBody ?: strip_tags($mensajeHtml);

            // DEBUG controlado
            if ($debugEnabled) {
                $mail->SMTPDebug   = SMTP::DEBUG_SERVER; // 2 = client; 3 = connection; 4 = low-level
                $mail->Debugoutput = static function($str, $level) use (&$smtpLog) {
                    $smtpLog .= "[$level] $str\n";
                };
            }

            // Envío
            $sent = $mail->send();

            return [
                'ok'         => (bool)$sent,
                'message'    => $sent ? 'Correo enviado correctamente.' : 'No se pudo enviar el correo.',
                'error'      => $sent ? null : 'send() devolvió false',
                'error_info' => $sent ? null : $mail->ErrorInfo,
                'debug'      => $debugEnabled ? $smtpLog : null,
            ];

        } catch (Exception $e) {
            // Reintento simple si es error transitorio 4xx (opcional)
            $errorInfo = $mail->ErrorInfo ?? '';
            $isTransient = preg_match('/\b4\d{2}\b/', $errorInfo);

            if ($isTransient) {
                // Pequeño backoff
                usleep(250000); // 250ms
                try {
                    $sentRetry = $mail->send();
                    if ($sentRetry) {
                        return [
                            'ok'         => true,
                            'message'    => 'Correo enviado correctamente en reintento.',
                            'error'      => null,
                            'error_info' => null,
                            'debug'      => $debugEnabled ? $smtpLog : null,
                        ];
                    }
                } catch (Exception $e2) {
                    // Continúa hacia el return final con detalles del segundo intento
                    $errorInfo .= " | Retry: " . ($mail->ErrorInfo ?? $e2->getMessage());
                }
            }

            // Error definitivo
            error_log("PHPMailer exception: " . $e->getMessage());
            error_log("PHPMailer ErrorInfo: " . ($mail->ErrorInfo ?? 'N/A'));

            return [
                'ok'         => false,
                'message'    => 'Fallo al enviar el correo.',
                'error'      => $e->getMessage(),
                'error_info' => $mail->ErrorInfo ?? null,
                'debug'      => $debugEnabled ? $smtpLog : null,
            ];
        }
    }

}*/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP; 

require __DIR__ . '/../vendor/autoload.php';

class EmailHelper
{
    public function enviarCorreo($correo, $asunto, $mensajeHtml, $altBody)
    {
        $mail = new PHPMailer(true);

        // SMTP Office 365
        $mail->isSMTP();
        $mail->Host       = 'smtp.office365.com';
        $mail->Port       = 587;
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Username   = 'ti@empacadorarosarito.com.mx';
        $mail->Password   = 'T1nf0.270';
        $mail->CharSet    = 'UTF-8';
        $logoPath = realpath(__DIR__ . '/../img/CentroG.png');

        // Usa la misma cuenta autenticada como From (evita 5.7.60)
        $mail->setFrom($mail->Username, 'Soporte TI');
        // Si tienes permiso "Send As" para no-reply@, entonces podrías usar:
        // $mail->setFrom('no-reply@empacadorarosarito.com.mx', 'Soporte');

        $mail->addAddress($correo);
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $mensajeHtml;
        $mail->AltBody = $altBody ?: strip_tags($mensajeHtml);

        // DEBUG (solo en desarrollo): envía el diálogo SMTP al error_log
        $mail->SMTPDebug  = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = static function($str, $level) {
            error_log("SMTP Debug [$level]: $str");
        };

        try {
            return $mail->send(); // true/false
        } catch (Exception $e) {
            error_log("PHPMailer exception: " . $e->getMessage());
            error_log("PHPMailer ErrorInfo: " . $mail->ErrorInfo);
            return false;
        }
    }
}