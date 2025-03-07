<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'db_warga';

// Create connection using mysqli
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");

function getSettings() {
    global $conn;
    
    try {
        // First try to get existing settings
        $query = "SELECT * FROM tbl_pengaturan WHERE id = 1";
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            throw new Exception("Query failed: " . mysqli_error($conn));
        }
        
        $settings = mysqli_fetch_assoc($result);
        
        if (!$settings) {
            // Create default settings if none exist
            $default_query = "INSERT INTO tbl_pengaturan (judul, judul_app, url, theme, pagination_limit) 
                            VALUES ('PAM Warga', 'PAM Warga', 'http://localhost/pam', 'default', 10)";
            
            if (!mysqli_query($conn, $default_query)) {
                throw new Exception("Failed to create default settings: " . mysqli_error($conn));
            }
            
            // Get the newly created settings
            $result = mysqli_query($conn, $query);
            $settings = mysqli_fetch_assoc($result);
        }
        
        return $settings;
        
    } catch (Exception $e) {
        echo "Error in getSettings(): " . $e->getMessage();
        return null;
    }
}
?> 