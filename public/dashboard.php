<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Auth.php';
require_once dirname(__DIR__) . '/src/Models/Company.php';

use App\Auth;
use App\Models\Company;

$pdo = getConnection();
if (!$pdo) {
    die("Error de conexión a la base de datos.");
}

$auth = new Auth($pdo);
$auth->requireLogin();
$user = $auth->getCurrentUser();

$companyModel = new Company($pdo);
$companies = $companyModel->findByUser($user['id']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Calendario Tributario</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .dashboard-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .welcome-text h1 {
            font-size: 24px;
            color: var(--text-primary);
        }

        .welcome-text p {
            color: var(--text-secondary);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
        }

        .companies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .company-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.2s;
        }

        .company-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-color: var(--accent-primary);
        }

        .company-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .company-icon {
            width: 40px;
            height: 40px;
            background: var(--accent-light);
            color: var(--accent-primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .company-title {
            font-weight: 600;
            font-size: 16px;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .company-meta {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .company-actions {
            margin-top: 15px;
            border-top: 1px solid var(--border-color);
            padding-top: 15px;
            display: flex;
            gap: 10px;
        }

        .action-link {
            font-size: 13px;
            color: var(--text-secondary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        .action-link:hover {
            color: var(--accent-primary);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            border: 2px dashed var(--border-color);
            width: 100%;
            grid-column: 1 / -1;
        }

        .empty-icon {
            font-size: 48px;
            color: var(--text-muted);
            margin-bottom: 10px;
        }
    </style>
</head>

<body style="background-color: #f8fafc;">
    <div class="app-wrapper">
        <header class="top-header">
            <div class="header-brand">
                <span class="material-icons" style="color: #0ea5e9;">event_available</span>
                <h1>Calendario Tributario <span>SaaS</span></h1>
            </div>
            <div class="user-menu">
                <span style="font-size: 14px; font-weight: 500;">Hola,
                    <?php echo htmlspecialchars($user['full_name']); ?>
                </span>
                <a href="logout.php" class="btn btn-secondary btn-sm">Salir</a>
            </div>
        </header>

        <main class="dashboard-container">
            <div class="dashboard-header">
                <div class="welcome-text">
                    <h1>Mis Empresas</h1>
                    <p>Gestiona los calendarios tributarios de tus clientes.</p>
                </div>
                <a href="create_company.php" class="btn btn-primary">
                    <span class="material-icons">add</span>
                    Nueva Empresa
                </a>
            </div>

            <div class="companies-grid">
                <?php if (empty($companies)): ?>
                    <div class="empty-state">
                        <span class="material-icons empty-icon">domain_disabled</span>
                        <h3>No tienes empresas registradas</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 20px;">Comienza creando tu primera empresa
                            para generar su calendario.</p>
                        <a href="create_company.php" class="btn btn-primary">Crear Empresa</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($companies as $company): ?>
                        <div class="company-card">
                            <div class="company-header">
                                <div>
                                    <div class="company-title">
                                        <?php echo htmlspecialchars($company['name']); ?>
                                    </div>
                                    <div class="company-meta">NIT:
                                        <?php echo htmlspecialchars($company['nit'] . '-' . $company['dv']); ?>
                                    </div>
                                    <div class="company-meta">
                                        <?php echo htmlspecialchars($company['city'] ?? 'Nacional'); ?>
                                    </div>
                                </div>
                                <div class="company-icon">
                                    <span class="material-icons">business</span>
                                </div>
                            </div>
                            <div class="company-actions">
                                <a href="company_calendar.php?id=<?php echo $company['id']; ?>" class="action-link"
                                    style="color: var(--accent-primary); font-weight: 500;">
                                    <span class="material-icons" style="font-size: 16px;">calendar_month</span> Ver Calendario
                                </a>
                                <a href="edit_company.php?id=<?php echo $company['id']; ?>" class="action-link">
                                    <span class="material-icons" style="font-size: 16px;">settings</span> Config
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>