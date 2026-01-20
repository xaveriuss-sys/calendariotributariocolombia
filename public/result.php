<?php
/**
 * Calendario Tributario Colombia 2026
 * Página de Resultado - Opciones de Calendario
 */

require_once dirname(__DIR__) . '/src/config.php';

session_start();

// Verificar que hay datos de resultado
if (!isset($_SESSION['calendar_result'])) {
    header('Location: index.php');
    exit;
}

$result = $_SESSION['calendar_result'];
$nit = $result['nit'];
$eventosCount = $result['eventos_count'];
$icsUrl = $result['ics_url'];
$icsFilename = $result['ics_filename'];
$ciudad = $result['ciudad'];

// HOTFIX: Corregir URLs antiguas en sesión (si el usuario no regeneró el calendario)
if (strpos($icsUrl, '/calendarios/') !== false && strpos($icsUrl, 'ics.php') === false && strpos($icsUrl, '.ics') === false) {
    // Si es un directorio y no un archivo, algo está mal, pero aquí asumimos que ya viene bien de generator.php
    // La lógica nueva en generator.php produce URLs tipo /calendarios/empresa_NIT_2026.ics
}

// URL Google Calendar (Suscripción Directa usando URL estática pública)
// Usamos render?cid=URL_ENCODED para abrir el diálogo de suscripción de Google
$googleSubscribeUrl = "https://calendar.google.com/calendar/render?cid=" . urlencode($icsUrl);

// URL de descarga directa (es la misma URL estática)
$downloadUrl = $icsUrl;

// Limpiar sesión (opcional, si queremos que al recargar se pierda, pero mejor mantener si recarga)
// unset($_SESSION['calendar_result']); 
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario Generado | Calendario Tributario 2026</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .result-container { max-width: 600px; }
        .success-header { text-align: center; padding: 24px 20px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); border-radius: var(--radius-md) var(--radius-md) 0 0; color: white; }
        .success-icon { width: 64px; height: 64px; border-radius: 50%; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
        .success-icon .material-icons { font-size: 32px; }
        .calendar-btn { display: flex; align-items: center; gap: 16px; padding: 16px 20px; border: 2px solid var(--border-color); border-radius: var(--radius-md); margin-bottom: 12px; cursor: pointer; transition: all var(--transition); text-decoration: none; color: var(--text-primary); background: white; }
        .calendar-btn:hover { border-color: var(--accent-primary); background: var(--accent-light); }
        .calendar-btn-icon { width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .calendar-btn-icon.google { background: #fff; }
        .calendar-btn-icon.email { background: #f1f5f9; color: #64748b; }
        .calendar-btn-content { flex: 1; }
        .calendar-btn-title { font-size: 14px; font-weight: 600; margin-bottom: 2px; }
        .calendar-btn-desc { font-size: 12px; color: var(--text-secondary); }
        .summary-box { background: var(--bg-main); border-radius: var(--radius-md); padding: 16px; margin-bottom: 20px; }
        .summary-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 12px; }
        .summary-value { font-weight: 500; }
        .divider { height: 1px; background: var(--border-color); margin: 20px 0; }
        .back-link { display: flex; align-items: center; justify-content: center; gap: 6px; color: var(--text-secondary); font-size: 13px; text-decoration: none; padding: 12px; }
        .back-link:hover { color: var(--accent-primary); }
    </style>
</head>

<body>
    <div class="app-wrapper">
        <header class="top-header">
            <div class="header-brand">
                <span class="material-icons" style="color: #0ea5e9;">event_available</span>
                <h1>Calendario Tributario <span>by Dataeficiencia</span></h1>
            </div>
        </header>

        <main class="main-content">
            <div class="container result-container">
                <div class="card" style="overflow: hidden;">
                    <div class="success-header">
                        <div class="success-icon">
                            <span class="material-icons">check</span>
                        </div>
                        <h2>¡Calendario Listo!</h2>
                        <p><?php echo $eventosCount; ?> obligaciones para <?php echo htmlspecialchars($nit); ?></p>
                    </div>

                    <div class="calendar-options">
                        <div class="summary-box">
                            <div class="summary-row">
                                <span class="summary-label">NIT:</span>
                                <span class="summary-value"><?php echo htmlspecialchars($nit); ?></span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Ciudad:</span>
                                <span class="summary-value"><?php echo htmlspecialchars($ciudad); ?></span>
                            </div>
                            <!--
                            <div class="summary-row">
                                <span class="summary-label">URL:</span>
                                <span class="summary-value" style="font-size: 10px; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo htmlspecialchars($icsUrl); ?></span>
                            </div>
                            -->
                        </div>

                        <!-- Opción 1: Google Calendar (Principal) -->
                        <a href="<?php echo htmlspecialchars($googleSubscribeUrl); ?>" target="_blank" class="calendar-btn">
                            <div class="calendar-btn-icon google">
                                <svg viewBox="0 0 24 24" width="32" height="32">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                                </svg>
                            </div>
                            <div class="calendar-btn-content">
                                <div class="calendar-btn-title">Suscribirse en Google Calendar</div>
                                <div class="calendar-btn-desc">Sincronización automática a la nube</div>
                            </div>
                            <span class="material-icons arrow">open_in_new</span>
                        </a>

                        <!-- Opción 2: Enviar por Correo -->
                        <div class="calendar-btn" onclick="sendEmail()">
                            <div class="calendar-btn-icon email">
                                <span class="material-icons">email</span>
                            </div>
                            <div class="calendar-btn-content">
                                <div class="calendar-btn-title">Enviar por Correo</div>
                                <div class="calendar-btn-desc">Recibe el enlace y archivo adjunto</div>
                            </div>
                            <span class="material-icons arrow">send</span>
                        </div>

                        <div class="divider"></div>

                        <!-- Opción 3: Descarga Directa -->
                        <a href="<?php echo htmlspecialchars($downloadUrl); ?>" download class="calendar-btn" style="border: none; background: none; padding: 0;">
                            <div class="calendar-btn-content">
                                <div class="calendar-btn-title" style="color: var(--accent-primary); font-size: 13px;">⬇ Descargar archivo .ICS físico</div>
                            </div>
                        </a>

                        <a href="index.php" class="back-link" style="margin-top: 20px;">
                            <span class="material-icons" style="font-size: 18px;">arrow_back</span>
                            Generar otro calendario
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <footer class="app-footer">
            <p>&copy; 2026 <a href="#">Dataeficiencia</a>. Calendario informativo.</p>
        </footer>
    </div>

    <script>
        const icsUrl = <?php echo json_encode($icsUrl); ?>;

        function sendEmail() {
            Swal.fire({
                title: 'Enviar Calendario',
                input: 'email',
                inputLabel: 'Ingresa tu correo electrónico',
                inputPlaceholder: 'tu@correo.com',
                showCancelButton: true,
                confirmButtonText: 'Enviar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#0ea5e9',
                showLoaderOnConfirm: true,
                preConfirm: (email) => {
                    return fetch('send_email.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ email: email, icsUrl: icsUrl })
                    })
                    .then(response => {
                        if (!response.ok) throw new Error(response.statusText)
                        return response.json()
                    })
                    .then(data => {
                        if (!data.success) throw new Error(data.message)
                        return data
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Error: ${error}`)
                    })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Enviado!',
                        text: 'Revisa tu bandeja de entrada (y spam).',
                        confirmButtonColor: '#0ea5e9'
                    })
                }
            })
        }
    </script>
</body>

</html>