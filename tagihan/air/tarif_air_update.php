<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$base_url}tagihan/air/tarif_air.php");
    exit();
}

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // Get and validate ID
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception("ID tarif tidak ditemukan");
    }
    $id = (int)$_POST['id'];

    // Get values from hidden fields that contain unformatted numbers
    $biaya_m3 = (float)$_POST['biaya_m3_hidden'];
    $biaya_mtc = (float)$_POST['biaya_mtc_hidden'];
    $biaya_adm = (float)$_POST['biaya_adm_hidden'];
    $range_pemakaian = mysqli_real_escape_string($conn, $_POST['range_pemakaian']);
    
    // Validate amounts
    if ($biaya_m3 < 0) {
        throw new Exception("Biaya per m³ tidak valid");
    }
    if ($biaya_mtc < 0) {
        throw new Exception("Biaya maintenance tidak valid");
    }
    if ($biaya_adm < 0) {
        throw new Exception("Biaya admin tidak valid");
    }

    // Check if tarif exists
    $check_query = "SELECT id FROM tbl_m_air_tarif WHERE id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        throw new Exception("Tarif tidak ditemukan");
    }

    // Update tarif
    $update_query = "UPDATE tbl_m_air_tarif 
                    SET range_pemakaian = ?, 
                        biaya_m3 = ?, 
                        biaya_mtc = ?, 
                        biaya_adm = ? 
                    WHERE id = ?";
                    
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "sdddi", 
        $range_pemakaian,
        $biaya_m3,
        $biaya_mtc,
        $biaya_adm,
        $id
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal mengupdate tarif: " . mysqli_error($conn));
    }

    // Commit transaction
    mysqli_commit($conn);
    
    setFlashMessage('success', "Tarif air berhasil diupdate");
    header("Location: {$base_url}tagihan/air/tarif_air.php");
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    
    setFlashMessage('danger', $e->getMessage());
    header("Location: {$base_url}tagihan/air/tarif_air_edit.php?id=" . $_POST['id']);
    exit();
}