<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin']);

try {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

    if (!$id || empty($reason)) {
        throw new Exception('Data tidak lengkap');
    }

    // Check if bill is already paid
    $check_query = "SELECT status_bayar FROM tbl_trx_air WHERE id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $tagihan = mysqli_fetch_assoc($result);

    if (!$tagihan) {
        throw new Exception('Data tagihan tidak ditemukan');
    }

    if ($tagihan['status_bayar'] == 1) {
        throw new Exception('Tagihan yang sudah lunas tidak dapat dihapus');
    }

    // Delete the bill
    $query = "DELETE FROM tbl_trx_air WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Gagal menghapus data: ' . mysqli_error($conn));
    }

    // Log the deletion
    $user_id = $_SESSION['user']['id'];
    $log_query = "INSERT INTO tbl_log_delete (table_name, record_id, reason, deleted_by, deleted_at) 
                  VALUES ('tbl_trx_air', ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $log_query);
    mysqli_stmt_bind_param($stmt, "isi", $id, $reason, $user_id);
    mysqli_stmt_execute($stmt);

    setFlashMessage('success', 'Data tagihan berhasil dihapus');

} catch (Exception $e) {
    setFlashMessage('danger', $e->getMessage());
}

header("Location: tagihan.php");
exit();
?>