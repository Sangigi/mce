<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php';

function enviarCorreoRecuperacion($para, $nombre, $enlace) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP de Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sanguineape2189@gmail.com';     // Cambia por tu Gmail
        $mail->Password   = 'fvwr iyqg wjlo cskc'; // Usa la contraseña de aplicación, jamas la real
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Remitente y destinatario
        $mail->setFrom('tu_correo@gmail.com', 'Centro de Estudios Avanzados');
        $mail->addAddress($para, $nombre);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de contraseña - CEA';
        $mail->Body    = "
            <p>Hola <strong>$nombre</strong>,</p>
            <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace:</p>
            <p><a href='$enlace'>$enlace</a></p>
            <p>Este enlace expirará en 1 hora.</p>
            <p>Si no solicitaste esto, ignora este correo.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
