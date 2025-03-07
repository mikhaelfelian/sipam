<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin']);

$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT id, nik, nama, blok 
          FROM tbl_m_warga 
          WHERE (nik LIKE ? OR nama LIKE ?) 
          AND id NOT IN (
              SELECT id_warga FROM tbl_users WHERE id_warga IS NOT NULL
          )
          ORDER BY nama 
          LIMIT 10";

$stmt = mysqli_prepare($conn, $query);
$search_term = "%$search%";
mysqli_stmt_bind_param($stmt, "ss", $search_term, $search_term);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = array(
        'id' => $row['id'],
        'nik' => $row['nik'],
        'nama' => $row['nama'],
        'blok' => $row['blok']
    );
}

header('Content-Type: application/json');
echo json_encode($data); 