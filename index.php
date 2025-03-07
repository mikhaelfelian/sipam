<?php
require_once 'config/config.php';
require_once 'config/auth.php';

startSession();

// Redirect to dashboard if logged in, otherwise to login page
if (isset($_SESSION['user'])) {
    header("Location: {$base_url}dashboard.php");
} else {
    header("Location: {$base_url}auth/login.php");
}
exit();
?>