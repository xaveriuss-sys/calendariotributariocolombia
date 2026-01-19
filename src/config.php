<?php
/**
 * Calendario Tributario Colombia 2026
 * Configuración de la Aplicación
 */

// Ruta base del proyecto
define('BASE_PATH', dirname(__DIR__));
define('STORAGE_PATH', BASE_PATH . '/storage');
define('CONFIG_FILE', STORAGE_PATH . '/config.json');

// Valor UVT 2026 (proyectado)
define('UVT_2026', 49799);

// Umbrales en UVT
define('UVT_TOPE_IVA', 92000);       // 92.000 UVT para IVA bimestral
define('UVT_TOPE_ICA_BOG', 391);     // 391 UVT para ICA Bogotá bimestral

/**
 * Verificar si la aplicación está instalada
 */
function isInstalled()
{
    return file_exists(CONFIG_FILE);
}

/**
 * Obtener configuración guardada
 */
function getConfig()
{
    if (!isInstalled()) {
        return null;
    }

    $content = file_get_contents(CONFIG_FILE);
    return json_decode($content, true);
}

/**
 * Guardar configuración
 */
function saveConfig($config)
{
    // Crear directorio storage si no existe
    if (!is_dir(STORAGE_PATH)) {
        mkdir(STORAGE_PATH, 0755, true);
    }

    return file_put_contents(CONFIG_FILE, json_encode($config, JSON_PRETTY_PRINT));
}

/**
 * Obtener conexión PDO usando la configuración guardada
 */
function getConnection()
{
    $config = getConfig();
    if (!$config) {
        return null;
    }

    require_once BASE_PATH . '/src/database.php';
    return getDBConnection(
        $config['db_host'],
        $config['db_name'],
        $config['db_user'],
        $config['db_pass']
    );
}

/**
 * Función para calcular valor en pesos desde UVT
 */
function uvtToPesos($uvt)
{
    return $uvt * UVT_2026;
}

/**
 * Validar dígito de verificación del NIT colombiano
 */
function validarNIT($nit, $dv)
{
    $nit = preg_replace('/[^0-9]/', '', $nit);

    if (empty($nit) || strlen($nit) < 5 || strlen($nit) > 10) {
        return false;
    }

    $pesos = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
    $nitInvertido = strrev($nit);
    $suma = 0;

    for ($i = 0; $i < strlen($nitInvertido); $i++) {
        $suma += intval($nitInvertido[$i]) * $pesos[$i];
    }

    $residuo = $suma % 11;
    $dvCalculado = ($residuo > 1) ? 11 - $residuo : $residuo;

    return intval($dv) === $dvCalculado;
}

/**
 * Obtener último dígito del NIT (sin DV)
 */
function getUltimoDigitoNIT($nit)
{
    $nit = preg_replace('/[^0-9]/', '', $nit);
    if (empty($nit)) {
        return null;
    }
    return substr($nit, -1);
}
