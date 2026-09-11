<?php
/**
 * CLEA System - Login Page
 */

session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// If already logged in, redirect to dashboard
if (isAuthenticated()) {
    redirect('/private/dashboard.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate input
    if (empty($email) || empty($password)) {
        $error = 'Email e password sono obbligatori';
    } elseif (!validateEmail($email)) {
        $error = 'Formato email non valido';
    } else {
        try {
            // Check CSRF token
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $error = 'Token di sicurezza non valido';
            } else {
                // Check user credentials
                $user = $db->fetch(
                    "SELECT id, email, password_hash, nome, cognome, ruolo FROM users WHERE email = ?",
                    [$email]
                );

                if ($user && verifyPassword($password, $user['password_hash'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['nome'] . ' ' . $user['cognome'];
                    $_SESSION['user_role'] = $user['ruolo'];
                    $_SESSION['last_activity'] = time();

                    redirect('/private/dashboard.php');
                } else {
                    $error = 'Email o password non corretti';
                }
            }
        } catch (Exception $e) {
            $error = 'Errore durante il login';
        }
    }
}

$page_title = 'Login';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_TITLE; ?> - Login</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/responsive.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1 class="login-title"><?php echo APP_NAME; ?></h1>
            <p class="login-subtitle">Sistema di Gestione Manutenzione</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <p><?php echo esc($error); ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['session_expired'])): ?>
                <div class="alert alert-warning">
                    <p>Sessione scaduta. Effettua il login di nuovo.</p>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo esc($email); ?>" 
                        required
                        placeholder="tecnico@azienda.it"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        placeholder="••••••••"
                    >
                </div>

                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <button type="submit" class="btn btn-primary btn-block">
                    Accedi
                </button>
            </form>

            <div class="login-info">
                <p>Demo: usa le credenziali di prova per testare il sistema</p>
                <hr>
                <p><strong>Email:</strong> tecnico@clea.it</p>
                <p><strong>Password:</strong> password123</p>
            </div>
        </div>
    </div>
</body>
</html>
