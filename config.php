<?php
/**
 * Calendario Tributario Colombia 2026
 * Configuración de Base de Datos
 */

// Configuración de conexión MySQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'calendario_tributario');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Valor UVT 2026 (estimado basado en proyección)
define('UVT_2026', 49799);

// Umbrales en UVT
define('UVT_TOPE_IVA', 92000);       // 92.000 UVT para IVA bimestral
define('UVT_TOPE_ICA_BOG', 391);     // 391 UVT para ICA Bogotá bimestral

// Función de conexión PDO
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        die("Error de conexión a la base de datos: " . $e->getMessage());
    }
}

// Función para calcular valor en pesos desde UVT
function uvtToPesos($uvt) {
    return $uvt * UVT_2026;
}

// Validar dígito de verificación del NIT colombiano
function validarNIT($nit, $dv) {
    // Remover caracteres no numéricos del NIT
    $nit = preg_replace('/[^0-9]/', '', $nit);
    
    if (empty($nit) || strlen($nit) < 5 || strlen($nit) > 10) {
        return false;
    }
    
    // Pesos para el cálculo del DV
    $pesos = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
    
    // Invertir el NIT para aplicar pesos
    $nitInvertido = strrev($nit);
    $suma = 0;
    
    for ($i = 0; $i < strlen($nitInvertido); $i++) {
        $suma += intval($nitInvertido[$i]) * $pesos[$i];
    }
    
    $residuo = $suma % 11;
    
    if ($residuo > 1) {
        $dvCalculado = 11 - $residuo;
    } else {
        $dvCalculado = $residuo;
    }
    
    return intval($dv) === $dvCalculado;
}

// Obtener último dígito del NIT (sin DV)
function getUltimoDigitoNIT($nit) {
    $nit = preg_replace('/[^0-9]/', '', $nit);
    if (empty($nit)) {
        return null;
    }
    return substr($nit, -1);
}
?>
