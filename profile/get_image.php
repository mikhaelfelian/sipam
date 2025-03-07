<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/auth.php';

startSession();
checkLogin();

if (!isset($_GET['filename'])) {
    // Return default image if no filename provided
    header('Content-Type: image/jpeg');
    readfile('../assets/theme/admin-lte-3/dist/img/user2-160x160.jpg');
    exit;
}

// Sanitize filename
$filename = basename($_GET['filename']);

// Check if file exists in database
$query = "SELECT profile_picture FROM tbl_users WHERE profile_picture = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $filename);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $filepath = '../assets/images/profile/' . $filename;
    
    if (file_exists($filepath)) {
        // Get file mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $filepath);
        finfo_close($finfo);
        
        // Set proper headers
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: private, max-age=3600');
        
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Output file
        readfile($filepath);
        exit;
    }
}

// If file not found, return default image
header('Content-Type: image/jpeg');
readfile('../assets/theme/admin-lte-3/dist/img/user2-160x160.jpg');
 