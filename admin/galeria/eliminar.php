<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/admin/login.php");
    exit;
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . SITE_URL . "/admin/galeria/");
    exit;
}

$id = $_GET['id'];
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';

try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Obtener información de la imagen
    $stmt = $db->prepare("SELECT archivo, categoria_id FROM galeria WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $imagen = $stmt->fetch();
    
    if (!$imagen) {
        header("Location: " . SITE_URL . "/admin/galeria/");
        exit;
    }
    
    // Eliminar archivo físico
    $rutaArchivo = '../../uploads/galeria/' . $imagen['archivo'];
    if (file_exists($rutaArchivo)) {
        unlink($rutaArchivo);
    }
    
    // Eliminar registro de la base de datos
    $stmt = $db->prepare("DELETE FROM galeria WHERE id = :id");
    $stmt->execute(['id' => $id]);
    
    // Redireccionar con mensaje de éxito
    $categoriaParam = !empty($categoria) ? "&categoria=" . $categoria : (!empty($imagen['categoria_id']) ? "&categoria=" . $imagen['categoria_id'] : "");
    header("Location: " . SITE_URL . "/admin/galeria/index.php?mensaje=eliminado" . $categoriaParam);
    exit;
} catch (PDOException $e) {
    // Redireccionar con mensaje de error
    header("Location: " . SITE_URL . "/admin/galeria/index.php?mensaje=error&error=" . urlencode($e->getMessage()));
    exit;
}