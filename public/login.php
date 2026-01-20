<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/src/config.php';
require_once dirname(__DIR__) . '/src/Auth.php';

use App\Auth;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getConnection();
    if (!$pdo) {
        $error = "Error de conexión. Verifica la base de datos.";
    } else {
        $auth = new Auth($pdo);
        $email = trim($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';

        if ($auth->login($email, $pass)) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Credenciales incorrectas.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Calendario Tributario SaaS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .auth-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .auth-header h1 {
            font-size: 24px;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .btn-block {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            background: #fee2e2;
            color: #991b1b;
        }

        .auth-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .auth-footer a {
            color: var(--accent-primary);
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>

<body style="background-color: #f8fafc;">
    <div class="auth-container">
        <div class="auth-header">
            <span class="material-icons" style="font-size: 48px; color: #0ea5e9;">lock</span>
            <h1>Iniciar Sesión</h1>
            <p style="color: #64748b;">Accede a tus calendarios guardados</p>
        </div>

        <div class="auth-card">
            <?php if ($error): ?>
                <div class="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Entrar</button>
            </form>

            <div class="auth-footer">
                ¿No tienes cuenta? <a href="register.php">Regístrate gratis</a>
            </div>
        </div>
    </div>
</body>

</html>