<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/pages/contact.php');
}

// Obtener y limpiar datos del formulario
$nombre = cleanInput($_POST['nombre'] ?? '');
$email = cleanInput($_POST['email'] ?? '');
$asunto = cleanInput($_POST['asunto'] ?? '');
$mensaje = cleanInput($_POST['mensaje'] ?? '');
$telefono = cleanInput($_POST['telefono'] ?? '');

// Validar campos obligatorios
if (empty($nombre) || empty($email) || empty($asunto) || empty($mensaje)) {
    setAlert('danger', 'Por favor, completa todos los campos obligatorios.');
    redirect(SITE_URL . '/pages/contact.php');
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setAlert('danger', 'Por favor, introduce un email válido.');
    redirect(SITE_URL . '/pages/contact.php');
}

// Guardar mensaje en la base de datos
$db = getDB();
$data = [
    'nombre' => $nombre,
    'email' => $email,
    'asunto' => $asunto,
    'mensaje' => $mensaje,
    'telefono' => $telefono,
    'fecha' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'],
    'estado' => 'pendiente'
];

$result = insert('contactos', $data);

if (!$result) {
    setAlert('danger', 'Ha ocurrido un error al enviar tu mensaje. Por favor, inténtalo de nuevo más tarde.');
    redirect(SITE_URL . '/pages/contact.php');
}

// Enviar email de notificación
$to = 'contacto@cea.edu.mx'; // Usar una constante o variable de configuración
$subject = "Nuevo mensaje de contacto: $asunto";

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: $nombre <$email>" . "\r\n";

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
        .footer {
            background-color: #f5f5f5;
            padding: 10px 20px;
            border-radius: 0 0 5px 5px;
            font-size: 12px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Nuevo Mensaje de Contacto</h2>
        </div>
        <div class='content'>
            <p>Has recibido un nuevo mensaje de contacto a través del formulario del sitio web:</p>
            
            <table>
                <tr>
                    <th>Nombre:</th>
                    <td>$nombre</td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td>$email</td>
                </tr>
                <tr>
                    <th>Teléfono:</th>
                    <td>$telefono</td>
                </tr>
                <tr>
                    <th>Asunto:</th>
                    <td>$asunto</td>
                </tr>
                <tr>
                    <th>Mensaje:</th>
                    <td>$mensaje</td>
                </tr>
                <tr>
                    <th>Fecha:</th>
                    <td>" . date('d/m/Y H:i:s') . "</td>
                </tr>
                <tr>
                    <th>IP:</th>
                    <td>" . $_SERVER['REMOTE_ADDR'] . "</td>
                </tr>
            </table>
            
            <p>Puedes responder directamente a este correo para contactar con el remitente.</p>
        </div>
        <div class='footer'>
            <p>Este mensaje ha sido enviado desde el formulario de contacto del sitio web del Centro de Estudios Avanzados.</p>
        </div>
    </div>
</body>
</html>
";

// Intentar enviar el email
$mailSent = mail($to, $subject, $emailBody, $headers);

// Enviar email de confirmación al usuario
$toUser = $email;
$subjectUser = "Hemos recibido tu mensaje - Centro de Estudios Avanzados";

$emailBodyUser = "
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
            <h2>Hemos recibido tu mensaje</h2>
        </div>
        <div class='content'>
            <p>Estimado/a $nombre,</p>
            
            <p>Gracias por contactar con el Centro de Estudios Avanzados. Hemos recibido tu mensaje y te responderemos lo antes posible.</p>
            
            <p>A continuación, te recordamos los detalles de tu mensaje:</p>
            
            <p><strong>Asunto:</strong> $asunto</p>
            <p><strong>Mensaje:</strong> $mensaje</p>
            
            <p>Si tienes alguna pregunta adicional, no dudes en contactarnos.</p>
            
            <p>Saludos cordiales,</p>
            <p>El equipo del Centro de Estudios Avanzados</p>
        </div>
        <div class='footer'>
            <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
        </div>
    </div>
</body>
</html>
";

$headerUser = "MIME-Version: 1.0" . "\r\n";
$headerUser .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headerUser .= "From: Centro de Estudios Avanzados <contacto@cea.edu.mx>" . "\r\n";

$mailUserSent = mail($toUser, $subjectUser, $emailBodyUser, $headerUser);

// Mostrar mensaje de éxito
setAlert('success', 'Tu mensaje ha sido enviado correctamente. Nos pondremos en contacto contigo lo antes posible.');
redirect(SITE_URL . '/pages/contact.php');