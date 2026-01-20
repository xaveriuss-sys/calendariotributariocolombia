<?php
/**
 * Script de Migración SaaS
 * Ejecuta database_saas.sql para crear las nuevas tablas
 */

require_once dirname(__DIR__) . '/src/config.php';

echo "<h2>Iniciando Migración SaaS...</h2>";

try {
    $pdo = getConnection();

    if (!$pdo) {
        die("❌ Error de conexión a la base de datos.");
    }

    $sqlFile = __DIR__ . '/database_saas.sql';
    if (!file_exists($sqlFile)) {
        die("❌ No se encuentra el archivo SQL: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);

    // Ejecutar múltiples queries
    $pdo->exec($sql);

    echo "✅ Tablas creadas exitosamente:<br>";
    echo "<ul>
        <li>users</li>
        <li>companies</li>
        <li>calendar_events</li>
    </ul>";
    echo "<p>La base de datos ha sido actualizada a la versión SaaS.</p>";
    echo "<a href='index.php'>Volver al inicio</a>";

} catch (PDOException $e) {
    echo "❌ Error en migración: " . $e->getMessage();
}
