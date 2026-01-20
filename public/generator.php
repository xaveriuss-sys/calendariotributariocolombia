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
// VALIDACIÓN DE INPUT
// ============================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$nit = preg_replace('/[^0-9]/', '', $_POST['nit'] ?? '');
$nit_dv = preg_replace('/[^0-9]/', '', $_POST['nit_dv'] ?? '');
$ciudad = trim($_POST['ciudad'] ?? '');
$ingresos = floatval(preg_replace('/[^0-9]/', '', $_POST['ingresos'] ?? 0));
$ica_cargo = floatval(preg_replace('/[^0-9]/', '', $_POST['ica_cargo'] ?? 0));
$action = $_POST['action'] ?? 'download'; // download, google, outlook

if (!validarNIT($nit, $nit_dv)) {
    die('Error: NIT inválido. <a href="index.php">Volver</a>');
}

$ciudadesValidas = ['Bogotá', 'Medellín', 'Cali', 'Otra'];
if (!in_array($ciudad, $ciudadesValidas)) {
    die('Error: Ciudad no válida. <a href="index.php">Volver</a>');
}

$ultimoDigito = getUltimoDigitoNIT($nit);
$grupoDigitos = getDigitGroup($ultimoDigito);

$umbalIVA = uvtToPesos(UVT_TOPE_IVA);
$umbralICABog = uvtToPesos(UVT_TOPE_ICA_BOG);

$ivaPeriodicidad = ($ingresos > $umbalIVA) ? 'bimestral' : 'cuatrimestral';
$ivaCodigo = ($ivaPeriodicidad === 'bimestral') ? 'IVA_BIM' : 'IVA_CUAT';

$icaBogotaCodigo = null;
if ($ciudad === 'Bogotá') {
    $icaBogotaCodigo = ($ica_cargo > $umbralICABog) ? 'ICA_BOG_BIM' : 'ICA_BOG_ANUAL';
}

// ============================================
// CONSULTAR BASE DE DATOS
// ============================================

