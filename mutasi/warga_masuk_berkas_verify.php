<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? (int)$_GET['status'] : 0;
$id_mutasi = isset($_GET['id_mutasi']) ? (int)$_GET['id_mutasi'] : 0;

try {
    // Verify file exists and belongs to mutasi
    $query = "SELECT id FROM tbl_trx_mutasi_warga_file WHERE id = ? AND id_mutasi = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $id, $id_mutasi);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!mysqli_fetch_assoc($result)) {
        throw new Exception('File tidak ditemukan');
    }

    // Update status
    $query = "UPDATE tbl_trx_mutasi_warga_file SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $status, $id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Gagal memperbarui status file');
    }

    $message = $status == 1 ? 'File berhasil diverifikasi' : 'Verifikasi file dibatalkan';
    setFlashMessage('success', $message);

} catch (Exception $e) {
    setFlashMessage('danger', $e->getMessage());
}

header("Location: warga_masuk_berkas.php?id=$id_mutasi");
exit();
?>