<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debug POST data
    error_log("POST Data: " . print_r($_POST, true));

    $id_trx = $_POST['id_trx'];
    $jumlah_bayar = str_replace('.', '', $_POST['jumlah_bayar']);
    $platform = $_POST['platform'];
    $total_tagihan = str_replace('.', '', $_POST['total_tagihan']);
    $sisa_tagihan = $total_tagihan - $jumlah_bayar;

    // Debug values
    error_log("ID Trx: " . $id_trx);
    error_log("Jumlah Bayar: " . $jumlah_bayar);
    error_log("Platform: " . $platform);
    error_log("Total Tagihan: " . $total_tagihan);
    error_log("Sisa Tagihan: " . $sisa_tagihan);

    try {
        mysqli_begin_transaction($conn);

        // Debug the values before insert
        error_log("Before Insert - ID TRX: {$id_trx}, Jumlah Bayar: {$jumlah_bayar}, Platform: {$platform}");

        // Insert payment record with explicit field list
        $query = "INSERT INTO tbl_trx_air_pembayaran 
                  (id_trx_air, jumlah_bayar, id_platform, platform, created_at, created_by) 
                  VALUES (?, ?, ?, (SELECT platform FROM tbl_m_platform WHERE id = ?), NOW(), ?)";
        error_log("Query 1: " . $query);
        
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . mysqli_error($conn));
        }

        $user_id = $_SESSION['user']['id']; // Get logged in user ID
        mysqli_stmt_bind_param($stmt, "idiis", $id_trx, $jumlah_bayar, $platform, $platform, $user_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Execute failed: ' . mysqli_stmt_error($stmt) . ' - ' . mysqli_error($conn));
        }

        // Update transaction status
        $status_bayar = ($sisa_tagihan <= 0) ? 1 : 2; // 1 = Lunas, 2 = Kurang Bayar
        $query = "UPDATE tbl_trx_air SET status_bayar = ? WHERE id = ?";
        error_log("Query 2: " . $query);
        
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "ii", $status_bayar, $id_trx);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Execute failed: ' . mysqli_stmt_error($stmt));
        }

        // If payment is complete (Lunas), save to tbl_akt_kas
        if ($status_bayar == 1) {
            // Get warga info and period
            $query = "SELECT w.nama, t.bulan, t.tahun 
                     FROM tbl_trx_air t 
                     JOIN tbl_m_warga w ON t.id_warga = w.id 
                     WHERE t.id = ?";
            error_log("Query 3: " . $query);
            
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "i", $id_trx);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Execute failed: ' . mysqli_stmt_error($stmt));
            }

            $result = mysqli_stmt_get_result($stmt);
            $data = mysqli_fetch_assoc($result);

            error_log("Warga Data: " . print_r($data, true));

            // Format period name
            $bulan_array = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $periode = $bulan_array[$data['bulan']] . ' ' . $data['tahun'];

            // Insert into tbl_akt_kas
            $jenis = "Pembayaran Air " . $data['nama'] . " Periode " . $periode;
            $query = "INSERT INTO tbl_akt_kas (tgl_masuk, jenis, keterangan, status_kas, tipe, nominal) 
                     VALUES (CURRENT_DATE(), ?, ?, '1', 1, ?)";
            error_log("Query 4: " . $query);
            
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "ssd", $jenis, $jenis, $total_tagihan);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Execute failed: ' . mysqli_stmt_error($stmt));
            }
        }

        mysqli_commit($conn);
        setFlashMessage('success', 'Pembayaran berhasil disimpan!');
    } catch (Exception $e) {
        mysqli_rollback($conn);
        setFlashMessage('error', $e->getMessage());
        error_log("Error in bayar_save.php: " . $e->getMessage());
    }
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();