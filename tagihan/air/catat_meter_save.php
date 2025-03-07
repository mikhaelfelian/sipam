<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';

startSession();
checkLogin();

try {
    // Get and validate input
    $id_warga = isset($_POST['id_warga']) ? (int)$_POST['id_warga'] : 0;
    $meter_awal = isset($_POST['meter_awal']) ? (float)$_POST['meter_awal'] : 0;
    $meter_akhir = isset($_POST['meter_akhir']) ? (float)$_POST['meter_akhir'] : 0;
    $bulan = isset($_POST['bulan']) ? $_POST['bulan'] : '';
    $tahun = isset($_POST['tahun']) ? $_POST['tahun'] : '';

    // Validate meter readings
    if ($meter_akhir < $meter_awal) {
        throw new Exception('Meter saat ini tidak boleh lebih kecil dari meter sebelumnya');
    }

    // Validate access
    if ($_SESSION['user']['role'] === 'warga' && $_SESSION['user']['id_warga'] != $id_warga) {
        throw new Exception('Anda hanya dapat mencatat meter untuk diri sendiri');
    }

    // Begin transaction
    mysqli_begin_transaction($conn);

    // Check if reading already exists for this period
    $stmt = mysqli_prepare($conn, "SELECT id FROM tbl_trx_air WHERE id_warga = ? AND bulan = ? AND tahun = ?");
    mysqli_stmt_bind_param($stmt, "iss", $id_warga, $bulan, $tahun);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        throw new Exception('Data meter untuk periode ini sudah ada');
    }

    // Calculate pemakaian
    $pemakaian = $meter_akhir - $meter_awal;

    // Insert meter reading
    $query = "INSERT INTO tbl_trx_air (id_warga, meter_awal, meter_akhir, pemakaian, bulan, tahun, created_by) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "idddiss", 
        $id_warga, 
        $meter_awal, 
        $meter_akhir, 
        $pemakaian,
        $bulan,
        $tahun,
        $_SESSION['user']['id']
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Gagal menyimpan data meter: ' . mysqli_error($conn));
    }

    mysqli_commit($conn);
    setFlashMessage('success', 'Data meter berhasil disimpan');

} catch (Exception $e) {
    mysqli_rollback($conn);
    setFlashMessage('danger', $e->getMessage());
    header("Location: catat_meter.php");
    exit();
}

header("Location: catat_meter.php");
exit();