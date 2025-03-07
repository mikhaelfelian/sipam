<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();

// Check if user is logged in
checkLogin();

// Only superadmin can delete users
checkRole(['superadmin']);

if (!isset($_GET['id'])) {
    setFlashMessage('danger', 'ID pengguna tidak ditemukan');
    header("Location: {$base_url}admin/users.php");
    exit();
}

$user_id = (int)$_GET['id'];

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // Check if user exists and is not the superadmin
    $stmt = mysqli_prepare($conn, "SELECT username, role FROM tbl_users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        throw new Exception("Pengguna tidak ditemukan");
    }

    // Prevent deletion of superadmin account
    if ($user['username'] === 'admin') {
        throw new Exception("Tidak dapat menghapus akun superadmin");
    }

    // Delete user
    $stmt = mysqli_prepare($conn, "DELETE FROM tbl_users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menghapus pengguna");
    }

    // Commit transaction
    mysqli_commit($conn);
    
    setFlashMessage('success', "Pengguna berhasil dihapus");
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    setFlashMessage('danger', $e->getMessage());
}

header("Location: {$base_url}admin/users.php");
exit();