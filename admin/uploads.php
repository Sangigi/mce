<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

// Verificar si se ha enviado un archivo
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'No se ha enviado ningún archivo o ha ocurrido un error.']);
    exit;
}

// Configuración
$uploadDir = '../uploads/editor/';
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

// Crear directorio si no existe
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Obtener información del archivo
$file = $_FILES['file'];
$fileName = $file['name'];
$fileType = $file['type'];
$fileSize = $file['size'];
$fileTmpName = $file['tmp_name'];

// Validar tipo de archivo
if (!in_array($fileType, $allowedTypes)) {
    header('HTTP/1.1 415 Unsupported Media Type');
    echo json_encode(['error' => 'Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF.']);
    exit;
}

// Validar tamaño de archivo
if ($fileSize > $maxFileSize) {
    header('HTTP/1.1 413 Payload Too Large');
    echo json_encode(['error' => 'El archivo es demasiado grande. El tamaño máximo permitido es 5MB.']);
    exit;
}

// Generar nombre único para el archivo
$fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
$uniqueName = uniqid() . '_' . time() . '.' . $fileExt;
$uploadFile = $uploadDir . $uniqueName;

// Mover el archivo
if (move_uploaded_file($fileTmpName, $uploadFile)) {
    // Éxito
    $location = SITE_URL . '/uploads/editor/' . $uniqueName;
    echo json_encode(['location' => $location]);
} else {
    // Error
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Error al guardar el archivo.']);
}