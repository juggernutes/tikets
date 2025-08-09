<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // Ajusta la ruta si es necesario

class EmailHelper
{
    public function enviarCorreo($correo, $asunto, $mensajeHtml)
    {
        $mail = new PHPMailer(true);

        try {
            // Configuración SMTP (Office365)
            $mail->isSMTP();
            $mail->Host       = 'smtp.office365.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ti@empacadorarosarito.com.mx';
            $mail->Password   = 'T1nf0.270';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('no-reply@empacadorarosarito.com.mx', 'Soporte');
            $mail->addAddress($correo);

            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $mensajeHtml;

            // Enviar el correo
            
            $mail->send();
            error_log("Correo enviado a: $correo");

            return true;
        } catch (Exception $e) {

            error_log("Error al enviar correo: {$mail->ErrorInfo}");

            
            return false;
        }
    }
}
