<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();

// Check if user is logged in
checkLogin();

// Only superadmin and pengurus can access this page
checkRole(['superadmin', 'pengurus']);

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$base_url}master/warga.php");
    exit();
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid request");
}

// Convert date format from dd/mm/yyyy to yyyy-mm-dd
function convertDate($date) {
    if (empty($date)) return null;
    $parts = explode('/', $date);
    if (count($parts) === 3) {
        return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
    }
    return null;
}

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // Validate required fields
    $required_fields = ['kk', 'nik', 'nama', 'alamat', 'blok', 'status_rumah'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Field $field is required");
        }
    }
    
    // Validate and sanitize input
    $kk = preg_replace("/[^0-9]/", "", $_POST['kk']);
    $nik = preg_replace("/[^0-9]/", "", $_POST['nik']);
    $nama = filter_var($_POST['nama'], FILTER_SANITIZE_STRING);
    $alamat = filter_var($_POST['alamat'], FILTER_SANITIZE_STRING);
    $blok = filter_var($_POST['blok'], FILTER_SANITIZE_STRING);
    $status_rumah = filter_var($_POST['status_rumah'], FILTER_VALIDATE_INT);
    
    // Validate KK length
    if (strlen($kk) < 10 || strlen($kk) > 16) {
        throw new Exception("KK harus antara 10-16 digit");
    }
    
    // Check for duplicate KK
    if (isKKExists($conn, $kk)) {
        throw new Exception("Nomor KK sudah terdaftar dalam sistem");
    }
    
    // Rest of validation
    if (strlen($nik) < 10 || strlen($nik) > 16) {
        throw new Exception("NIK harus antara 10-16 digit");
    }
    if ($status_rumah < 1 || $status_rumah > 2) {
        throw new Exception("Invalid status rumah");
    }
    
    // Convert the dates
    $tgl_masuk = convertDate($_POST['tgl_masuk']);
    $tgl_keluar = null;
    if ($_POST['status_rumah'] == '2' && !empty($_POST['tgl_keluar'])) {
        $tgl_keluar = convertDate($_POST['tgl_keluar']);
    }
    
    // Sanitize and validate input
    $no_hp = preg_replace("/[^0-9]/", "", $_POST['no_hp']);
    if (!empty($no_hp)) {
        // Remove leading zero if exists
        $no_hp = ltrim($no_hp, '0');
        // Validate length
        if (strlen($no_hp) < 10 || strlen($no_hp) > 13) {
            throw new Exception("Nomor WhatsApp harus antara 10-13 digit");
        }
    }
    
    // Insert warga
    $stmt = mysqli_prepare($conn, "INSERT INTO tbl_m_warga (kk, nik, nama, alamat, blok, status_rumah, tgl_masuk, tgl_keluar, no_hp) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    mysqli_stmt_bind_param($stmt, "sssssssss", 
        $kk, 
        $nik, 
        $nama, 
        $alamat, 
        $blok, 
        $status_rumah,
        $tgl_masuk,
        $tgl_keluar,
        $no_hp
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_error($conn));
    }

    // Get the inserted warga ID
    $warga_id = mysqli_insert_id($conn);

    // Create user account for the warga
    $username = $nik; // Use NIK as username
    $password = password_hash('password', PASSWORD_DEFAULT); // Default password: 'password'
    $role = 'warga';

    // Check if username (NIK) already exists in users table
    $stmt = mysqli_prepare($conn, "SELECT id FROM tbl_users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_fetch($stmt)) {
        throw new Exception("NIK sudah digunakan sebagai username di sistem");
    }

    // Insert user
    $stmt = mysqli_prepare($conn, "INSERT INTO tbl_users (username, password, role, id_warga) 
                                  VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssi", $username, $password, $role, $warga_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_error($conn));
    }

    // Commit transaction
    mysqli_commit($conn);
    
    setFlashMessage('success', "<strong>Berhasil!</strong> Data warga telah ditambahkan. Username: $nik, Password: password");
    header("Location: {$base_url}master/warga.php");
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    
    setFlashMessage('danger', "<strong>Error!</strong> " . $e->getMessage());
    header("Location: {$base_url}master/warga_add.php");
    exit();
} 