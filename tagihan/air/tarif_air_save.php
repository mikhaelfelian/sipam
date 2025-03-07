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

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid request");
}

try {
    // Validate required fields
    $required_fields = ['range_pemakaian', 'biaya_m3', 'biaya_mtc', 'biaya_adm'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Field $field is required");
        }
    }
    
    // Sanitize and validate input
    $range_pemakaian = filter_var($_POST['range_pemakaian'], FILTER_SANITIZE_STRING);
    $biaya_m3 = filter_var($_POST['biaya_m3'], FILTER_VALIDATE_FLOAT);
    $biaya_mtc = filter_var($_POST['biaya_mtc'], FILTER_VALIDATE_FLOAT);
    $biaya_adm = filter_var($_POST['biaya_adm'], FILTER_VALIDATE_FLOAT);
    
    // Validate range format
    if (!preg_match('/^\d+-\d+\s*m³$/', $range_pemakaian)) {
        throw new Exception("Format range pemakaian tidak valid. Contoh: 0-10 m³");
    }
    
    // Extract and validate range numbers
    $range_parts = explode('-', str_replace(' m³', '', $range_pemakaian));
    if (count($range_parts) !== 2) {
        throw new Exception("Format range pemakaian tidak valid");
    }

    $start = (int)$range_parts[0];
    $end = (int)$range_parts[1];

    if ($start >= $end) {
        throw new Exception("Angka awal harus lebih kecil dari angka akhir");
    }

    if ($start < 0 || $end < 0) {
        throw new Exception("Range pemakaian tidak boleh negatif");
    }
    
    // Validate amounts
    if ($biaya_m3 === false || $biaya_m3 < 0) {
        throw new Exception("Biaya per m³ tidak valid");
    }
    if ($biaya_mtc === false || $biaya_mtc < 0) {
        throw new Exception("Biaya maintenance tidak valid");
    }
    if ($biaya_adm === false || $biaya_adm < 0) {
        throw new Exception("Biaya admin tidak valid");
    }
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    // Check if range already exists
    $stmt = mysqli_prepare($conn, "SELECT id FROM tbl_m_air_tarif WHERE range_pemakaian = ?");
    mysqli_stmt_bind_param($stmt, "s", $range_pemakaian);
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_fetch($stmt)) {
        throw new Exception("Range pemakaian sudah ada dalam sistem");
    }
    
    // Insert tarif
    $stmt = mysqli_prepare($conn, "INSERT INTO tbl_m_air_tarif (range_pemakaian, biaya_m3, biaya_mtc, biaya_adm) 
                                  VALUES (?, ?, ?, ?)");
    
    mysqli_stmt_bind_param($stmt, "sddd", 
        $range_pemakaian,
        $biaya_m3,
        $biaya_mtc,
        $biaya_adm
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_error($conn));
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    setFlashMessage('success', "Data tarif air berhasil ditambahkan");
    header("Location: {$base_url}tagihan/air/tarif_air.php");
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    
    setFlashMessage('danger', $e->getMessage());
    header("Location: {$base_url}tagihan/air/tarif_air_add.php");
    exit();
}