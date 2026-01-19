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

// URL de descarga (usando misma URL estática pero forzando descarga)
$downloadUrl = $icsUrl . '?dl=1';

// URL con protocolo webcal (para apps de calendario nativas)
$webcalUrl = str_replace(['https://', 'http://'], 'webcal://', $icsUrl);

// Limpiar sesión
unset($_SESSION['calendar_result']);
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
        .result-container {
            max-width: 600px;
        }

        .success-header {
            text-align: center;
            padding: 24px 20px;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border-radius: var(--radius-md) var(--radius-md) 0 0;
            color: white;
        }

        .success-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .success-icon .material-icons {
            font-size: 32px;
        }

        .success-header h2 {
            font-size: 18px;
            margin-bottom: 4px;
        }

        .success-header p {
            font-size: 13px;
            opacity: 0.9;
        }

        .calendar-options {
            padding: 24px 20px;
        }

        .calendar-btn {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            margin-bottom: 12px;
            cursor: pointer;
            transition: all var(--transition);
            text-decoration: none;
            color: var(--text-primary);
            background: white;
        }

        .calendar-btn:hover {
            border-color: var(--accent-primary);
            background: var(--accent-light);
        }

        .calendar-btn-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .calendar-btn-icon.google {
            background: #fff;
        }

        .calendar-btn-icon.outlook {
            background: #fff;
        }

        .calendar-btn-icon.download {
            background: var(--accent-light);
        }

        .calendar-btn-icon.download .material-icons {
            font-size: 28px;
            color: var(--accent-primary);
        }

        .calendar-btn-content {
            flex: 1;
        }

        .calendar-btn-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .calendar-btn-desc {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .calendar-btn .material-icons.arrow {
            color: var(--text-muted);
            font-size: 20px;
        }

        .summary-box {
            background: var(--bg-main);
            border-radius: var(--radius-md);
            padding: 16px;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 12px;
        }

        .summary-label {
            color: var(--text-secondary);
        }

        .summary-value {
            font-weight: 500;
        }

        .divider {
            height: 1px;
            background: var(--border-color);
            margin: 20px 0;
        }

        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: var(--text-secondary);
            font-size: 13px;
            text-decoration: none;
            padding: 12px;
        }

        .back-link:hover {
            color: var(--accent-primary);
        }

        .url-box {
            background: var(--bg-main);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 10px 12px;
            margin: 12px 0;
            font-size: 11px;
            word-break: break-all;
            color: var(--text-secondary);
            font-family: monospace;
        }

        .copy-btn {
            background: var(--accent-primary);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            padding: 8px 16px;
            font-size: 12px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 8px;
        }

        .copy-btn:hover {
            background: var(--accent-hover);
        }

        .copy-btn .material-icons {
            font-size: 16px;
        }

        .instructions {
            font-size: 12px;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .instructions ol {
            margin: 8px 0;
            padding-left: 20px;
        }

        .instructions li {
            margin-bottom: 6px;
        }
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
                        <h2>¡Calendario Generado!</h2>
                        <p><?php echo $eventosCount; ?> eventos tributarios para 2026</p>
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
                            <div class="summary-row">
                                <span class="summary-label">Eventos:</span>
                                <span class="summary-value"><?php echo $eventosCount; ?> obligaciones</span>
                            </div>
                        </div>

                        <p
                            style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px; text-align: center;">
                            Seleccione cómo desea agregar los eventos a su calendario:
                        </p>

                        <!-- Descargar ICS (Opción principal) -->
                        <a href="<?php echo htmlspecialchars($downloadUrl); ?>" download class="calendar-btn"
                            style="border-color: var(--accent-primary); background: var(--accent-light);">
                            <div class="calendar-btn-icon download">
                                <span class="material-icons">download</span>
                            </div>
                            <div class="calendar-btn-content">
                                <div class="calendar-btn-title">Descargar archivo .ICS</div>
                                <div class="calendar-btn-desc">Recomendado - Compatible con Outlook, Google Calendar,
                                    Apple</div>
                            </div>
                            <span class="material-icons arrow">arrow_forward</span>
                        </a>

                        <div class="divider"></div>

                        <!-- Google Calendar -->
                        <div class="calendar-btn" onclick="showGoogleInstructions()">
                            <div class="calendar-btn-icon google">
                                <svg viewBox="0 0 24 24" width="32" height="32">
                                    <path fill="#4285F4"
                                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                    <path fill="#34A853"
                                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                    <path fill="#FBBC05"
                                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                    <path fill="#EA4335"
                                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                                </svg>
                            </div>
                            <div class="calendar-btn-content">
                                <div class="calendar-btn-title">Google Calendar</div>
                                <div class="calendar-btn-desc">Ver instrucciones para agregar</div>
                            </div>
                            <span class="material-icons arrow">arrow_forward</span>
                        </div>

                        <!-- Outlook -->
                        <a href="<?php echo htmlspecialchars($webcalUrl); ?>" class="calendar-btn">
                            <div class="calendar-btn-icon outlook">
                                <svg viewBox="0 0 24 24" width="32" height="32">
                                    <path fill="#0078D4"
                                        d="M24 7.387v10.478c0 .23-.08.424-.238.576-.159.152-.352.228-.581.228h-8.547v-6.95l1.56 1.373c.106.08.23.119.37.119.14 0 .264-.04.37-.119l6.828-5.705c.079-.062.159-.077.238-.046.08.031.119.093.119.186v-.14zm-.238-1.056c.159.152.238.345.238.576v-.576c0-.152-.04-.278-.119-.378-.08-.1-.186-.15-.32-.15h-.148l-7.78 6.476-7.781-6.476H.237c-.134 0-.24.05-.32.15-.079.1-.119.226-.119.378v.576c0-.231.08-.424.238-.576.159-.152.352-.228.581-.228h22.764c.229 0 .422.076.581.228zM7.633 22.67H.82c-.229 0-.422-.076-.581-.228-.159-.152-.238-.345-.238-.576V8.866l7.633 6.358v7.446zm8.547-7.446l7.78-6.358v13c0 .231-.08.424-.238.576-.159.152-.352.228-.581.228h-6.961v-7.446z" />
                                    <path fill="#0078D4"
                                        d="M7.633 4.66v17.467H.82c-.229 0-.422-.076-.581-.228-.159-.152-.238-.345-.238-.576V5.466c0-.231.08-.424.238-.576.159-.152.352-.228.581-.228h6.813z" />
                                    <path fill="#28A8EA"
                                        d="M7.633 4.66L0 11.018V5.466c0-.231.08-.424.238-.576.159-.152.352-.228.581-.228h6.814z" />
                                </svg>
                            </div>
                            <div class="calendar-btn-content">
                                <div class="calendar-btn-title">Outlook / Apple Calendar</div>
                                <div class="calendar-btn-desc">Suscribirse al calendario (webcal://)</div>
                            </div>
                            <span class="material-icons arrow">arrow_forward</span>
                        </a>

                        <a href="index.php" class="back-link">
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

        function showGoogleInstructions() {
            Swal.fire({
                title: 'Agregar a Google Calendar',
                html: `
                    <div style="text-align: left; font-size: 13px;">
                        <p style="margin-bottom: 12px;">Siga estos pasos para agregar el calendario:</p>
                        <ol style="padding-left: 20px; line-height: 1.8;">
                            <li>Abra <a href="https://calendar.google.com" target="_blank" style="color: #0ea5e9;">calendar.google.com</a></li>
                            <li>En el panel izquierdo, busque "Otros calendarios"</li>
                            <li>Haga clic en el <strong>+</strong> y seleccione <strong>"Desde URL"</strong></li>
                            <li>Pegue esta URL:</li>
                        </ol>
                        <div style="background: #f1f5f9; border-radius: 4px; padding: 10px; margin: 10px 0; font-family: monospace; font-size: 11px; word-break: break-all; color: #64748b;">
                            ${icsUrl}
                        </div>
                        <button onclick="copyUrl()" style="background: #0ea5e9; color: white; border: none; border-radius: 4px; padding: 8px 16px; cursor: pointer; font-size: 12px;">
                            <span style="margin-right: 4px;">📋</span> Copiar URL
                        </button>
                    </div>
                `,
                showConfirmButton: true,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#0ea5e9',
                width: 500
            });
        }

        function copyUrl() {
            navigator.clipboard.writeText(icsUrl).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: '¡URL Copiada!',
                    text: 'Ahora puede pegarla en Google Calendar',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        }
    </script>
</body>

</html>