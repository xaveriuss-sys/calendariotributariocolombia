<?php
/**
 * Script de Reinicio de Configuración
 * Elimina el archivo config.json para forzar una reinstalación.
 */

// Definir rutas (usando la misma estructura que config.php)
define('BASE_PATH', __DIR__); // Asumiendo que reset.php está en public/ o raíz, adjustar si es necesario.
// Nota: En la estructura actual public/index.php incluye src/config.php que define BASE_PATH como dirname(__DIR__).
// Si reset.php se pone en public/, BASE_PATH debería ser dirname(__DIR__).

$configFile = dirname(__DIR__) . '/storage/config.json';

if (file_exists($configFile)) {
    if (unlink($configFile)) {
        $message = "✅ Archivo de configuración eliminado correctamente.";
        $status = "success";
    } else {
        $message = "❌ Error: No se pudo eliminar el archivo. Verifique permisos.";
        $status = "error";
    }
} else {
    $message = "ℹ️ El archivo de configuración no existe. La aplicación ya debería pedir instalación.";
    $status = "info";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reinicio de Configuración</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f1f5f9;
            margin: 0;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
        }

        .btn {
            display: inline-block;
            background-color: #0ea5e9;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 1rem;
            font-weight: 600;
        }

        .btn:hover {
            background-color: #0284c7;
        }

        .success {
            color: #16a34a;
        }

        .error {
            color: #dc2626;
        }

        .info {
            color: #0284c7;
        }
    </style>
</head>

<body>
    <div class="card">
        <h2 class="<?php echo $status; ?>">
            <?php echo ($status == 'success' ? '¡Éxito!' : 'Información'); ?>
        </h2>
        <p>
            <?php echo $message; ?>
        </p>
        <p style="font-size: 0.9rem; color: #64748b;">Ahora puedes recargar la página principal para conectar la nueva
            base de datos.</p>
        <a href="index.php" class="btn">Ir al Inicio (Instalar)</a>
    </div>
</body>

</html>