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
    header("Location: " . SITE_URL . "/admin/eventos/");
    exit;
}

$id = $_GET['id'];

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
    
    // Verificar si el evento existe
    $stmt = $db->prepare("SELECT id FROM eventos WHERE id = :id");
    $stmt->execute(['id' => $id]);
    
    if (!$stmt->fetch()) {
        header("Location: " . SITE_URL . "/admin/eventos/");
        exit;
    }
    
    // Eliminar evento
    $stmt = $db->prepare("DELETE FROM eventos WHERE id = :id");
    $stmt->execute(['id' => $id]);
    
    // Redireccionar con mensaje de éxito
    header("Location: " . SITE_URL . "/admin/eventos/index.php?mensaje=eliminado");
    exit;
} catch (PDOException $e) {
    // Redireccionar con mensaje de error
    header("Location: " . SITE_URL . "/admin/eventos/index.php?mensaje=error&error=" . urlencode($e->getMessage()));
    exit;
}