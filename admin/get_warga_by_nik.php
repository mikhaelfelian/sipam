<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin']);

if (!isset($_GET['nik'])) {
    echo json_encode(['error' => 'NIK not provided']);
    exit;
}

$nik = $_GET['nik'];
$query = "SELECT id FROM tbl_m_warga WHERE nik = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $nik);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$warga = mysqli_fetch_assoc($result);

header('Content-Type: application/json');
echo json_encode($warga ?: ['error' => 'Warga not found']); 