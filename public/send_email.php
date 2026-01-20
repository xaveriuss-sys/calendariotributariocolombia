<?php
/**
 * Servicio de Envío de Email
 * Envía el calendario generado por correo electrónico
 */

require_once dirname(__DIR__) . '/src/config.php';
session_start();

// Respuesta JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del cuerpo o POST normal
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? $_POST['email'] ?? '';
$icsUrl = $input['icsUrl'] ?? $_POST['icsUrl'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

if (empty($icsUrl)) {
    echo json_encode(['success' => false, 'message' => 'URL de calendario no válida']);
    exit;
}

// Configuración del correo
$to = $email;
$subject = "📅 Tu Calendario Tributario 2026 - Dataeficiencia";

// Mensaje HTML
$message = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; }
        .header { background: #0ea5e9; color: white; padding: 15px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; }
        .btn { display: inline-block; background-color: #0ea5e9; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; margin: 10px 0; }
        .footer { font-size: 12px; color: #64748b; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Calendario Tributario 2026</h2>
        </div>
        <div class='content'>
            <p>Hola,</p>
            <p>Aquí tienes el enlace a tu calendario tributario personalizado para 2026.</p>
            
            <h3>Opciones de Instalación:</h3>
            
            <p><strong>1. Google Calendar (Suscripción):</strong><br>
            Haz clic abajo para suscribirte y recibir actualizaciones automáticas.</p>
            <center>
                <a href='https://calendar.google.com/calendar/render?cid=" . urlencode($icsUrl) . "' class='btn'>Añadir a Google Calendar</a>
            </center>

            <p><strong>2. Outlook / Descarga Directa:</strong><br>
            Si usas Outlook o prefieres el archivo físico:</p>
            <p><a href='" . $icsUrl . "'>Descargar archivo .ICS</a></p>

            <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
            
            <p><small>Enlace directo: " . $icsUrl . "</small></p>
        </div>
        <div class='footer'>
            <p>&copy; 2026 Dataeficiencia. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
";

// Headers
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: Calendario Tributario <noreply@calendariotributario.co>" . "\r\n";
$headers .= "Reply-To: noreply@calendariotributario.co" . "\r\n";

// Enviar
if (mail($to, $subject, $message, $headers)) {
    echo json_encode(['success' => true, 'message' => 'Correo enviado exitosamente']);
} else {
    // Si falla mail(), intentamos simular éxito para el usuario si es localhost (no hay SMTP)
    // En producción esto debería loguearse
    if ($_SERVER['SERVER_NAME'] === 'localhost') {
        echo json_encode(['success' => true, 'message' => 'Simulación: Correo enviado (Localhost)']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al enviar el correo. Verifique configuración SMTP.']);
    }
}
