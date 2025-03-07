<?php
// Security helper functions

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateFileUpload($file, $allowed_types, $max_size) {
    // Check file size
    if ($file['size'] > $max_size) {
        throw new Exception("File size exceeds limit");
    }

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        throw new Exception("Invalid file type");
    }

    // Check for PHP code in image
    $content = file_get_contents($file['tmp_name']);
    if (preg_match('/<\?php/i', $content)) {
        throw new Exception("Invalid file content");
    }

    return true;
}

function generateSecureFilename($original_name, $prefix = '') {
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    return $prefix . '_' . bin2hex(random_bytes(16)) . '.' . $ext;
}

// Rate limiting using session only
function checkRateLimit($key, $limit = 5, $time = 300) {
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }

    // Clean up expired rate limits
    foreach ($_SESSION['rate_limit'] as $k => $data) {
        if (time() - $data['time'] > $time) {
            unset($_SESSION['rate_limit'][$k]);
        }
    }

    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = [
            'attempts' => 1,
            'time' => time()
        ];
    } else {
        if (time() - $_SESSION['rate_limit'][$key]['time'] > $time) {
            $_SESSION['rate_limit'][$key] = [
                'attempts' => 1,
                'time' => time()
            ];
        } else if ($_SESSION['rate_limit'][$key]['attempts'] >= $limit) {
            throw new Exception("Terlalu banyak percobaan. Silakan coba lagi nanti.");
        } else {
            $_SESSION['rate_limit'][$key]['attempts']++;
        }
    }
}

if (!function_exists('createCSRFToken')) {
    function createCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('validateCSRFToken')) {
    function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        if (!is_string($token) || !is_string($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Use hash_equals to prevent timing attacks
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('regenerateCSRFToken')) {
    function regenerateCSRFToken() {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
} 