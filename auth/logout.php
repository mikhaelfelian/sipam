<?php
require_once '../config/config.php';
startSession();
session_destroy();
header("Location: {$base_url}auth/login.php");
exit();