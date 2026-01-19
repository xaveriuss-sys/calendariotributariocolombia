<?php
/**
 * Calendario Tributario Colombia 2026
 * Wizard de Instalación
 */

require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/database.php';

// Si ya está instalado, redirigir al inicio
if (isInstalled()) {
    header('Location: index.php');
    exit;
}

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'test_connection') {
        // Paso 1: Probar conexión
        $host = trim($_POST['db_host'] ?? 'localhost');
        $user = trim($_POST['db_user'] ?? '');
        $pass = $_POST['db_pass'] ?? '';
        $dbname = trim($_POST['db_name'] ?? 'calendario_tributario');
        
        if (empty($user)) {
            $error = 'El usuario de MySQL es requerido.';
        } else {
            $result = testConnection($host, $user, $pass);
            
            if ($result['success']) {
                // Guardar en sesión para el siguiente paso
                session_start();
                $_SESSION['install'] = [
                    'db_host' => $host,
                    'db_user' => $user,
                    'db_pass' => $pass,
                    'db_name' => $dbname
                ];
                
                // Verificar si la base de datos existe
                $pdo = $result['pdo'];
                $dbExists = databaseExists($pdo, $dbname);
                
                $_SESSION['install']['db_exists'] = $dbExists;
                
                header('Location: install.php?step=2');
                exit;
            } else {
                $error = 'Error de conexión: ' . $result['error'];
            }
        }
    } elseif ($action === 'create_database') {
        // Paso 2: Crear base de datos y tablas
        session_start();
        
        if (!isset($_SESSION['install'])) {
            header('Location: install.php?step=1');
            exit;
        }
        
        $config = $_SESSION['install'];
        
        // Conectar sin base de datos
        $result = testConnection($config['db_host'], $config['db_user'], $config['db_pass']);
        
        if (!$result['success']) {
            $error = 'Error de conexión: ' . $result['error'];
        } else {
            $pdo = $result['pdo'];
            
            // Crear base de datos si no existe
            if (!$config['db_exists']) {
                if (!createDatabase($pdo, $config['db_name'])) {
                    $error = 'No se pudo crear la base de datos.';
                }
            }
            
            if (empty($error)) {
                // Conectar a la base de datos
                $pdo->exec("USE `{$config['db_name']}`");
                
                // Ejecutar script de instalación
                $setupResult = runDatabaseSetup($pdo);
                
                if ($setupResult['success']) {
                    // Guardar configuración
                    saveConfig([
                        'db_host' => $config['db_host'],
                        'db_user' => $config['db_user'],
                        'db_pass' => $config['db_pass'],
                        'db_name' => $config['db_name'],
                        'installed_at' => date('Y-m-d H:i:s'),
                        'version' => '1.0.0'
                    ]);
                    
                    // Limpiar sesión
                    unset($_SESSION['install']);
                    
                    header('Location: install.php?step=3');
                    exit;
                } else {
                    $error = 'Error al crear las tablas: ' . $setupResult['error'];
                }
            }
        }
    }
}

// Obtener datos de sesión para paso 2
$installData = null;
if ($step === 2) {
    session_start();
    $installData = $_SESSION['install'] ?? null;
    if (!$installData) {
        header('Location: install.php?step=1');
        exit;
    }
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
        <!-- Header -->
        <header class="top-header">
            <div class="header-brand">
                <span class="material-icons" style="color: #0ea5e9;">event_available</span>
                <h1>Calendario Tributario <span>Instalación</span></h1>
            </div>
        </header>
        
        <!-- Contenido -->
        <main class="main-content">
            <div class="container install-container">
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
                    <div class="step-dot <?php echo $step === 3 ? 'completed' : ''; ?>">
                        <?php echo $step === 3 ? '<span class="material-icons" style="font-size:16px">check</span>' : '3'; ?>
                    </div>
                </div>
                
                <div class="card">
                    <?php if ($step === 1): ?>
                    <!-- PASO 1: Configuración de Base de Datos -->
                    <div class="card-header">
                        <span class="material-icons">storage</span>
                        <h2>Configuración de Base de Datos</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                        <div class="alert alert-error">
                            <span class="material-icons">error</span>
                            <span><?php echo htmlspecialchars($error); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-box">
                            <span class="material-icons">info</span>
                            <p>Ingrese los datos de conexión a MySQL. Si la base de datos no existe, se creará automáticamente.</p>
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="test_connection">
                            
                            <div class="form-group">
                                <label class="form-label">Servidor MySQL <span class="required">*</span></label>
                                <input type="text" class="form-input" name="db_host" value="localhost" required>
                                <p class="form-hint">Generalmente es "localhost" o "127.0.0.1"</p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Usuario MySQL <span class="required">*</span></label>
                                <input type="text" class="form-input" name="db_user" placeholder="root" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Contraseña MySQL</label>
                                <input type="password" class="form-input" name="db_pass" placeholder="••••••••">
                                <p class="form-hint">Déjelo vacío si no tiene contraseña</p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Nombre de Base de Datos <span class="required">*</span></label>
                                <input type="text" class="form-input" name="db_name" value="calendario_tributario" required>
                            </div>
                            
                            <div class="form-group" style="margin-top: 24px;">
                                <button type="submit" class="btn btn-primary btn-lg btn-block">
                                    <span class="material-icons">arrow_forward</span>
                                    Probar Conexión
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <?php elseif ($step === 2): ?>
                    <!-- PASO 2: Crear Tablas -->
                    <div class="card-header">
                        <span class="material-icons">table_chart</span>
                        <h2>Crear Base de Datos</h2>
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
                            <span>Conexión exitosa a MySQL</span>
                        </div>
                        
                        <div class="db-info">
                            <div class="db-info-row">
                                <span class="db-info-label">Servidor:</span>
                                <span class="db-info-value"><?php echo htmlspecialchars($installData['db_host']); ?></span>
                            </div>
                            <div class="db-info-row">
                                <span class="db-info-label">Usuario:</span>
                                <span class="db-info-value"><?php echo htmlspecialchars($installData['db_user']); ?></span>
                            </div>
                            <div class="db-info-row">
                                <span class="db-info-label">Base de datos:</span>
                                <span class="db-info-value"><?php echo htmlspecialchars($installData['db_name']); ?></span>
                            </div>
                            <div class="db-info-row">
                                <span class="db-info-label">Estado:</span>
                                <span class="db-info-value">
                                    <?php echo $installData['db_exists'] ? 'Existente (se actualizará)' : 'Se creará nueva'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-box">
                            <span class="material-icons">info</span>
                            <p>
                                Se crearán las tablas <strong>tax_rules</strong> y <strong>tax_deadlines_2026</strong> 
                                con todas las fechas tributarias del año 2026.
                            </p>
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="create_database">
                            
                            <div class="form-group" style="margin-top: 24px; display: flex; gap: 12px;">
                                <a href="install.php?step=1" class="btn btn-secondary" style="flex: 1; text-decoration: none; text-align: center;">
                                    <span class="material-icons">arrow_back</span>
                                    Atrás
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg" style="flex: 2;">
                                    <span class="material-icons">play_arrow</span>
                                    Instalar Ahora
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <?php elseif ($step === 3): ?>
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
        
        <!-- Footer -->
        <footer class="app-footer">
            <p>&copy; 2026 <a href="#">Dataeficiencia</a></p>
        </footer>
    </div>
</body>
</html>
