<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

if (!isset($_GET['id'])) {
    setFlashMessage('danger', 'ID tarif tidak ditemukan');
    header("Location: {$base_url}tagihan/air/tarif_air.php");
    exit();
}

$tarif_id = (int)$_GET['id'];

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // Check if tarif exists
    $stmt = mysqli_prepare($conn, "SELECT range_pemakaian FROM tbl_m_air_tarif WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $tarif_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $tarif = mysqli_fetch_assoc($result);

    if (!$tarif) {
        throw new Exception("Data tarif tidak ditemukan");
    }

    // Check if tarif is being used in transactions
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as used_count FROM tbl_trx_air WHERE id_tarif = ?");
    mysqli_stmt_bind_param($stmt, "i", $tarif_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $usage = mysqli_fetch_assoc($result);

    if ($usage['used_count'] > 0) {
        throw new Exception("Tarif ini tidak dapat dihapus karena sudah digunakan dalam transaksi");
    }

    // Delete tarif
    $stmt = mysqli_prepare($conn, "DELETE FROM tbl_m_air_tarif WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $tarif_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menghapus tarif");
    }

    // Commit transaction
    mysqli_commit($conn);
    
    setFlashMessage('success', "Tarif air berhasil dihapus");
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    setFlashMessage('danger', $e->getMessage());
}

header("Location: {$base_url}tagihan/air/tarif_air.php");
exit();