try {
    $pdo = getConnection();

    if (!$pdo) {
        die('Error: No se pudo conectar a la base de datos. <a href="install.php">Reinstalar</a>');
    }

    $eventos = [];
    $eventosData = []; // Para Google/Outlook URLs

    // 1. RENTA PERSONAS JURÍDICAS
    $sql = "SELECT d.fecha_vencimiento, d.descripcion, d.periodo, r.impuesto_nombre
            FROM tax_deadlines_2026 d
            JOIN tax_rules r ON d.rule_id = r.id
            WHERE r.impuesto_codigo = 'RENTA_PJ'
            AND d.ultimo_digito_nit = :digito
            AND r.activo = 1
            ORDER BY d.fecha_vencimiento";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['digito' => $ultimoDigito]);
    $fechasRenta = $stmt->fetchAll();

    foreach ($fechasRenta as $row) {
        $summary = "📋 " . $row['impuesto_nombre'] . " - " . $row['periodo'];
        $desc = $row['descripcion'] . "\\nNIT: {$nit}-{$nit_dv}\\nÚltimo dígito: {$ultimoDigito}\\n\\nRecuerde verificar con su contador.";
        $eventos[] = createEvent($summary, $row['fecha_vencimiento'], $desc, 'DIAN - Renta');
        $eventosData[] = [
            'summary' => $row['impuesto_nombre'] . " - " . $row['periodo'],
            'date' => $row['fecha_vencimiento'],
            'description' => $row['descripcion'] . " - NIT: {$nit}-{$nit_dv}"
        ];
    }

    // 2. IVA
    $sql = "SELECT d.fecha_vencimiento, d.descripcion, d.periodo, r.impuesto_nombre
            FROM tax_deadlines_2026 d
            JOIN tax_rules r ON d.rule_id = r.id
            WHERE r.impuesto_codigo = :codigo
            AND d.ultimo_digito_nit = :grupo
            AND r.activo = 1
            ORDER BY d.fecha_vencimiento";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['codigo' => $ivaCodigo, 'grupo' => $grupoDigitos]);
    $fechasIVA = $stmt->fetchAll();

    foreach ($fechasIVA as $row) {
        $periodicidadLabel = ($ivaPeriodicidad === 'bimestral') ? 'Bimestral' : 'Cuatrimestral';
        $summary = "💰 IVA {$periodicidadLabel} - " . $row['periodo'];
        $desc = $row['descripcion'] . "\\nPeriodicidad: {$periodicidadLabel}\\nNIT: {$nit}-{$nit_dv}\\n\\nRecuerde verificar con su contador.";
        $eventos[] = createEvent($summary, $row['fecha_vencimiento'], $desc, 'DIAN - IVA');
        $eventosData[] = [
            'summary' => "IVA {$periodicidadLabel} - " . $row['periodo'],
            'date' => $row['fecha_vencimiento'],
            'description' => $row['descripcion'] . " - NIT: {$nit}-{$nit_dv}"
        ];
    }

    // 3. RETENCIÓN EN LA FUENTE
    $sql = "SELECT d.fecha_vencimiento, d.descripcion, d.periodo, r.impuesto_nombre
            FROM tax_deadlines_2026 d
            JOIN tax_rules r ON d.rule_id = r.id
            WHERE r.impuesto_codigo = 'RETEFUENTE'
            AND d.ultimo_digito_nit = :grupo
            AND r.activo = 1
            ORDER BY d.fecha_vencimiento";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['grupo' => $grupoDigitos]);
    $fechasRetencion = $stmt->fetchAll();

    foreach ($fechasRetencion as $row) {
        $summary = "🏦 Retención Fuente - " . $row['periodo'];
        $desc = $row['descripcion'] . "\\nNIT: {$nit}-{$nit_dv}\\n\\nRecuerde verificar con su contador.";
        $eventos[] = createEvent($summary, $row['fecha_vencimiento'], $desc, 'DIAN - Retención');
        $eventosData[] = [
            'summary' => "Retención Fuente - " . $row['periodo'],
            'date' => $row['fecha_vencimiento'],
            'description' => $row['descripcion'] . " - NIT: {$nit}-{$nit_dv}"
        ];
    }

    // 4. ICA MUNICIPAL
    if ($ciudad === 'Bogotá' && $icaBogotaCodigo) {
        $sql = "SELECT d.fecha_vencimiento, d.descripcion, d.periodo, r.impuesto_nombre
                FROM tax_deadlines_2026 d
                JOIN tax_rules r ON d.rule_id = r.id
                WHERE r.impuesto_codigo = :codigo
                AND d.ultimo_digito_nit = '*'
                AND r.activo = 1
                ORDER BY d.fecha_vencimiento";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['codigo' => $icaBogotaCodigo]);
        $fechasICABog = $stmt->fetchAll();

        $icaLabel = ($icaBogotaCodigo === 'ICA_BOG_BIM') ? 'Bimestral' : 'Anual';
        foreach ($fechasICABog as $row) {
            $summary = "🏛️ ICA Bogotá {$icaLabel} - " . $row['periodo'];
            $desc = $row['descripcion'] . "\\nRégimen: {$icaLabel}\\nNIT: {$nit}-{$nit_dv}\\n\\nRecuerde verificar con su contador.";
            $eventos[] = createEvent($summary, $row['fecha_vencimiento'], $desc, 'ICA - Bogotá');
            $eventosData[] = [
                'summary' => "ICA Bogotá {$icaLabel} - " . $row['periodo'],
                'date' => $row['fecha_vencimiento'],
                'description' => $row['descripcion'] . " - NIT: {$nit}-{$nit_dv}"
            ];
        }

    } elseif ($ciudad === 'Medellín') {
        $sql = "SELECT d.fecha_vencimiento, d.descripcion, d.periodo, r.impuesto_nombre
                FROM tax_deadlines_2026 d
                JOIN tax_rules r ON d.rule_id = r.id
                WHERE r.impuesto_codigo = 'ICA_MED'
                AND r.activo = 1
                ORDER BY d.fecha_vencimiento";

        $stmt = $pdo->query($sql);
        $fechasICAMed = $stmt->fetchAll();

        foreach ($fechasICAMed as $row) {
            $summary = "🏛️ ICA Medellín - " . $row['periodo'];
            $desc = $row['descripcion'] . "\\nNIT: {$nit}-{$nit_dv}\\n\\nRecuerde verificar con su contador.";
            $eventos[] = createEvent($summary, $row['fecha_vencimiento'], $desc, 'ICA - Medellín');
            $eventosData[] = [
                'summary' => "ICA Medellín - " . $row['periodo'],
                'date' => $row['fecha_vencimiento'],
                'description' => $row['descripcion'] . " - NIT: {$nit}-{$nit_dv}"
            ];
        }

    } elseif ($ciudad === 'Cali') {
        $sql = "SELECT d.fecha_vencimiento, d.descripcion, d.periodo, r.impuesto_nombre
                FROM tax_deadlines_2026 d
                JOIN tax_rules r ON d.rule_id = r.id
                WHERE r.impuesto_codigo = 'ICA_CALI'
                AND r.activo = 1
                ORDER BY d.fecha_vencimiento";

        $stmt = $pdo->query($sql);
        $fechasICACali = $stmt->fetchAll();

        foreach ($fechasICACali as $row) {
            $summary = "🏛️ ICA Cali - " . $row['periodo'];
            $desc = $row['descripcion'] . "\\nNIT: {$nit}-{$nit_dv}\\n\\nRecuerde verificar con su contador.";
            $eventos[] = createEvent($summary, $row['fecha_vencimiento'], $desc, 'ICA - Cali');
            $eventosData[] = [
                'summary' => "ICA Cali - " . $row['periodo'],
                'date' => $row['fecha_vencimiento'],
                'description' => $row['descripcion'] . " - NIT: {$nit}-{$nit_dv}"
            ];
        }
    }

    // 5. OBLIGACIONES LABORALES
    $sql = "SELECT d.fecha_vencimiento, d.descripcion, d.periodo, r.impuesto_nombre
            FROM tax_deadlines_2026 d
            JOIN tax_rules r ON d.rule_id = r.id
            WHERE r.impuesto_codigo LIKE 'LAB_%'
            AND r.activo = 1
            ORDER BY d.fecha_vencimiento";

    $stmt = $pdo->query($sql);
    $fechasLaborales = $stmt->fetchAll();

    foreach ($fechasLaborales as $row) {
        $summary = "👥 " . $row['impuesto_nombre'];
        $desc = $row['descripcion'] . "\\nEmpresa: {$nit}-{$nit_dv}\\n\\nObligación laboral importante.";
        $eventos[] = createEvent($summary, $row['fecha_vencimiento'], $desc, 'Laboral');
        $eventosData[] = [
            'summary' => $row['impuesto_nombre'],
            'date' => $row['fecha_vencimiento'],
            'description' => $row['descripcion'] . " - NIT: {$nit}-{$nit_dv}"
        ];
    }

} catch (PDOException $e) {
    die('Error de base de datos: ' . $e->getMessage() . ' <a href="index.php">Volver</a>');
}

