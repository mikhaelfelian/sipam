<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();

// Check if user is logged in
checkLogin();

// Only superadmin can access user management
checkRole(['superadmin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$base_url}admin/users.php");
    exit();
}

try {
    // Validate required fields
    $required_fields = ['username', 'password', 'role'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Field $field is required");
        }
    }
    
    // Validate and sanitize input
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $id_warga = !empty($_POST['id_warga']) ? (int)$_POST['id_warga'] : null;
    
    // Additional validation
    if ($role === 'warga' && empty($id_warga)) {
        throw new Exception("Warga harus dipilih untuk role warga");
    }
    
    if (!in_array($role, ['superadmin', 'pengurus', 'warga'])) {
        throw new Exception("Role tidak valid");
    }
    
    // Check for duplicate username
    $stmt = mysqli_prepare($conn, "SELECT id FROM tbl_users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_fetch($stmt)) {
        throw new Exception("Username sudah digunakan");
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = mysqli_prepare($conn, "INSERT INTO tbl_users (username, password, role, id_warga) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssi", $username, $hashed_password, $role, $id_warga);
    
    if (mysqli_stmt_execute($stmt)) {
        setFlashMessage('success', "Pengguna berhasil ditambahkan");
        header("Location: {$base_url}admin/users.php");
        exit();
    } else {
        throw new Exception(mysqli_error($conn));
    }
    
} catch (Exception $e) {
    setFlashMessage('danger', $e->getMessage());
    header("Location: {$base_url}admin/user_add.php");
    exit();
} 