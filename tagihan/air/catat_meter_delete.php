<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin']); // Only superadmin can delete

if (!isset($_POST['id']) || !isset($_POST['reason'])) {
    setFlashMessage('danger', 'Data tidak lengkap');
    header("Location: {$base_url}tagihan/air/catat_meter.php");
    exit();
}

$id = (int)$_POST['id'];
$reason = trim($_POST['reason']);

if (empty($reason)) {
    setFlashMessage('danger', 'Alasan hapus harus diisi');
    header("Location: {$base_url}tagihan/air/catat_meter.php");
    exit();
}

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // Check if transaction exists and get the data for logging
    $check_query = "SELECT t.*, w.nama, w.blok 
                   FROM tbl_trx_air t 
                   JOIN tbl_m_warga w ON t.id_warga = w.id 
                   WHERE t.id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        throw new Exception("Data transaksi tidak ditemukan");
    }

    $old_data = mysqli_fetch_assoc($result);
    $old_data_json = json_encode($old_data);

    // Check if transaction has been paid
    $check_payment = "SELECT COUNT(*) as paid_count 
                     FROM tbl_trx_air_pembayaran 
                     WHERE id_trx_air = ?";
    $stmt = mysqli_prepare($conn, $check_payment);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if ($row['paid_count'] > 0) {
        throw new Exception("Transaksi tidak dapat dihapus karena sudah dibayar");
    }

    // Insert into log table
    $log_query = "INSERT INTO tbl_log_delete (table_name, record_id, old_data, reason, deleted_by) 
                  VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $log_query);
    $table_name = 'tbl_trx_air';
    $deleted_by = $_SESSION['user']['id'];
    mysqli_stmt_bind_param($stmt, "sissi", $table_name, $id, $old_data_json, $reason, $deleted_by);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menyimpan log: " . mysqli_error($conn));
    }

    // Delete transaction
    $delete_query = "DELETE FROM tbl_trx_air WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menghapus transaksi: " . mysqli_error($conn));
    }

    // Commit transaction
    mysqli_commit($conn);
    
    setFlashMessage('success', 'Data pencatatan meter berhasil dihapus');

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    setFlashMessage('danger', $e->getMessage());
}

header("Location: {$base_url}tagihan/air/catat_meter.php");
exit();