<?php
/**
 * Calendario Tributario Colombia 2026
 * Wizard de Instalación
 * 
 * Solo conecta a base de datos existente y crea/actualiza tablas.
 * La base de datos debe crearse desde el panel de hosting (Ploi.io).
 */

require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/database.php';

// Versión actual de la base de datos
define('DB_VERSION', '1.0.0');

// Si ya está instalado, verificar actualizaciones
if (isInstalled()) {
    $config = getConfig();
    if (isset($config['db_version']) && version_compare($config['db_version'], DB_VERSION, '>=')) {
        header('Location: index.php');
        exit;
    }
    // Si la versión es anterior, redirigir al paso de actualización
    header('Location: install.php?step=update');
    exit;
}

$step = $_GET['step'] ?? '1';
$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'test_connection') {
        // Paso 1: Probar conexión a base de datos EXISTENTE
        $host = trim($_POST['db_host'] ?? 'localhost');
        $user = trim($_POST['db_user'] ?? '');
        $pass = $_POST['db_pass'] ?? '';
        $dbname = trim($_POST['db_name'] ?? '');

        if (empty($user) || empty($dbname)) {
            $error = 'Usuario y nombre de base de datos son requeridos.';
        } else {
            // Intentar conectar DIRECTAMENTE a la base de datos
            $pdo = getDBConnection($host, $dbname, $user, $pass);

            if ($pdo) {
                // Conexión exitosa
                session_start();
                $_SESSION['install'] = [
                    'db_host' => $host,
                    'db_user' => $user,
                    'db_pass' => $pass,
                    'db_name' => $dbname
                ];

                // Verificar si las tablas ya existen
                $_SESSION['install']['tables_exist'] = tablesExist($pdo);

                header('Location: install.php?step=2');
                exit;
            } else {
                $error = 'No se pudo conectar a la base de datos. Verifique que la base de datos exista y las credenciales sean correctas.';
            }
        }
    } elseif ($action === 'create_tables') {
        // Paso 2: Crear o actualizar tablas
        session_start();

        if (!isset($_SESSION['install'])) {
            header('Location: install.php?step=1');
            exit;
        }

        $config = $_SESSION['install'];
        $pdo = getDBConnection($config['db_host'], $config['db_name'], $config['db_user'], $config['db_pass']);

        if (!$pdo) {
            $error = 'Error de conexión a la base de datos.';
        } else {
            // Ejecutar script de instalación/actualización
            $setupResult = runDatabaseSetup($pdo);

            if ($setupResult['success']) {
                // Guardar configuración con versión
                saveConfig([
                    'db_host' => $config['db_host'],
                    'db_user' => $config['db_user'],
                    'db_pass' => $config['db_pass'],
                    'db_name' => $config['db_name'],
                    'db_version' => DB_VERSION,
                    'installed_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                unset($_SESSION['install']);

                header('Location: install.php?step=3');
                exit;
            } else {
                $error = 'Error al crear las tablas: ' . $setupResult['error'];
            }
        }
    } elseif ($action === 'update_tables') {
        // Actualizar tablas existentes
        $config = getConfig();
        $pdo = getDBConnection($config['db_host'], $config['db_name'], $config['db_user'], $config['db_pass']);

        if (!$pdo) {
            $error = 'Error de conexión a la base de datos.';
        } else {
            $setupResult = runDatabaseSetup($pdo);

            if ($setupResult['success']) {
                // Actualizar versión
                $config['db_version'] = DB_VERSION;
                $config['updated_at'] = date('Y-m-d H:i:s');
                saveConfig($config);

                header('Location: install.php?step=3');
                exit;
            } else {
                $error = 'Error al actualizar las tablas: ' . $setupResult['error'];
            }
        }
    }
}

// Obtener datos de sesión para paso 2
$installData = null;
if ($step === '2') {
    session_start();
    $installData = $_SESSION['install'] ?? null;
    if (!$installData) {
        header('Location: install.php?step=1');
        exit;
    }
}

// Datos para actualización
$updateData = null;
if ($step === 'update') {
    $updateData = getConfig();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación | Calendario Tributario 2026</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .install-container {
            max-width: 500px;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .step-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
        }

        .step-dot.active {
            background: var(--accent-primary);
            color: white;
        }

        .step-dot.completed {
            background: var(--success);
            color: white;
        }

        .step-line {
            width: 40px;
            height: 2px;
            background: var(--border-color);
            align-self: center;
        }

        .step-line.completed {
            background: var(--success);
        }

        .alert {
            padding: 12px 16px;
            border-radius: var(--radius-md);
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-error {
            background: var(--danger-light);
            border: 1px solid var(--danger);
            color: var(--danger);
        }

        .alert-success {
            background: var(--success-light);
            border: 1px solid var(--success);
            color: #15803d;
        }

        .alert-warning {
            background: var(--warning-light);
            border: 1px solid var(--warning);
            color: #92400e;
        }

        .alert .material-icons {
            font-size: 20px;
            flex-shrink: 0;
        }

        .db-info {
            background: var(--bg-main);
            border-radius: var(--radius-md);
            padding: 12px 16px;
            margin-bottom: 16px;
        }

        .db-info-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 12px;
        }

        .db-info-label {
            color: var(--text-secondary);
        }

        .db-info-value {
            font-weight: 500;
        }

        .success-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--success-light);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .success-icon .material-icons {
            font-size: 32px;
            color: var(--success);
        }
    </style>
</head>

<body>
    <div class="app-wrapper">
        <header class="top-header">
            <div class="header-brand">
                <span class="material-icons" style="color: #0ea5e9;">event_available</span>
                <h1>Calendario Tributario <span>Instalación</span></h1>
            </div>
        </header>

        <main class="main-content">
            <div class="container install-container">
                <?php if ($step !== 'update'): ?>
                    <!-- Indicador de pasos -->
                    <div class="step-indicator">
                        <div class="step-dot <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">
                            <?php echo $step > 1 ? '<span class="material-icons" style="font-size:16px">check</span>' : '1'; ?>
                        </div>
                        <div class="step-line <?php echo $step > 1 ? 'completed' : ''; ?>"></div>
                        <div class="step-dot <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">
                            <?php echo $step > 2 ? '<span class="material-icons" style="font-size:16px">check</span>' : '2'; ?>
                        </div>
                        <div class="step-line <?php echo $step > 2 ? 'completed' : ''; ?>"></div>
                        <div class="step-dot <?php echo $step == 3 ? 'completed' : ''; ?>">
                            <?php echo $step == 3 ? '<span class="material-icons" style="font-size:16px">check</span>' : '3'; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <?php if ($step === '1'): ?>
                        <!-- PASO 1: Conexión a Base de Datos Existente -->
                        <div class="card-header">
                            <span class="material-icons">storage</span>
                            <h2>Conexión a Base de Datos</h2>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-error">
                                    <span class="material-icons">error</span>
                                    <span><?php echo htmlspecialchars($error); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="alert alert-warning">
                                <span class="material-icons">info</span>
                                <span>La base de datos debe existir previamente. Créela desde el panel de Ploi.io antes de
                                    continuar.</span>
                            </div>

                            <form method="POST">
                                <input type="hidden" name="action" value="test_connection">

                                <div class="form-group">
                                    <label class="form-label">Servidor MySQL <span class="required">*</span></label>
                                    <input type="text" class="form-input" name="db_host" value="127.0.0.1" required>
                                    <p class="form-hint">Generalmente "127.0.0.1" o "localhost"</p>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Nombre de Base de Datos <span
                                            class="required">*</span></label>
                                    <input type="text" class="form-input" name="db_name" placeholder="calendario_tributario"
                                        required>
                                    <p class="form-hint">Nombre exacto de la base de datos creada en Ploi.io</p>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Usuario MySQL <span class="required">*</span></label>
                                    <input type="text" class="form-input" name="db_user" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Contraseña MySQL <span class="required">*</span></label>
                                    <input type="password" class="form-input" name="db_pass">
                                </div>

                                <div class="form-group" style="margin-top: 24px;">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                                        <span class="material-icons">arrow_forward</span>
                                        Conectar
                                    </button>
                                </div>
                            </form>
                        </div>

                    <?php elseif ($step === '2'): ?>
                        <!-- PASO 2: Crear Tablas -->
                        <div class="card-header">
                            <span class="material-icons">table_chart</span>
                            <h2>Crear Tablas</h2>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-error">
                                    <span class="material-icons">error</span>
                                    <span><?php echo htmlspecialchars($error); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="alert alert-success">
                                <span class="material-icons">check_circle</span>
                                <span>Conexión exitosa a la base de datos</span>
                            </div>

                            <div class="db-info">
                                <div class="db-info-row">
                                    <span class="db-info-label">Servidor:</span>
                                    <span
                                        class="db-info-value"><?php echo htmlspecialchars($installData['db_host']); ?></span>
                                </div>
                                <div class="db-info-row">
                                    <span class="db-info-label">Base de datos:</span>
                                    <span
                                        class="db-info-value"><?php echo htmlspecialchars($installData['db_name']); ?></span>
                                </div>
                                <div class="db-info-row">
                                    <span class="db-info-label">Tablas:</span>
                                    <span class="db-info-value">
                                        <?php echo $installData['tables_exist'] ? 'Existentes (se actualizarán)' : 'Se crearán nuevas'; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="info-box">
                                <span class="material-icons">info</span>
                                <p>
                                    Se crearán las tablas <strong>tax_rules</strong> y <strong>tax_deadlines_2026</strong>
                                    con ~70 fechas tributarias para el año 2026.
                                </p>
                            </div>

                            <form method="POST">
                                <input type="hidden" name="action" value="create_tables">

                                <div class="form-group" style="margin-top: 24px; display: flex; gap: 12px;">
                                    <a href="install.php?step=1" class="btn btn-secondary"
                                        style="flex: 1; text-decoration: none; text-align: center;">
                                        <span class="material-icons">arrow_back</span>
                                        Atrás
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg" style="flex: 2;">
                                        <span class="material-icons">play_arrow</span>
                                        Instalar Tablas
                                    </button>
                                </div>
                            </form>
                        </div>

                    <?php elseif ($step === 'update'): ?>
                        <!-- ACTUALIZACIÓN DE TABLAS -->
                        <div class="card-header">
                            <span class="material-icons">update</span>
                            <h2>Actualización Disponible</h2>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-error">
                                    <span class="material-icons">error</span>
                                    <span><?php echo htmlspecialchars($error); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="alert alert-warning">
                                <span class="material-icons">new_releases</span>
                                <span>Hay una nueva versión de las tablas disponible</span>
                            </div>

                            <div class="db-info">
                                <div class="db-info-row">
                                    <span class="db-info-label">Versión actual:</span>
                                    <span
                                        class="db-info-value"><?php echo htmlspecialchars($updateData['db_version'] ?? '0.0.0'); ?></span>
                                </div>
                                <div class="db-info-row">
                                    <span class="db-info-label">Nueva versión:</span>
                                    <span class="db-info-value"><?php echo DB_VERSION; ?></span>
                                </div>
                            </div>

                            <div class="info-box">
                                <span class="material-icons">info</span>
                                <p>Se actualizarán las tablas con los últimos datos tributarios. Los datos existentes serán
                                    reemplazados.</p>
                            </div>

                            <form method="POST">
                                <input type="hidden" name="action" value="update_tables">

                                <button type="submit" class="btn btn-primary btn-lg btn-block">
                                    <span class="material-icons">update</span>
                                    Actualizar Ahora
                                </button>
                            </form>
                        </div>

                    <?php elseif ($step === '3'): ?>
                        <!-- PASO 3: Completado -->
                        <div class="card-header">
                            <span class="material-icons">celebration</span>
                            <h2>¡Instalación Completada!</h2>
                        </div>
                        <div class="card-body" style="text-align: center;">
                            <div class="success-icon">
                                <span class="material-icons">check</span>
                            </div>

                            <h3 style="margin-bottom: 8px; color: var(--text-primary);">
                                Calendario Tributario 2026
                            </h3>
                            <p style="color: var(--text-secondary); margin-bottom: 24px;">
                                La aplicación ha sido instalada correctamente.<br>
                                Ya puede comenzar a generar calendarios tributarios.
                            </p>

                            <a href="index.php" class="btn btn-primary btn-lg btn-block" style="text-decoration: none;">
                                <span class="material-icons">arrow_forward</span>
                                Ir a la Aplicación
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <footer class="app-footer">
            <p>&copy; 2026 <a href="#">Dataeficiencia</a> | Versión <?php echo DB_VERSION; ?></p>
        </footer>
    </div>
</body>

</html>