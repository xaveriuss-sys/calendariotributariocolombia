<?php
/**
 * Script de Diagnóstico de Base de Datos
 */
require_once dirname(__DIR__) . '/src/config.php';

echo "<h1>Diagnóstico de Base de Datos</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

$configPath = dirname(__DIR__) . '/storage/config.json';
if (file_exists($configPath)) {
    echo "<p>✅ config.json encontrado en: $configPath</p>";
    $config = json_decode(file_get_contents($configPath), true);
    echo "<pre>" . print_r($config, true) . "</pre>";
} else {
    echo "<p>❌ CONFLICTO CRÍTICO: config.json NO encontrado en $configPath</p>";
    exit;
}

echo "<h2>Intentando conectar...</h2>";
try {
    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "<p style='color:green; font-weight:bold;'>✅ ¡CONEXIÓN EXITOSA!</p>";
} catch (PDOException $e) {
    echo "<div style='background:#fee; color:#c00; padding:10px; border:1px solid #c00;'>";
    echo "<h3>❌ Error de Conexión:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
