<?php
/**
 * CLEA System - Authentication Check
 * Verify user session and redirect if not authenticated
 */

session_start();

require_once __DIR__ . '/../../config/config.php';

// Check session timeout
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        session_destroy();
        header('Location: ' . APP_URL . '/index.php?session_expired=1');
        exit;
    }
}

$_SESSION['last_activity'] = time();

// Check if user is authenticated
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/index.php');
    exit;
}
