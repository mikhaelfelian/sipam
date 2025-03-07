<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

$id_mutasi = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Add connection check at the start
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }
    
    // Start transaction
    mysqli_begin_transaction($conn);

    // Check if all required files are uploaded and verified
    $required_files = [1, 2, 3]; // 1=KK, 2=KTP Suami, 3=KTP Istri
    $query = "SELECT jenis_berkas, status 
              FROM tbl_trx_mutasi_warga_file 
              WHERE id_mutasi = ? AND status = 1";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_mutasi);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $verified_files = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $verified_files[] = $row['jenis_berkas'];
    }

    // Check if KK (1) and at least one KTP (2 or 3) are verified
    $has_kk = in_array(1, $verified_files);
    $has_ktp = in_array(2, $verified_files) || in_array(3, $verified_files);

    if (!$has_kk || !$has_ktp) {
        throw new Exception('Berkas wajib (KK dan minimal satu KTP) belum lengkap atau belum diverifikasi');
    }

    // Get mutasi data
    $query = "SELECT * FROM tbl_trx_mutasi_warga WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_mutasi);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $mutasi = mysqli_fetch_assoc($result);

    if (!$mutasi) {
        throw new Exception('Data mutasi tidak ditemukan');
    }

    // Insert into tbl_m_warga
    $query = "INSERT INTO tbl_m_warga (kk, nik, nama, blok, status_warga, tgl_masuk, status_rumah) 
              VALUES (?, ?, ?, ?, 1, ?, ?)";
    
    if (!$stmt = mysqli_prepare($conn, $query)) {
        throw new Exception('Prepare failed: ' . mysqli_error($conn));
    }
    
    if (!mysqli_stmt_bind_param($stmt, "ssssss", 
        $mutasi['no_kk'],
        $mutasi['nik'],
        $mutasi['nama'],
        $mutasi['blok'],
        $mutasi['tgl_masuk'],
        $mutasi['status_rumah']
    )) {
        throw new Exception('Bind param failed: ' . mysqli_stmt_error($stmt));
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Execute failed: (' . mysqli_stmt_errno($stmt) . ') ' . mysqli_stmt_error($stmt));
    }

    // Get the newly inserted warga ID
    $id_warga = mysqli_insert_id($conn);

    // Create user account in tbl_users
    $username = $mutasi['nik']; // Use NIK as username
    $password = password_hash($mutasi['nik'], PASSWORD_DEFAULT); // Use NIK as initial password
    $role = 'warga';

    $query = "INSERT INTO tbl_users (username, password, role, id_warga, created_at) 
              VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
    
    if (!$stmt = mysqli_prepare($conn, $query)) {
        throw new Exception('Prepare failed for user creation: ' . mysqli_error($conn));
    }
    
    if (!mysqli_stmt_bind_param($stmt, "sssi", $username, $password, $role, $id_warga)) {
        throw new Exception('Bind param failed for user creation: ' . mysqli_stmt_error($stmt));
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to create user account: ' . mysqli_stmt_error($stmt));
    }

    // Update tbl_trx_mutasi_warga status_berkas only
    $query = "UPDATE tbl_trx_mutasi_warga 
              SET status_berkas = '1' 
              WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_mutasi);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Gagal mengupdate status mutasi');
    }

    // Check if there are verified files to copy
    $check_query = "SELECT COUNT(*) as count FROM tbl_trx_mutasi_warga_file 
                    WHERE id_mutasi = ? AND status = 1";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $id_mutasi);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $count = mysqli_fetch_assoc($result)['count'];

    if ($count > 0) {
        // Copy verified files to tbl_m_warga_file
        $query = "INSERT INTO tbl_m_warga_file (id_warga, tgl_masuk, nama, file, tipe)
                  SELECT ?, tgl_masuk, nama, file, jenis_berkas
                  FROM tbl_trx_mutasi_warga_file
                  WHERE id_mutasi = ? AND status = 1";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $id_warga, $id_mutasi);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Gagal menyalin data berkas: ' . mysqli_error($conn));
        }
    }

    // Commit transaction
    mysqli_commit($conn);
    setFlashMessage('success', 'Data warga berhasil ditambahkan');

} catch (Exception $e) {
    mysqli_rollback($conn);
    setFlashMessage('danger', $e->getMessage());
}

header("Location: warga_masuk_berkas.php?id=$id_mutasi");
exit();
?>