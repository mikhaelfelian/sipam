<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $query = "DELETE FROM tbl_akt_kas WHERE id = ? AND status_kas = '2' AND jenis = 'Pengeluaran Air'";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            setFlashMessage('success', 'Data pengeluaran berhasil dihapus!');
        } else {
            throw new Exception('Gagal menghapus data.');
        }
    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }
}

header("Location: pengeluaran.php");
exit();
?>