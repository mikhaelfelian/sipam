<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/auth.php';

session_start();
checkLogin();

// Get search term
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

try {
    // Simple query to get warga data
    $query = "SELECT id, nama, nik, blok 
              FROM tbl_m_warga 
              WHERE (
                  nama LIKE ? 
                  OR nik LIKE ? 
                  OR blok LIKE ?
              ) 
              AND status_warga = 1 
              ORDER BY nama ASC 
              LIMIT 20";

    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception('Query preparation failed');
    }

    $search_term = "%$search%";
    mysqli_stmt_bind_param($stmt, "sss", $search_term, $search_term, $search_term);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Query execution failed');
    }

    $result = mysqli_stmt_get_result($stmt);
    
    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = array(
            'id' => $row['id'],
            'text' => $row['nama'] . ' - Blok ' . $row['blok'] . ' (NIK: ' . $row['nik'] . ')'
        );
    }

    header('Content-Type: application/json');
    echo json_encode($data);

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}