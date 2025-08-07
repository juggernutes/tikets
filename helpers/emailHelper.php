<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // Ajusta la ruta si es necesario

class EmailHelper
{
    public function enviaCorreoParaRestablecerContrasena($userInfo, $correo)
    {
        $mail = new PHPMailer(true);

        try {
            // Configuración SMTP de Exchange
            $mail->isSMTP();
            $mail->Host       = 'smtp.office365.com';       // Servidor Exchange
            $mail->SMTPAuth   = true;
            //$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  O PHPMailer::ENCRYPTION_SMTPS según config
            $mail->Username   = 'ti@empacadorarosarito.com.mx';    // Cuenta para autenticarse
            $mail->Password   = 'T1nf0.270';                // Contraseña
            $mail->SMTPSecure = 'tls';                       // O 'ssl' según config
            $mail->Port       = 587;                         // 587 (TLS) o 465 (SSL)

            $mail->setFrom('no-reply@empacadorarosarito.com.mx', 'Soporte');
            $mail->addAddress($correo);

            $mail->isHTML(true);
            $mail->Subject = 'Restablece tu contraseña';

            // Genera el token y liga segura
            $token = bin2hex(random_bytes(32));
            


            $link = "https://localhost/tikets/public/nueva_contrasena.php?token=$token";
            $mail->Body = "
                <p>Hola,</p>
                <p>Recibiste este correo porque solicitaste restablecer tu contraseña.</p>
                <p>Haz clic en el siguiente enlace para restablecerla (válido solo una vez):<br>
                <a href='$link'>$link</a></p>
                <p>Si no lo solicitaste, ignora este mensaje.</p>
            ";

            $mail->send();
            // Aquí deberías guardar el token y la expiración en la base de datos
            return true;
        } catch (Exception $e) {
            // Log o mensaje de error
            error_log("Error al enviar correo: {$mail->ErrorInfo}");
            return false;
        }
    }
}
