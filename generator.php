<?php
/**
 * Calendario Tributario Colombia 2026
 * Generador de Archivo ICS
 * 
 * Este script recibe los datos del formulario, consulta las fechas
 * aplicables en la base de datos y genera un archivo .ics para descarga.
 */

require_once 'config.php';

// ============================================
// FUNCIONES AUXILIARES
// ============================================

/**
 * Genera un UID único para eventos ICS
 */
function generateUID()
{
    return uniqid('CTR-') . '@calendario-tributario.co';
}

/**
 * Formatea una fecha para ICS (YYYYMMDD)
 */
function formatDateICS($date)
{
    return date('Ymd', strtotime($date));
}

/**
 * Calcula la fecha del día siguiente (para DTEND)
 */
function getNextDay($date)
{
    return date('Ymd', strtotime($date . ' +1 day'));
}

/**
 * Limpia texto para ICS (escapa caracteres especiales)
 */
function escapeICS($text)
{
    $text = str_replace(['\\', ';', ','], ['\\\\', '\\;', '\\,'], $text);
    $text = str_replace(["\r\n", "\r", "\n"], '\\n', $text);
    return $text;
}

/**
 * Genera un evento VEVENT para ICS
 */
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
    // Alarma 2 días antes
    $event .= "BEGIN:VALARM\r\n";
    $event .= "TRIGGER:-P2D\r\n";
    $event .= "ACTION:DISPLAY\r\n";
    $event .= "DESCRIPTION:Recordatorio: {$summary}\r\n";
    $event .= "END:VALARM\r\n";
    // Alarma 1 día antes
    $event .= "BEGIN:VALARM\r\n";
    $event .= "TRIGGER:-P1D\r\n";
    $event .= "ACTION:DISPLAY\r\n";
    $event .= "DESCRIPTION:¡Mañana vence! {$summary}\r\n";
    $event .= "END:VALARM\r\n";
    $event .= "END:VEVENT\r\n";

    return $event;
}

/**
 * Determina el grupo de dígitos (1-5 o 6-0)
 */
function getDigitGroup($digit)
{
    $digit = intval($digit);
    if ($digit >= 1 && $digit <= 5) {
        return '1-5';
    }
    return '6-0'; // Incluye 6, 7, 8, 9, 0
}

// ============================================
// VALIDACIÓN DE DATOS DE ENTRADA
// ============================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// Obtener y sanitizar datos
$nit = preg_replace('/[^0-9]/', '', $_POST['nit'] ?? '');
$nit_dv = preg_replace('/[^0-9]/', '', $_POST['nit_dv'] ?? '');
$ciudad = trim($_POST['ciudad'] ?? '');
$ingresos = floatval(preg_replace('/[^0-9]/', '', $_POST['ingresos'] ?? 0));
$ica_cargo = floatval(preg_replace('/[^0-9]/', '', $_POST['ica_cargo'] ?? 0));

// Validar NIT
if (!validarNIT($nit, $nit_dv)) {
    die('Error: NIT inválido. <a href="index.html">Volver</a>');
}

// Validar ciudad
$ciudadesValidas = ['Bogotá', 'Medellín', 'Cali', 'Otra'];
if (!in_array($ciudad, $ciudadesValidas)) {
    die('Error: Ciudad no válida. <a href="index.html">Volver</a>');
}

// Obtener último dígito del NIT
$ultimoDigito = getUltimoDigitoNIT($nit);
$grupoDigitos = getDigitGroup($ultimoDigito);

// Calcular umbrales en pesos
$umbalIVA = uvtToPesos(UVT_TOPE_IVA);       // ~$4,581,508,000
$umbralICABog = uvtToPesos(UVT_TOPE_ICA_BOG); // ~$19,461,409

// ============================================
// DETERMINAR PERIODICIDADES
// ============================================

// IVA: Bimestral si ingresos > 92.000 UVT, sino Cuatrimestral
$ivaPeriodicidad = ($ingresos > $umbalIVA) ? 'bimestral' : 'cuatrimestral';
$ivaCodigo = ($ivaPeriodicidad === 'bimestral') ? 'IVA_BIM' : 'IVA_CUAT';

// ICA Bogotá: Bimestral si cargo > 391 UVT, sino Anual
$icaBogotaCodigo = null;
if ($ciudad === 'Bogotá') {
    $icaBogotaCodigo = ($ica_cargo > $umbralICABog) ? 'ICA_BOG_BIM' : 'ICA_BOG_ANUAL';
}

// ============================================
// CONSULTAR BASE DE DATOS
// ============================================

try {
    $pdo = getDBConnection();
    $eventos = [];

    // 1. RENTA PERSONAS JURÍDICAS (Por último dígito exacto)
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
    }

    // 2. IVA (Por grupo de dígitos)
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
    }

    // 3. RETENCIÓN EN LA FUENTE (Por grupo de dígitos)
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
    }

    // 4. ICA MUNICIPAL
    if ($ciudad === 'Bogotá' && $icaBogotaCodigo) {
        // ICA Bogotá (Bimestral o Anual según cargo)
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
        }

    } elseif ($ciudad === 'Medellín') {
        // ICA Medellín
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
        }

    } elseif ($ciudad === 'Cali') {
        // ICA Cali
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
        }
    }

    // 5. OBLIGACIONES LABORALES (Aplican a todos)
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
    }

} catch (PDOException $e) {
    die('Error de base de datos: ' . $e->getMessage() . ' <a href="index.html">Volver</a>');
}

// ============================================
// GENERAR ARCHIVO ICS
// ============================================

// Nombre del archivo
$nombreArchivo = "calendario_tributario_{$nit}_2026.ics";

// Cabecera del calendario
$ics = "BEGIN:VCALENDAR\r\n";
$ics .= "VERSION:2.0\r\n";
$ics .= "PRODID:-//Dataeficiencia//CalendarioTributario2026//ES\r\n";
$ics .= "CALSCALE:GREGORIAN\r\n";
$ics .= "METHOD:PUBLISH\r\n";
$ics .= "X-WR-CALNAME:Calendario Tributario 2026 - {$nit}\r\n";
$ics .= "X-WR-TIMEZONE:America/Bogota\r\n";

// Agregar todos los eventos
foreach ($eventos as $evento) {
    $ics .= $evento;
}

// Cerrar calendario
$ics .= "END:VCALENDAR\r\n";

// ============================================
// ENVIAR ARCHIVO PARA DESCARGA
// ============================================

// Headers para forzar descarga
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Content-Length: ' . strlen($ics));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Enviar contenido
echo $ics;
exit;
