<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

if (isset($_GET['id'])) {
    // Get specific warga by ID
    $id = (int)$_GET['id'];
    $query = "SELECT DISTINCT w.id, w.nama, w.blok 
              FROM tbl_m_warga w 
              JOIN tbl_trx_air t ON w.id = t.id_warga 
              WHERE w.id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
} else {
    // Search warga
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $query = "SELECT DISTINCT w.id, w.nama, w.blok 
              FROM tbl_m_warga w 
              JOIN tbl_trx_air t ON w.id = t.id_warga 
              WHERE w.nama LIKE ? 
              ORDER BY w.nama 
              LIMIT 10";
    $stmt = mysqli_prepare($conn, $query);
    $search_term = "%$search%";
    mysqli_stmt_bind_param($stmt, "s", $search_term);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = array(
        'id' => $row['id'],
        'nama' => $row['nama'],
        'blok' => $row['blok']
    );
}

header('Content-Type: application/json');
echo json_encode($data); 