<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/auth.php';

session_start();
checkLogin();

$id_warga = isset($_POST['id_warga']) ? (int)$_POST['id_warga'] : 0;

try {
    // Get the last meter reading for this warga
    $query = "SELECT meter_akhir as last_meter 
              FROM tbl_trx_air 
              WHERE id_warga = ? 
              ORDER BY tahun DESC, bulan DESC, id DESC 
              LIMIT 1";
              
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception('Query preparation failed');
    }

    mysqli_stmt_bind_param($stmt, "i", $id_warga);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Query execution failed');
    }

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    header('Content-Type: application/json');
    echo json_encode([
        'last_meter' => $data ? floatval($data['last_meter']) : 0,
        'success' => true
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 