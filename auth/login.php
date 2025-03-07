<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';

startSession();

// Get settings for logo
$settings = getSettings();

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header("Location: {$base_url}dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
        $password = $_POST['password'];
        
        // Validate input
        if (empty($username) || empty($password)) {
            throw new Exception("Username dan password harus diisi");
        }
        
        // Get user
        $stmt = mysqli_prepare($conn, "SELECT * FROM tbl_users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception("Username atau password salah");
        }

        // Set session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'id_warga' => $user['id_warga']
        ];
        
        setFlashMessage('success', "Selamat datang, {$user['username']}!");
        header("Location: {$base_url}dashboard.php");
        exit();
        
    } catch (Exception $e) {
        setFlashMessage('danger', $e->getMessage());
    }
}

$page_title = "Login";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | <?php echo isset($settings['judul_app']) ? $settings['judul_app'] : 'PAM Warga'; ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo $base_url_style; ?>plugins/fontawesome-free/css/all.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="<?php echo $base_url_style; ?>plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo $base_url_style; ?>dist/css/adminlte.min.css">
    <?php if (!empty($settings['favicon'])): ?>
    <link rel="icon" type="image/x-icon" href="<?php echo $base_url; ?>assets/images/app/<?php echo $settings['favicon']; ?>">
    <?php endif; ?>
    <style>
        .login-page {
            background: linear-gradient(180deg, #2c3034 0%, #1a1d20 100%);
        }
        .title-text {
            color: #e2e2e2;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        .subtitle-text {
            color: #d4d4d4;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }
        .card {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="hold-transition login-page">
    <!-- Update the titles with new classes -->
    <div class="text-center mb-4">
        <h2 class="title-text mb-2">PERUM MUTIARA PANDANARAN</h2>
        <h4 class="subtitle-text">PENDATAAN WARGA</h4>
    </div>

    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <?php if (!empty($settings['logo'])): ?>
                    <div class="text-center mb-3">
                        <img src="<?php echo $base_url; ?>assets/images/app/<?php echo htmlspecialchars($settings['logo']); ?>" 
                             alt="<?php echo htmlspecialchars($settings['judul_app']); ?>"
                             style="max-height: 100px; max-width: 100%;">
                    </div>
                <?php endif; ?>
                <p class="welcome-text" style="font-size: 16px; color: #666; margin-bottom: 0;">
                    SELAMAT DATANG DI
                </p>
                <p class="welcome-text" style="font-size: 16px; font-weight: bold; color: #444;">
                    PERUMAHAN MUTIARA PANDANARAN
                </p>
            </div>
            <div class="card-body">
                <?php if ($flash_message = getFlashMessage()): ?>
                    <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <?php echo $flash_message['text']; ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo $base_url; ?>auth/login.php" method="post">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="username" placeholder="Username" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Login</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="<?php echo $base_url_style; ?>plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="<?php echo $base_url_style; ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="<?php echo $base_url_style; ?>dist/js/adminlte.min.js"></script>
</body>
</html> 