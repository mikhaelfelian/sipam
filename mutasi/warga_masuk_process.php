<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize inputs
        $tgl_masuk = date('Y-m-d', strtotime(str_replace('/', '-', $_POST['tgl_masuk'])));
        $no_kk = filter_var($_POST['no_kk'], FILTER_SANITIZE_STRING);
        $nik = filter_var($_POST['nik'], FILTER_SANITIZE_STRING);
        $nama = filter_var($_POST['nama'], FILTER_SANITIZE_STRING);
        $blok = filter_var($_POST['blok'], FILTER_SANITIZE_STRING);
        $alamat_asal = filter_var($_POST['alamat_asal'], FILTER_SANITIZE_STRING);
        $keterangan = filter_var($_POST['keterangan'], FILTER_SANITIZE_STRING);
        $status_rumah = filter_var($_POST['status_rumah'], FILTER_SANITIZE_STRING);

        // Validate required fields
        if (empty($tgl_masuk) || empty($no_kk) || empty($nik) || empty($nama) || empty($blok) || empty($alamat_asal)) {
            throw new Exception('Semua field harus diisi kecuali keterangan');
        }

        // Validate NIK and KK length
        if (strlen($nik) !== 16 || strlen($no_kk) !== 16) {
            throw new Exception('NIK dan No KK harus 16 digit');
        }

        // Begin transaction
        mysqli_begin_transaction($conn);

        // Insert into tbl_trx_mutasi_warga
        $query = "INSERT INTO tbl_trx_mutasi_warga (tgl_masuk, no_kk, nik, nama, blok, 
                  alamat_asal, keterangan, status_rumah) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssssss", 
            $tgl_masuk,
            $no_kk,
            $nik,
            $nama,
            $blok,
            $alamat_asal,
            $keterangan,
            $status_rumah
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Gagal menyimpan data mutasi: ' . mysqli_error($conn));
        }

        $id_mutasi = mysqli_insert_id($conn);

        // Commit transaction
        mysqli_commit($conn);
        
        setFlashMessage('success', 'Data mutasi masuk berhasil disimpan');
        header("Location: warga_masuk_berkas.php?id=" . $id_mutasi);
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        setFlashMessage('danger', $e->getMessage());
        header("Location: warga_masuk_add.php");
        exit();
    }
} else {
    header("Location: warga_masuk.php");
    exit();
} 