<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

if (!isset($_GET['id'])) {
    setFlashMessage('danger', 'ID platform tidak ditemukan');
    header("Location: {$base_url}master/platform.php");
    exit();
}

$id = (int)$_GET['id'];

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // Check if platform exists
    $check_query = "SELECT platform FROM tbl_m_platform WHERE id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        throw new Exception("Platform tidak ditemukan");
    }

    // Check if platform is being used in transactions
    $check_usage = "SELECT COUNT(*) as used_count FROM tbl_trx_air_pembayaran WHERE id_platform = ?";
    $stmt = mysqli_prepare($conn, $check_usage);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if ($row['used_count'] > 0) {
        throw new Exception("Platform tidak dapat dihapus karena sudah digunakan dalam transaksi");
    }

    // Delete platform
    $delete_query = "DELETE FROM tbl_m_platform WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menghapus platform: " . mysqli_error($conn));
    }

    // Commit transaction
    mysqli_commit($conn);
    
    setFlashMessage('success', 'Platform berhasil dihapus');

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    setFlashMessage('danger', $e->getMessage());
}

header("Location: {$base_url}master/platform.php");
exit();