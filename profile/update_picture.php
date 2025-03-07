<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';
require_once '../config/security.php';

startSession();

// Check if user is logged in
checkLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$base_url}profile/index.php");
    exit();
}

try {
    // Rate limiting for file uploads
    checkRateLimit('upload_' . $_SESSION['user']['id'], 3, 300);

    $file = null;
    $tempFile = null;

    // Handle camera capture
    if (!empty($_POST['image_data'])) {
        // Decode base64 image
        $imageData = $_POST['image_data'];
        $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        $imageData = base64_decode($imageData);

        // Create temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'profile_');
        file_put_contents($tempFile, $imageData);

        // Create file array similar to $_FILES
        $file = [
            'tmp_name' => $tempFile,
            'size' => strlen($imageData),
            'type' => 'image/jpeg',
            'name' => 'camera_capture.jpg',
            'error' => 0
        ];
    } 
    // Handle file upload
    elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
    } else {
        throw new Exception("Tidak ada gambar yang dipilih");
    }

    $allowed_types = ['image/jpeg', 'image/png'];
    $max_size = 2 * 1024 * 1024; // 2MB

    // Validate file using security helper
    validateFileUpload($file, $allowed_types, $max_size);

    // Create upload directory if it doesn't exist
    $upload_path = '../assets/images/profile/';
    if (!file_exists($upload_path)) {
        if (!mkdir($upload_path, 0777, true)) {
            throw new Exception("Gagal membuat direktori upload");
        }
    }

    // Generate secure filename
    $filename = generateSecureFilename($file['name'], 'profile_' . $_SESSION['user']['id']);
    $filepath = $upload_path . $filename;

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Get current profile picture
        $stmt = mysqli_prepare($conn, "SELECT profile_picture FROM tbl_users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['user']['id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        // Copy file to destination
        if ($tempFile) {
            // For camera capture
            if (!copy($tempFile, $filepath)) {
                throw new Exception("Gagal menyimpan foto");
            }
        } else {
            // For file upload
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception("Gagal mengupload file");
            }
        }

        // After successful file move/copy, ensure correct permissions
        chmod($filepath, 0644); // Read by owner, read by others

        // Update database
        $stmt = mysqli_prepare($conn, "UPDATE tbl_users SET profile_picture = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $filename, $_SESSION['user']['id']);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal mengupdate foto profil");
        }

        // Delete old profile picture if exists
        if (!empty($user['profile_picture'])) {
            $old_file = $upload_path . $user['profile_picture'];
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }

        // Commit transaction
        mysqli_commit($conn);
        setFlashMessage('success', "Foto profil berhasil diupdate");

    } catch (Exception $e) {
        // Rollback transaction and delete uploaded file if exists
        mysqli_rollback($conn);
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        throw $e;
    }
    
} catch (Exception $e) {
    // Clean up temporary file if it exists
    if ($tempFile && file_exists($tempFile)) {
        unlink($tempFile);
    }
    error_log("File upload error for user ID: " . $_SESSION['user']['id'] . " - " . $e->getMessage());
    setFlashMessage('danger', $e->getMessage());
}

header("Location: {$base_url}profile/index.php");
exit();