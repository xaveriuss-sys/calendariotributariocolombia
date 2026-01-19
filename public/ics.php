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

// Validar que solo contenga caracteres seguros
if (!preg_match('/^calendario_[0-9]+_[0-9]+\.ics$/', $file)) {
    http_response_code(400);
    die('Archivo no válido');
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

// Servir el archivo
$disposition = isset($_GET['dl']) ? 'attachment' : 'inline';
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: ' . $disposition . '; filename="' . $file . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: public, max-age=3600');

readfile($filepath);
