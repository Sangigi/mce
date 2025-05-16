<?php
/**
 * Funciones específicas para el panel de administración
 */

/**
 * Genera un slug a partir de un texto
 * 
 * @param string $text Texto a convertir en slug
 * @return string Slug generado
 */
function generateSlug($text) {
    // Convertir a minúsculas
    $text = mb_strtolower($text, 'UTF-8');
    
    // Reemplazar caracteres especiales
    $text = str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ', ' '],
        ['a', 'e', 'i', 'o', 'u', 'u', 'n', '-'],
        $text
    );
    
    // Eliminar caracteres que no sean alfanuméricos o guiones
    $text = preg_replace('/[^a-z0-9\-]/', '', $text);
    
    // Eliminar guiones duplicados
    $text = preg_replace('/-+/', '-', $text);
    
    // Eliminar guiones al principio y al final
    $text = trim($text, '-');
    
    return $text;
}

/**
 * Verifica si un usuario tiene permisos de administrador
 * 
 * @param string $role Rol del usuario
 * @return bool True si es administrador, false en caso contrario
 */
function isAdmin($role) {
    return $role === 'admin';
}

/**
 * Formatea una fecha para mostrarla en el panel de administración
 * 
 * @param string $date Fecha en formato Y-m-d
 * @param bool $withTime Incluir hora
 * @return string Fecha formateada
 */
function formatDate($date, $withTime = false) {
    if (empty($date)) {
        return '';
    }
    
    $format = $withTime ? 'd/m/Y H:i' : 'd/m/Y';
    return date($format, strtotime($date));
}

/**
 * Trunca un texto a una longitud determinada
 * 
 * @param string $text Texto a truncar
 * @param int $length Longitud máxima
 * @param string $append Texto a añadir al final
 * @return string Texto truncado
 */
function truncateText($text, $length = 100, $append = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    
    return $text . $append;
}

/**
 * Registra una acción en el log de actividad
 * 
 * @param int $userId ID del usuario
 * @param string $action Acción realizada
 * @param string $details Detalles adicionales
 * @return bool True si se registró correctamente, false en caso contrario
 */
function logActivity($userId, $action, $details = '') {
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
        
        $stmt = $db->prepare("
            INSERT INTO actividad_log (usuario_id, accion, detalles, ip_address)
            VALUES (:usuario_id, :accion, :detalles, :ip_address)
        ");
        
        $stmt->execute([
            'usuario_id' => $userId,
            'accion' => $action,
            'detalles' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);
        
        return true;
    } catch (PDOException $e) {
        // Registrar error en archivo de log
        error_log('Error al registrar actividad: ' . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene el tamaño de un archivo en formato legible
 * 
 * @param int $bytes Tamaño en bytes
 * @param int $precision Precisión decimal
 * @return string Tamaño formateado
 */
function formatFileSize($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Verifica si una extensión de archivo está permitida
 * 
 * @param string $extension Extensión del archivo
 * @param array $allowedExtensions Extensiones permitidas
 * @return bool True si está permitida, false en caso contrario
 */
function isAllowedExtension($extension, $allowedExtensions) {
    return in_array(strtolower($extension), $allowedExtensions);
}

/**
 * Genera un nombre único para un archivo
 * 
 * @param string $originalName Nombre original del archivo
 * @return string Nombre único
 */
function generateUniqueFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}