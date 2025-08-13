<?php
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