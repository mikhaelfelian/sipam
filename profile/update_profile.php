<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();

// Check if user is logged in
checkLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$base_url}profile/index.php");
    exit();
}

try {
    // Validate input
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        throw new Exception("Semua field harus diisi");
    }

    if ($new_password !== $confirm_password) {
        throw new Exception("Password baru dan konfirmasi tidak cocok");
    }

    // Get current user data
    $user_id = $_SESSION['user']['id'];
    $stmt = mysqli_prepare($conn, "SELECT password FROM tbl_users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        throw new Exception("Password saat ini tidak valid");
    }

    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "UPDATE tbl_users SET password = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal mengupdate password");
    }

    setFlashMessage('success', "Password berhasil diupdate");
    
} catch (Exception $e) {
    setFlashMessage('danger', $e->getMessage());
}

header("Location: {$base_url}profile/index.php");
exit();
 