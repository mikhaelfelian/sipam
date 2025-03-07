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
        $id_mutasi = (int)$_POST['id_mutasi'];
        $jenis_berkas_array = $_POST['jenis_berkas'];
        $nama_array = $_POST['nama'];
        $files = $_FILES['file'];
        
        // Get current date
        $current_date = date('Y-m-d');

        // Get no_kk for directory
        $query = "SELECT no_kk FROM tbl_trx_mutasi_warga WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id_mutasi);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $mutasi = mysqli_fetch_assoc($result);

        if (!$mutasi) {
            throw new Exception('Data mutasi tidak ditemukan');
        }

        // Create directories
        $base_upload_dir = '../assets/files/';
        if (!file_exists($base_upload_dir)) {
            mkdir($base_upload_dir, 0777, true);
        }

        $kk_dir = $base_upload_dir . $mutasi['no_kk'] . '/';
        if (!file_exists($kk_dir)) {
            mkdir($kk_dir, 0777, true);
        }

        // Process each file
        $success_count = 0;
        $error_messages = [];
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file_name = $files['name'][$i];
                $file_tmp = $files['tmp_name'][$i];
                $file_size = $files['size'][$i];
                
                // Validate file size (2MB max)
                if ($file_size > 2 * 1024 * 1024) {
                    $error_messages[] = "File '$file_name' melebihi 2MB";
                    continue;
                }
                
                // Validate file type
                $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
                $file_type = mime_content_type($file_tmp);
                if (!in_array($file_type, $allowed_types)) {
                    $error_messages[] = "File '$file_name' bukan file PDF atau gambar yang valid";
                    continue;
                }
                
                // Generate unique filename
                $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_filename = uniqid() . '_' . time() . '.' . $extension;
                $file_path = $mutasi['no_kk'] . '/' . $new_filename;
                
                // Move file to upload directory
                if (move_uploaded_file($file_tmp, $base_upload_dir . $file_path)) {
                    // Insert file record
                    $query = "INSERT INTO tbl_trx_mutasi_warga_file 
                             (id_mutasi, tgl_masuk, nama, file, tipe, jenis_berkas, status) 
                             VALUES (?, ?, ?, ?, ?, ?, 0)";
                    
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "isssii", 
                        $id_mutasi,
                        $current_date,      // Current date for tgl_masuk
                        $nama_array[$i],    // From nama field
                        $file_path,         // Uploaded file path
                        $jenis_berkas_array[$i],  // Jenis berkas as tipe
                        $jenis_berkas_array[$i]   // Jenis berkas
                    );
                    
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception('Gagal menyimpan data file: ' . mysqli_error($conn));
                    }
                    $success_count++;
                } else {
                    $error_messages[] = "Gagal mengupload file '$file_name'";
                }
            }
        }
        
        // Set appropriate message
        if ($success_count > 0) {
            $message = "Berhasil mengupload $success_count file";
            if (!empty($error_messages)) {
                $message .= ". Beberapa error terjadi: " . implode(", ", $error_messages);
            }
            setFlashMessage('success', $message);
        } else {
            throw new Exception(implode(", ", $error_messages));
        }
        
    } catch (Exception $e) {
        setFlashMessage('danger', $e->getMessage());
    }
    
    header("Location: warga_masuk_berkas.php?id=$id_mutasi");
    exit();
} else {
    header("Location: warga_masuk.php");
    exit();
} 