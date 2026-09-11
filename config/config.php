<?php
/**
 * CLEA System - Configuration File
 * Database connection and application constants
 */

// Environment
define('ENV', 'development'); // development or production

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'clea_system');
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'CLEA');
define('APP_URL', 'http://localhost/clea-system');
define('APP_TITLE', 'CLEA - Sistema di Gestione Manutenzione');

// File Upload Settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/macchinari/');
define('UPLOAD_URL', APP_URL . '/uploads/macchinari/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Session Settings
define('SESSION_TIMEOUT', 3600); // 1 hour
ini_set('session.name', 'CLEA_SESSION');
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', ENV === 'production' ? 1 : 0);
ini_set('session.cookie_samesite', 'Lax');

// Security
define('CSRF_TOKEN_LENGTH', 32);

// PDF Settings
define('PDF_FONT_DIR', __DIR__ . '/../vendor/tcpdf/fonts/');
define('PDF_CACHE_DIR', __DIR__ . '/../vendor/tcpdf/cache/');

// QR Code Settings
define('QR_SIZE', 200); // pixels

// Error Reporting
if (ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Create necessary directories
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