// ============================================
// GENERAR ARCHIVO ICS
// ============================================

$nombreArchivo = "calendario_tributario_{$nit}_2026.ics";

$ics = "BEGIN:VCALENDAR\r\n";
$ics .= "VERSION:2.0\r\n";
$ics .= "PRODID:-//Dataeficiencia//CalendarioTributario2026//ES\r\n";
$ics .= "CALSCALE:GREGORIAN\r\n";
$ics .= "METHOD:PUBLISH\r\n";
$ics .= "X-WR-CALNAME:Calendario Tributario 2026 - {$nit}\r\n";
$ics .= "X-WR-TIMEZONE:America/Bogota\r\n";

foreach ($eventos as $evento) {
    $ics .= $evento;
}

$ics .= "END:VCALENDAR\r\n";

// Guardar archivo físico
$icsFilename = saveICSFile($ics, $nit);

// PERMISOS EXPLÍCITOS PARA EVITAR BLOQUEOS
// 0644: Lectura pública, Escritura solo dueño
chmod(__DIR__ . '/calendarios/' . $icsFilename, 0644);

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

// Agregamos timestamp para evitar cache de Google (Cache Busting)
$icsUrl = $baseUrl . '/calendarios/' . $icsFilename . '?v=' . time();

// Guardar en sesión
session_start();
$_SESSION['calendar_result'] = [
    'nit' => $nit,
    'eventos_count' => count($eventos),
    'ics_url' => $icsUrl, // URL con versión para Google
    'ics_filename' => $icsFilename,
    'ciudad' => $ciudad
];

header('Location: result.php');
exit;
