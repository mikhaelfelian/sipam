<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal = $_POST['tanggal'];
    $catatan = $_POST['catatan'];
    $nominal = str_replace('.', '', $_POST['nominal']); // Remove thousand separator

    try {
        // Begin transaction
        mysqli_begin_transaction($conn);

        // For debugging
        error_log("Tanggal: " . $tanggal);
        error_log("Catatan: " . $catatan);
        error_log("Nominal: " . $nominal);

        // Insert into tbl_akt_kas with tipe=1 for Pengeluaran Air
        $query = "INSERT INTO tbl_akt_kas (tgl_masuk, jenis, keterangan, status_kas, tipe, nominal) 
                  VALUES (?, 'Pengeluaran Air', ?, '2', 1, ?)";
        
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . mysqli_error($conn));
        }

        if (!mysqli_stmt_bind_param($stmt, "ssd", $tanggal, $catatan, $nominal)) {
            throw new Exception('Binding parameters failed: ' . mysqli_stmt_error($stmt));
        }
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Execute failed: ' . mysqli_stmt_error($stmt));
        }

        // Commit transaction
        mysqli_commit($conn);
        setFlashMessage('success', 'Data pengeluaran berhasil ditambahkan!');

    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($conn);
        setFlashMessage('error', $e->getMessage());
        
        // For debugging
        error_log("Error in pengeluaran_save.php: " . $e->getMessage());
    }
}

// For debugging
if (isset($_POST)) {
    error_log("POST data: " . print_r($_POST, true));
}

header("Location: pengeluaran.php");
exit();
?>