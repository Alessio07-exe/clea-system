<?php
/**
 * CLEA System - Header/Navigation for Authenticated Users
 */

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? esc($page_title) . ' - ' . APP_TITLE : APP_TITLE; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/responsive.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <h1><?php echo APP_NAME; ?></h1>
            </div>
            
            <div class="navbar-menu">
                <a href="<?php echo APP_URL; ?>/private/dashboard.php" class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                    Dashboard
                </a>
                <a href="<?php echo APP_URL; ?>/private/macchinari.php" class="nav-link <?php echo $current_page === 'macchinari.php' ? 'active' : ''; ?>">
                    Macchinari
                </a>
                <a href="<?php echo APP_URL; ?>/private/nuova_installazione.php" class="nav-link <?php echo $current_page === 'nuova_installazione.php' ? 'active' : ''; ?>">
                    + Nuova Installazione
                </a>
                <a href="<?php echo APP_URL; ?>/private/auth/logout.php" class="nav-link logout">
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <?php
    // Display flash messages if any
    $flash = getFlash();
    if ($flash):
    ?>
    <div class="alert alert-<?php echo esc($flash['type']); ?>">
        <p><?php echo esc($flash['message']); ?></p>
    </div>
    <?php endif; ?>

    <div class="container">
