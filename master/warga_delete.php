<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();

// Check if user is logged in
checkLogin();

// Only superadmin and pengurus can delete warga
checkRole(['superadmin', 'pengurus']);

if (!isset($_GET['id'])) {
    setFlashMessage('danger', 'ID warga tidak ditemukan');
    header("Location: {$base_url}master/warga.php");
    exit();
}

$warga_id = (int)$_GET['id'];

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // Check if warga exists
    $stmt = mysqli_prepare($conn, "SELECT nama FROM tbl_m_warga WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $warga_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $warga = mysqli_fetch_assoc($result);

    if (!$warga) {
        throw new Exception("Data warga tidak ditemukan");
    }

    // Delete associated user account if exists
    $stmt = mysqli_prepare($conn, "DELETE FROM tbl_users WHERE id_warga = ?");
    mysqli_stmt_bind_param($stmt, "i", $warga_id);
    mysqli_stmt_execute($stmt);

    // Delete warga
    $stmt = mysqli_prepare($conn, "DELETE FROM tbl_m_warga WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $warga_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menghapus data warga");
    }

    // Commit transaction
    mysqli_commit($conn);
    
    setFlashMessage('success', "Data warga berhasil dihapus");
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    setFlashMessage('danger', $e->getMessage());
}

header("Location: {$base_url}master/warga.php");
exit();