<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();

// Check if user is logged in
checkLogin();

// Only superadmin and pengurus can access this page
checkRole(['superadmin', 'pengurus']);

if (!isset($_GET['id'])) {
    setFlashMessage('danger', 'ID Warga tidak ditemukan');
    header("Location: {$base_url}master/warga.php");
    exit();
}

$warga_id = (int)$_GET['id'];

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // Get warga data
    $stmt = mysqli_prepare($conn, "SELECT nik, nama FROM tbl_m_warga WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $warga_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $warga = mysqli_fetch_assoc($result);

    if (!$warga) {
        throw new Exception("Data warga tidak ditemukan");
    }

    // Check if user already exists
    $stmt = mysqli_prepare($conn, "SELECT id FROM tbl_users WHERE id_warga = ?");
    mysqli_stmt_bind_param($stmt, "i", $warga_id);
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_fetch($stmt)) {
        throw new Exception("User untuk warga ini sudah ada");
    }

    // Create user account
    $username = $warga['nik'];
    $password = password_hash('password', PASSWORD_DEFAULT);
    $role = 'warga';

    // Check if username (NIK) already exists
    $stmt = mysqli_prepare($conn, "SELECT id FROM tbl_users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_fetch($stmt)) {
        throw new Exception("NIK sudah digunakan sebagai username di sistem");
    }

    // Insert user
    $stmt = mysqli_prepare($conn, "INSERT INTO tbl_users (username, password, role, id_warga) 
                                  VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssi", $username, $password, $role, $warga_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_error($conn));
    }

    // Commit transaction
    mysqli_commit($conn);
    
    setFlashMessage('success', "Akun user berhasil dibuat untuk {$warga['nama']}. Username: {$warga['nik']}, Password: password");
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    setFlashMessage('danger', $e->getMessage());
}

header("Location: {$base_url}master/warga.php");
exit();