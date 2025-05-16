<?php
// Prevenir acceso directo
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

// Establecer el tipo de contenido como CSS
header('Content-Type: text/css');

// Obtener configuraciones de la base de datos
$db = new Database();
$configuraciones = [];
$stmt = $db->query("SELECT * FROM configuracion");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $configuraciones[$row['clave']] = $row['valor'];
}

// Generar variables CSS dinámicas
echo ":root {\n";

// Colores principales
echo "  --primary: " . ($configuraciones['tema_color_primario'] ?? '#004b8d') . ";\n";
echo "  --primary-dark: " . adjustBrightness($configuraciones['tema_color_primario'] ?? '#004b8d', -20) . ";\n";
echo "  --primary-light: " . adjustBrightness($configuraciones['tema_color_primario'] ?? '#004b8d', 40) . ";\n";
echo "  --primary-lighter: " . adjustBrightness($configuraciones['tema_color_primario'] ?? '#004b8d', 80) . ";\n";

echo "  --secondary: " . ($configuraciones['tema_color_secundario'] ?? '#48bb78') . ";\n";
echo "  --secondary-dark: " . adjustBrightness($configuraciones['tema_color_secundario'] ?? '#48bb78', -20) . ";\n";
echo "  --secondary-light: " . adjustBrightness($configuraciones['tema_color_secundario'] ?? '#48bb78', 40) . ";\n";
echo "  --secondary-lighter: " . adjustBrightness($configuraciones['tema_color_secundario'] ?? '#48bb78', 80) . ";\n";

// Fuente principal
echo "  --font-family: " . ($configuraciones['tema_fuente_principal'] ?? "'Inter', sans-serif") . ";\n";

echo "}\n";

// Función para ajustar el brillo de un color
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
?>