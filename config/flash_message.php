<?php
// Only start session if it hasn't been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function setFlashMessage($type, $message) {
    if (!isset($_SESSION['flash_message'])) {
        $_SESSION['flash_message'] = [];
    }
    $_SESSION['flash_message'] = [
        'type' => $type,
        'text' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Function to check if KK number already exists
function isKKExists($conn, $kk, $exclude_id = null) {
    $query = "SELECT id FROM tbl_m_warga WHERE kk = ?";
    $params = [$kk];
    
    if ($exclude_id) {
        $query .= " AND id != ?";
        $params[] = $exclude_id;
    }
    
    $stmt = mysqli_prepare($conn, $query);
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_num_rows($result) > 0;
} 