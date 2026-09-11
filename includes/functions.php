<?php
/**
 * CLEA System - Utility Functions
 * Common functions used throughout the application
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db.php';

// Initialize database connection
$db = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_CHARSET);

/**
 * Generate a random token for public machine access
 */
function generatePublicToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Generate CSRF token for form protection
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH / 2));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize input data
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Hash password for storage
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Handle file upload for machinery photos
 */
function uploadMachinePhoto($file) {
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds maximum allowed'];
    }

    // Get file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate extension
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => 'File format not allowed'];
    }

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ALLOWED_MIME_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file MIME type'];
    }

    // Generate unique filename
    $filename = 'photo_' . time() . '_' . uniqid() . '.' . $ext;
    $upload_path = UPLOAD_DIR . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => false, 'message' => 'Failed to save file'];
    }

    return [
        'success' => true,
        'filename' => $filename,
        'path' => UPLOAD_URL . $filename,
        'original_name' => $file['name']
    ];
}

/**
 * Delete a photo file and database record
 */
function deletePhoto($photo_id, $db) {
    $photo = $db->fetch("SELECT file_path FROM machine_photos WHERE id = ?", [$photo_id]);
    
    if ($photo) {
        $file_path = str_replace(UPLOAD_URL, UPLOAD_DIR, $photo['file_path']);
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $db->delete('machine_photos', 'id = ?', [$photo_id]);
        return true;
    }
    
    return false;
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'd/m/Y') {
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    return $dt ? $dt->format($format) : $date;
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
    return $dt ? $dt->format($format) : $datetime;
}

/**
 * Redirect to a page
 */
function redirect($path) {
    header('Location: ' . APP_URL . $path);
    exit;
}

/**
 * Set flash message in session
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function getCurrentUser($db) {
    if (!isAuthenticated()) {
        return null;
    }
    
    return $db->fetch(
        "SELECT id, email, nome, cognome, azienda, ruolo FROM users WHERE id = ?",
        [$_SESSION['user_id']]
    );
}

/**
 * Check if user has permission to modify a machine
 */
function canModifyMachine($machine_id, $user_id, $db) {
    $machine = $db->fetch(
        "SELECT technician_id FROM machines WHERE id = ?",
        [$machine_id]
    );
    
    // Only the original technician or admin can modify
    return $machine && ($machine['technician_id'] == $user_id || $_SESSION['user_role'] === 'admin');
}

/**
 * Escape output for HTML
 */
function esc($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Format maintenance type for display
 */
function formatMaintenanceType($type) {
    $types = [
        'ordinaria' => 'Manutenzione Ordinaria',
        'straordinaria' => 'Manutenzione Straordinaria'
    ];
    return $types[$type] ?? $type;
}
