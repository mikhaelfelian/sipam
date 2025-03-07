<?php
function checkLogin() {
    if (!isset($_SESSION['user'])) {
        header("Location: " . $GLOBALS['base_url'] . "auth/login.php");
        exit();
    }
}

function checkRole($allowed_roles) {
    if (!in_array($_SESSION['user']['role'], $allowed_roles)) {
        setFlashMessage('danger', 'Anda tidak memiliki akses ke halaman ini');
        header("Location: " . $GLOBALS['base_url'] . "dashboard.php");
        exit();
    }
} 