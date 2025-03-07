<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';
require_once '../config/security.php';

startSession();

// Check if user is logged in
checkLogin();

// Only superadmin can update warga
checkRole(['superadmin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$base_url}master/warga.php");
    exit();
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('danger', 'Invalid CSRF token');
    header("Location: {$base_url}master/warga.php");
    exit();
}

// Use the same convertDate function as in warga_save.php
function convertDate($date) {
    if (empty($date)) return null;
    $parts = explode('/', $date);
    if (count($parts) === 3) {
        return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
    }
    return null;
}

try {
    // Update required fields to use tgl_masuk instead of tanggal_masuk
    $required_fields = ['id', 'kk', 'nik', 'nama', 'alamat', 'blok', 'status_rumah', 'tgl_masuk'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Field $field is required");
        }
    }
    
    // Sanitize and validate input
    $id = (int)$_POST['id'];
    $kk = preg_replace("/[^0-9]/", "", $_POST['kk']);
    $nik = preg_replace("/[^0-9]/", "", $_POST['nik']);
    $nama = filter_var($_POST['nama'], FILTER_SANITIZE_STRING);
    $alamat = filter_var($_POST['alamat'], FILTER_SANITIZE_STRING);
    $blok = filter_var($_POST['blok'], FILTER_SANITIZE_STRING);
    $status_rumah = filter_var($_POST['status_rumah'], FILTER_VALIDATE_INT);
    $no_hp = preg_replace("/[^0-9]/", "", $_POST['no_hp']);
    if (!empty($no_hp)) {
        // Remove leading zero if exists
        $no_hp = ltrim($no_hp, '0');
        // Validate length
        if (strlen($no_hp) < 10 || strlen($no_hp) > 13) {
            throw new Exception("Nomor WhatsApp harus antara 10-13 digit");
        }
    }
    
    // Convert the dates
    $tgl_masuk = convertDate($_POST['tgl_masuk']);
    if (!$tgl_masuk) {
        throw new Exception("Format tanggal masuk tidak valid");
    }
    
    $tgl_keluar = null;
    if ($_POST['status_rumah'] == '2' && !empty($_POST['tgl_keluar'])) {
        $tgl_keluar = convertDate($_POST['tgl_keluar']);
        if (!$tgl_keluar) {
            throw new Exception("Format tanggal keluar tidak valid");
        }
    }
    
    // Validate KK and NIK length
    if (strlen($kk) < 10 || strlen($kk) > 16) {
        throw new Exception("KK harus antara 10-16 digit");
    }
    if (strlen($nik) < 10 || strlen($nik) > 16) {
        throw new Exception("NIK harus antara 10-16 digit");
    }
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    // Check if KK exists for other warga
    $stmt = mysqli_prepare($conn, "SELECT id FROM tbl_m_warga WHERE kk = ? AND id != ?");
    mysqli_stmt_bind_param($stmt, "si", $kk, $id);
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_fetch($stmt)) {
        throw new Exception("Nomor KK sudah terdaftar dalam sistem");
    }
    
    // Update warga data with tanggal_masuk
    $tanggal_berakhir_kontrak = null;
    if ($_POST['status_rumah'] == '2' && !empty($_POST['tanggal_berakhir_kontrak'])) {
        $tanggal_berakhir_kontrak = date('Y-m-d', strtotime($_POST['tanggal_berakhir_kontrak']));
    }
    
    $stmt = mysqli_prepare($conn, "UPDATE tbl_m_warga SET 
        kk = ?, 
        nik = ?, 
        nama = ?, 
        alamat = ?, 
        blok = ?, 
        status_rumah = ?,
        tgl_masuk = ?,
        tgl_keluar = ?,
        no_hp = ?
        WHERE id = ?");
    
    mysqli_stmt_bind_param($stmt, "sssssssssi", 
        $kk, 
        $nik, 
        $nama, 
        $alamat, 
        $blok, 
        $status_rumah,
        $tgl_masuk,
        $tgl_keluar,
        $no_hp,
        $id
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal mengupdate data: " . mysqli_error($conn));
    }

    // Update associated user's username if exists (since username is NIK)
    $stmt = mysqli_prepare($conn, "UPDATE tbl_users SET username = ? WHERE id_warga = ? AND role = 'warga'");
    mysqli_stmt_bind_param($stmt, "si", $nik, $id);
    mysqli_stmt_execute($stmt);
    
    // Commit transaction
    mysqli_commit($conn);
    
    setFlashMessage('success', "Data warga berhasil diupdate");
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    setFlashMessage('danger', $e->getMessage());
}

header("Location: {$base_url}master/warga.php");
exit();
?>