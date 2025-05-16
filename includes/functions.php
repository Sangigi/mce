<?php
require_once 'db.php';

// Función para limpiar datos de entrada
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para generar slug a partir de un título
function generateSlug($text) {
    // Reemplazar caracteres no alfanuméricos con guiones
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // Transliterar
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // Eliminar caracteres no deseados
    $text = preg_replace('~[^-\w]+~', '', $text);
    // Trim
    $text = trim($text, '-');
    // Eliminar guiones duplicados
    $text = preg_replace('~-+~', '-', $text);
    // Convertir a minúsculas
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
}

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Función para verificar si el usuario es administrador
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

// Función para redireccionar
function redirect($url) {
    header("Location: $url");
    exit;
}

// Función para mostrar mensajes de alerta
function setAlert($type, $message) {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Función para mostrar alertas
function displayAlert() {
    if (isset($_SESSION['alert'])) {
        $type = $_SESSION['alert']['type'];
        $message = $_SESSION['alert']['message'];
        
        echo "<div class='alert alert-$type'>$message</div>";
        
        // Limpiar la alerta después de mostrarla
        unset($_SESSION['alert']);
    }
}

// Función para obtener una página por su slug
function getPageBySlug($slug) {
    global $db;
    return $db->getRow("SELECT * FROM paginas WHERE slug = :slug", ['slug' => $slug]);
}

function getEvents() {
    global $db;
        return $db->getRows("SELECT * FROM eventos WHERE fecha_fin >= NOW() ORDER BY fecha_fin ASC LIMIT 4");
}

// Función para obtener convocatorias activas
function getAnnouncementsByType($type = 'activa') {
    global $db;
    if ($type === 'activa') {
        return $db->getRows("SELECT * FROM convocatorias WHERE estado = 'activa' AND fecha_fin >= CURDATE() ORDER BY fecha_inicio ASC");
    } else {
        return $db->getRows("SELECT * FROM convocatorias WHERE estado = 'inactiva' OR fecha_fin < CURDATE() ORDER BY fecha_fin DESC");
    }
}

function getAnnouncements() {
    global $db;
        return $db->getRows("SELECT * FROM convocatorias ORDER BY fecha_inicio DESC");
}

// Función para obtener cursos abiertos
function getOpenCourses() {
    global $db;
    return $db->getRows("SELECT * FROM cursos WHERE estado = 'abierto' AND fecha_inicio >= CURDATE() ORDER BY fecha_inicio ASC");
}

function getCourses() {
    global $db;
    return $db->getRows("SELECT * FROM cursos ORDER BY fecha_inicio ASC");
}

// Función para formatear fecha
function formatDate($date) {
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

// Función para subir archivos
function uploadFile($file, $destination, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']) {
    // Verificar si hay errores
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Verificar el tipo de archivo
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    // Generar nombre único
    $filename = uniqid() . '_' . basename($file['name']);
    $uploadPath = $destination . $filename;
    
    // Mover el archivo
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $filename;
    }
    
    return false;
}

function fetchAll($query, $params = []) {
    $db = getDB();
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function insert($table, $data) {
    global $db;
    return $db->insert($table, $data);
}

function getRow($query, $params = []) {
    $db = getDB();
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getRows($query, $params = []) {
    global $db;
    return $db->getRows($query, $params);
}

function update($table, $data, $whereClause, $params = []) {
    global $db;
    return $db->update($table, $data, $whereClause, $params);
}

function getCurrentUser() {
    global $db;

    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $userId = $_SESSION['user_id'];
    return $db->getRow("SELECT * FROM usuarios WHERE id = :id", ['id' => $userId]);
}

/**
 * Ajusta el brillo de un color hexadecimal
 * 
 * @param string $hex Color en formato hexadecimal (#RRGGBB)
 * @param int $steps Pasos para ajustar el brillo (positivo = más claro, negativo = más oscuro)
 * @return string Color ajustado en formato hexadecimal
 */
function adjustBrightness($hex, $steps) {
    // Convertir hex a rgb
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    
    // Convertir a decimal
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Ajustar brillo
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    // Convertir de nuevo a hex
    return '#' . sprintf('%02x', $r) . sprintf('%02x', $g) . sprintf('%02x', $b);
}