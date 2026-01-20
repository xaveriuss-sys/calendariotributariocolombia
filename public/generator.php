<?php
/**
 * Calendario Tributario Colombia 2026
 * Generador de Archivo ICS y URLs de Calendario
 */

require_once dirname(__DIR__) . '/src/config.php';

// Verificar instalación
if (!isInstalled()) {
    header('Location: install.php');
    exit;
}

// ============================================
// FUNCIONES AUXILIARES
// ============================================

function generateUID()
{
    return uniqid('CTR-') . '@calendario-tributario.co';
}

function formatDateICS($date)
{
    return date('Ymd', strtotime($date));
}

function getNextDay($date)
{
    return date('Ymd', strtotime($date . ' +1 day'));
}

function escapeICS($text)
{
    $text = str_replace(['\\', ';', ','], ['\\\\', '\\;', '\\,'], $text);
    $text = str_replace(["\r\n", "\r", "\n"], '\\n', $text);
    return $text;
}

function createEvent($summary, $date, $description, $category = 'Tributario')
{
    $uid = generateUID();
    $dtstart = formatDateICS($date);
    $dtend = getNextDay($date);
    $dtstamp = gmdate('Ymd\THis\Z');

    $summary = escapeICS($summary);
    $description = escapeICS($description);

    $event = "BEGIN:VEVENT\r\n";
    $event .= "UID:{$uid}\r\n";
    $event .= "DTSTAMP:{$dtstamp}\r\n";
    $event .= "DTSTART;VALUE=DATE:{$dtstart}\r\n";
    $event .= "DTEND;VALUE=DATE:{$dtend}\r\n";
    $event .= "SUMMARY:{$summary}\r\n";
    $event .= "DESCRIPTION:{$description}\r\n";
    $event .= "CATEGORIES:{$category}\r\n";
    $event .= "STATUS:CONFIRMED\r\n";
    $event .= "BEGIN:VALARM\r\n";
    $event .= "TRIGGER:-P2D\r\n";
    $event .= "ACTION:DISPLAY\r\n";
    $event .= "DESCRIPTION:Recordatorio: {$summary}\r\n";
    $event .= "END:VALARM\r\n";
    $event .= "BEGIN:VALARM\r\n";
    $event .= "TRIGGER:-P1D\r\n";
    $event .= "ACTION:DISPLAY\r\n";
    $event .= "DESCRIPTION:¡Mañana vence! {$summary}\r\n";
    $event .= "END:VALARM\r\n";
    $event .= "END:VEVENT\r\n";

    return $event;
}

function getDigitGroup($digit)
{
    $digit = intval($digit);
    if ($digit >= 1 && $digit <= 5) {
        return '1-5';
    }
    return '6-0';
}

/**
 * Guardar ICS en carpeta pública (Estático)
 */
function saveICSFile($ics, $nit)
{
    // Directorio público
    $publicDir = __DIR__ . '/calendarios';
    if (!is_dir($publicDir)) {
        mkdir($publicDir, 0755, true);
    }

    // Nombre persistente: empresa_{NIT}_{AÑO}.ics
    // Esto permite actualizar el archivo sin cambiar la URL
    $filename = "empresa_{$nit}_2026.ics";
    $filepath = $publicDir . '/' . $filename;

    file_put_contents($filepath, $ics);

    return $filename;
}

// ============================================
// LOGICA PRINCIPAL
// ============================================

// ... (Validaciones anteriores) ...

// NOTA: Se ha eliminado cleanOldICSFiles() porque ahora son archivos persistentes

// ... (Generación de eventos) ...

// ... (Generación de contenido ICS) ...

// Guardar archivo físico
$icsFilename = saveICSFile($ics, $nit);

// Generar URL pública absoluta
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $protocol = 'https';
}
$host = $_SERVER['HTTP_HOST'];
// Asumiendo que generator.php está en / y calendarios en /calendarios
// Ajuste de basePath si es necesario, pero generalmente es root
$basePath = dirname($_SERVER['REQUEST_URI']);
// Limpieza de basePath para evitar dobles slashes o rutas relativas extrañas de PHP self
$baseUrl = $protocol . '://' . $host . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$icsUrl = $baseUrl . '/calendarios/' . $icsFilename;

// Guardar en sesión
session_start();
$_SESSION['calendar_result'] = [
    'nit' => $nit,
    'eventos_count' => count($eventos),
    'ics_url' => $icsUrl, // URL ESTÁTICA PÚBLICA DIRECTA
    'ics_filename' => $icsFilename,
    'ciudad' => $ciudad
];

header('Location: result.php');
exit;
