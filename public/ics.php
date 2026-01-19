<?php
/**
 * Calendario Tributario Colombia 2026
 * Servidor de archivos ICS
 * 
 * Este script sirve los archivos ICS desde storage/ics/
 * para que sean accesibles via URL pública.
 */

// Obtener el nombre del archivo
$file = $_GET['file'] ?? '';

// Fallback para URLs malformadas (ej: file%3D...)
if (empty($file) && !empty($_SERVER['QUERY_STRING'])) {
    $qs = urldecode($_SERVER['QUERY_STRING']);
    if (preg_match('/file=([^&]+)/', $qs, $matches)) {
        $file = $matches[1];
    }
}

$file = trim($file);

// Validar que solo contenga caracteres seguros
if (!preg_match('/^calendario_[0-9]+_[0-9]+\.ics$/', $file)) {
    // Debug info si estamos en desarrollo (o mostrar error genérico en prod)
    http_response_code(400);
    die('Archivo no válido (Format error)');
}

$filepath = dirname(__DIR__) . '/storage/ics/' . $file;

// Verificar que el archivo existe
if (!file_exists($filepath)) {
    http_response_code(404);
    die('Archivo no encontrado');
}

// Verificar que no sea muy antiguo (máximo 2 horas)
if (filemtime($filepath) < time() - 7200) {
    unlink($filepath);
    http_response_code(410);
    die('El archivo ha expirado');
}

// Servir el archivo para descarga directa (One-Time Import)
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Description: File Transfer');
header('Connection: Keep-Alive');
header('Cache-Control: public, max-age=3600');
header('Access-Control-Allow-Origin: *');

readfile($filepath);
