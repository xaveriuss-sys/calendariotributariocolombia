<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Auth.php';
require_once dirname(__DIR__) . '/src/Models/Company.php';

use App\Auth;
use App\Models\Company;

$pdo = getConnection();
if (!$pdo)
    die("Error BD");
$auth = new Auth($pdo);
$auth->requireLogin();
$user = $auth->getCurrentUser();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $nit = preg_replace('/[^0-9]/', '', $_POST['nit'] ?? '');
    $dv = $_POST['nit_dv'] ?? '';
    $city = $_POST['ciudad'] ?? '';

    // Validaciones simples
    if (empty($name) || strlen($nit) < 5) {
        $error = "Por favor verifique los datos.";
    } else {
        $companyModel = new Company($pdo);
        // Guardamos todo el $_POST como settings para futuros cálculos (ingresos, ica, etc)
        $settings = $_POST;
        unset($settings['name'], $settings['nit'], $settings['nit_dv'], $settings['ciudad']);

        $data = [
            'name' => $name,
            'nit' => $nit,
            'dv' => $dv,
            'city' => $city,
            'settings' => $settings
        ];

        try {
            $companyId = $companyModel->create($user['id'], $data);
            // TODO: Generar calendario inicial aquí
            header("Location: dashboard.php");
            exit;
        } catch (Exception $e) {
            $error = "Error al guardar empresa: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Empresa | Calendario Tributario</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 30px auto;
        }

        .step-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title .material-icons {
            color: var(--accent-primary);
            font-size: 20px;
        }
    </style>
</head>

<body style="background-color: #f8fafc;">
    <div class="app-wrapper">
        <header class="top-header">
            <div class="header-brand">
                <span class="material-icons" style="color: #0ea5e9;">event_available</span>
                <h1>Nueva Empresa</h1>
            </div>
            <a href="dashboard.php" class="btn btn-secondary btn-sm">Cancelar</a>
        </header>

        <main class="form-container">
            <?php if ($error): ?>
                <div class="alert alert-error"
                    style="background:#fee2e2; color:#991b1b; padding:10px; border-radius:6px; margin-bottom:15px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <!-- Paso 1: Datos Básicos -->
                <div class="step-section">
                    <div class="section-title">
                        <span class="material-icons">business</span>
                        Datos de la Empresa
                    </div>

                    <div class="form-group">
                        <label class="form-label">Razón Social / Nombre</label>
                        <input type="text" name="name" class="form-input" required placeholder="Ej. Mi Empresa S.A.S.">
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="flex: 3;">
                            <label class="form-label">NIT (Sin DV)</label>
                            <input type="text" id="nit" name="nit" class="form-input" placeholder="Ej. 900123456"
                                maxlength="15" oninput="calcularDV()" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">DV</label>
                            <input type="text" id="nit_dv" name="nit_dv" class="form-input" readonly
                                style="background-color: var(--bg-main); text-align: center;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ciudad Principal</label>
                        <select name="ciudad" id="ciudad" class="form-select" onchange="toggleCamposCiudad()">
                            <option value="Bogotá">Bogotá D.C.</option>
                            <option value="Medellín">Medellín</option>
                            <option value="Cali">Cali</option>
                            <option value="Otra">Otra / Nivel Nacional</option>
                        </select>
                    </div>
                </div>

                <!-- Paso 2: Datos Financieros -->
                <div class="step-section">
                    <div class="section-title">
                        <span class="material-icons">attach_money</span>
                        Información Tributaria (Año Base 2025)
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ingresos Brutos 2025</label>
                        <input type="text" name="ingresos" class="form-input money-input" placeholder="$ 0"
                            onkeyup="formatCurrency(this)">
                        <p class="form-hint">Para definir periodicidad de IVA</p>
                    </div>

                    <div class="form-group" id="group-ica-bogota">
                        <label class="form-label">Impuesto a Cargo ICA 2025 (Solo Bogotá)</label>
                        <input type="text" name="ica_cargo" class="form-input money-input" placeholder="$ 0"
                            onkeyup="formatCurrency(this)">
                        <p class="form-hint">Para definir periodicidad de ICA en Bogotá</p>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    Guardar y Generar Calendario
                </button>
            </form>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Reutilizamos la lógica de validación del index original
        function calcularDV() {
            let nit = document.getElementById('nit').value.replace(/\D/g, '');
            if (nit.length === 0) {
                document.getElementById('nit_dv').value = '';
                return;
            }

            let pesos = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
            let suma = 0;
            let reversedNit = nit.split('').reverse().join('');

            for (let i = 0; i < reversedNit.length; i++) {
                suma += parseInt(reversedNit[i]) * pesos[i];
            }

            let residuo = suma % 11;
            let dv = residuo > 1 ? 11 - residuo : residuo;

            document.getElementById('nit_dv').value = dv;
        }

        function toggleCamposCiudad() {
            const ciudad = document.getElementById('ciudad').value;
            const groupIca = document.getElementById('group-ica-bogota');
            if (ciudad === 'Bogotá') {
                groupIca.style.display = 'block';
            } else {
                groupIca.style.display = 'none';
            }
        }

        function formatCurrency(input) {
            let value = input.value.replace(/\D/g, '');
            value = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(value);
            input.value = value;
        }
    </script>
</body>

</html>