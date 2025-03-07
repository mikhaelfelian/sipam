<?php
if (!isset($settings)) {
    require_once dirname(__FILE__) . '/../config/connect.php';
    $settings = getSettings();
}

// Include base URL configuration
require_once dirname(__FILE__) . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($settings['judul_app']) ? $settings['judul_app'] : 'PAM Warga'; ?> |
        <?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></title>

    <!-- Add favicon if exists -->
    <?php if (!empty($settings['favicon'])): ?>
        <link rel="icon" type="image/x-icon"
            href="<?php echo $base_url; ?>assets/images/app/<?php echo $settings['favicon']; ?>">
    <?php endif; ?>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo $base_url_style; ?>plugins/fontawesome-free/css/all.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet"
        href="<?php echo $base_url_style; ?>plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="<?php echo $base_url_style; ?>plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- JQVMap -->
    <link rel="stylesheet" href="<?php echo $base_url_style; ?>plugins/jqvmap/jqvmap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo $base_url_style; ?>dist/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="<?php echo $base_url_style; ?>plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="<?php echo $base_url_style; ?>plugins/daterangepicker/daterangepicker.css">
    <!-- summernote -->
    <link rel="stylesheet" href="<?php echo $base_url_style; ?>plugins/summernote/summernote-bs4.min.css">
    <!-- DataTables -->
    <link rel="stylesheet"
        href="<?php echo $base_url_style; ?>plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet"
        href="<?php echo $base_url_style; ?>plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet"
        href="<?php echo $base_url_style; ?>plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet"
        href="<?php echo $base_url_style; ?>plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="<?php echo $base_url_style; ?>plugins/toastr/toastr.min.css">
    <!-- Add these in the head section -->
    <link rel="stylesheet" href="<?php echo $base_url_style; ?>plugins/jquery-ui/jquery-ui.min.css">
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <?php if (!empty($settings['logo'])): ?>
                <img class="animation__shake"
                    src="<?php echo $base_url; ?>assets/images/app/<?php echo $settings['logo']; ?>"
                    alt="<?php echo htmlspecialchars($settings['judul_app']); ?>" height="60" width="60">
            <?php else: ?>
                <img class="animation__shake"
                    src="<?php echo $base_url; ?>assets/theme/admin-lte-3/dist/img/AdminLTELogo.png" alt="AdminLTELogo"
                    height="60" width="60">
            <?php endif; ?>
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- User menu section -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fas fa-user"></i>
                        <?php
                        // Get user's display name
                        $display_name = $_SESSION['user']['username'];
                        if ($_SESSION['user']['id_warga']) {
                            // If user is linked to warga, get warga name
                            $stmt = mysqli_prepare($conn, "SELECT nama FROM tbl_m_warga WHERE id = ?");
                            mysqli_stmt_bind_param($stmt, "i", $_SESSION['user']['id_warga']);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            if ($warga = mysqli_fetch_assoc($result)) {
                                $display_name = $warga['nama'];
                            }
                        }
                        echo htmlspecialchars($display_name);
                        ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <a href="<?php echo $base_url; ?>profile/index.php" class="dropdown-item">
                            <i class="fas fa-user-cog mr-2"></i> Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo $base_url; ?>auth/logout.php" class="dropdown-item" 
                           onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </li>

                <!-- Keep existing navbar items -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>

        <?php require_once dirname(__FILE__) . '/sidebar.php'; ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">