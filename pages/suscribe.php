<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL);
}

// Obtener y limpiar email
$email = cleanInput($_POST['email'] ?? '');
$origen = cleanInput($_POST['origen'] ?? 'general');
$redirect = isset($_POST['redirect']) ? cleanInput($_POST['redirect']) : SITE_URL;

// Validar email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setAlert('danger', 'Por favor, introduce un email válido.');
    redirect($redirect);
}

// Verificar si el email ya está suscrito
$db = getDB();
$existeEmail = $db->getRow("SELECT id FROM suscriptores WHERE email = :email", ['email' => $email]);

if ($existeEmail) {
    setAlert('info', 'Este email ya está suscrito a nuestro boletín.');
    redirect($redirect);
}

// Guardar suscripción en la base de datos
$data = [
    'email' => $email,
    'fecha_suscripcion' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'],
    'origen' => $origen,
    'estado' => 'activo',
    'token' => md5(uniqid(rand(), true))
];

$result = $db->insert('suscriptores', $data);

if (!$result) {
    setAlert('danger', 'Ha ocurrido un error al procesar tu suscripción. Por favor, inténtalo de nuevo más tarde.');
    redirect($redirect);
}

// Enviar email de confirmación
$to = $email;
$subject = "Confirmación de suscripción - Centro de Estudios Avanzados";

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: Centro de Estudios Avanzados <contacto@cea.edu.mx>" . "\r\n";

$emailBody = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            background-color: #002B5B;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 20px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #002B5B;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 10px 20px;
            border-radius: 0 0 5px 5px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>¡Gracias por suscribirte!</h2>
        </div>
        <div class='content'>
            <p>Estimado/a suscriptor/a,</p>
            
            <p>Gracias por suscribirte al boletín del Centro de Estudios Avanzados. A partir de ahora, recibirás información sobre:</p>
            
            <ul>
                <li>Nuevos cursos y programas académicos</li>
                <li>Convocatorias y becas</li>
                <li>Eventos y conferencias</li>
                <li>Noticias relevantes del ámbito académico</li>
            </ul>
            
            <p>Si en algún momento deseas cancelar tu suscripción, puedes hacerlo a través del siguiente enlace:</p>
            
            <a href='" . SITE_URL . "/unsubscribe.php?token=" . $data['token'] . "' class='button'>Cancelar suscripción</a>
            
            <p>Saludos cordiales,</p>
            <p>El equipo del Centro de Estudios Avanzados</p>
        </div>
        <div class='footer'>
            <p>Este correo fue enviado a $email porque te has suscrito a nuestro boletín.</p>
            <p>© " . date('Y') . " Centro de Estudios Avanzados. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
";

// Intentar enviar el email
$mailSent = mail($to, $subject, $emailBody, $headers);

// Mostrar mensaje de éxito
setAlert('success', '¡Gracias por suscribirte a nuestro boletín! Hemos enviado un correo de confirmación a tu dirección de email.');
redirect($redirect);