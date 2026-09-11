<?php
/**
 * CLEA System - Logout
 */

session_start();

require_once __DIR__ . '/../../config/config.php';

// Destroy session
session_destroy();

// Redirect to login
header('Location: ' . APP_URL . '/index.php');
exit;
