<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_mutasi = isset($_GET['id_mutasi']) ? (int)$_GET['id_mutasi'] : 0;

try {
    // Get file info
    $query = "SELECT file, status FROM tbl_trx_mutasi_warga_file WHERE id = ? AND id_mutasi = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $id, $id_mutasi);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $file = mysqli_fetch_assoc($result);

    if (!$file) {
        throw new Exception('File tidak ditemukan');
    }

    if ($file['status'] != 0) {
        throw new Exception('File yang sudah diverifikasi tidak dapat dihapus');
    }

    // Delete file from storage
    $file_path = '../uploads/mutasi/' . $file['file'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    // Delete record from database
    $query = "DELETE FROM tbl_trx_mutasi_warga_file WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Gagal menghapus file');
    }

    setFlashMessage('success', 'File berhasil dihapus');

} catch (Exception $e) {
    setFlashMessage('danger', $e->getMessage());
}

header("Location: warga_masuk_berkas.php?id=$id_mutasi");
exit();