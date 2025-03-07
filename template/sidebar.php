<?php
if (!isset($settings)) {
    require_once dirname(__FILE__) . '/../config/connect.php';
    $settings = getSettings();
}

if (!isset($base_url)) {
    require_once dirname(__FILE__) . '/../config/config.php';
}
?>
<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?php echo $base_url; ?>dashboard.php" class="brand-link">
        <?php if (!empty($settings['logo'])): ?>
            <img src="<?php echo $base_url; ?>assets/images/app/<?php echo $settings['logo']; ?>" 
                 alt="<?php echo htmlspecialchars($settings['judul_app']); ?>" 
                 class="brand-image img-circle elevation-3">
        <?php else: ?>
            <img src="<?php echo $base_url; ?>assets/theme/admin-lte-3/dist/img/AdminLTELogo.png" 
                 alt="AdminLTE Logo" 
                 class="brand-image img-circle elevation-3">
        <?php endif; ?>
        <span class="brand-text font-weight-light"><?php echo htmlspecialchars($settings['judul_app']); ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <?php 
                // Get user profile picture from database
                $user_id = $_SESSION['user']['id'];
                $query = "SELECT u.profile_picture, w.nama 
                         FROM tbl_users u 
                         LEFT JOIN tbl_m_warga w ON u.id_warga = w.id 
                         WHERE u.id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($result);
                ?>
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?php echo $base_url; ?>profile/get_image.php?filename=<?php echo urlencode($user['profile_picture']); ?>" 
                         class="img-circle elevation-1" 
                         alt="User Image"
                         style="width: 2.1rem; height: 2.1rem; object-fit: cover; border-radius: 50% !important;">
                <?php else: ?>
                    <img src="<?php echo $base_url_style; ?>assets/theme/admin-lte-3/dist/img/user2-160x160.jpg" 
                         class="img-circle elevation-1" 
                         alt="User Image"
                         style="width: 2.1rem; height: 2.1rem; object-fit: cover; border-radius: 50% !important;">
                <?php endif; ?>
            </div>
            <div class="info">
                <a href="<?php echo $base_url; ?>profile/index.php" class="d-block">
                    <?php 
                    // Show warga name if exists, otherwise show username
                    if (!empty($user['nama'])) {
                        echo htmlspecialchars(strtoupper($user['nama']));
                    } else {
                        echo htmlspecialchars(strtoupper($_SESSION['user']['username']));
                    }
                    ?>
                    <small class="d-block text-muted">
                        <?php 
                        $role = $_SESSION['user']['role'];
                        echo ucfirst($role);
                        ?>
                    </small>
                </a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Dashboard - All roles -->
                <li class="nav-item">
                    <a href="<?php echo $base_url; ?>dashboard.php" class="nav-link <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Master Data - Superadmin and Pengurus only -->
                <?php if (in_array($_SESSION['user']['role'], ['superadmin', 'pengurus'])): ?>
                <li class="nav-item <?php echo in_array($current_page, ['warga', 'platform']) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo in_array($current_page, ['warga', 'platform']) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-database"></i>
                        <p>
                            Master Data
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>master/warga.php" 
                               class="nav-link <?php echo $current_page == 'warga' ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Data Warga</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>master/platform.php" 
                               class="nav-link <?php echo $current_page == 'platform' ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Platform Pembayaran</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Mutasi Warga Menu -->
                <?php if (in_array($_SESSION['user']['role'], ['superadmin', 'pengurus'])): ?>
                    <li class="nav-item <?php echo in_array($current_page, ['warga_masuk', 'warga_keluar']) ? 'menu-open' : ''; ?>">
                        <a href="#" class="nav-link <?php echo in_array($current_page, ['warga_masuk', 'warga_keluar']) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-exchange-alt"></i>
                            <p>
                                Mutasi Warga
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?php echo $base_url; ?>mutasi/warga_masuk.php" 
                                   class="nav-link <?php echo $current_page == 'warga_masuk' ? 'active' : ''; ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Warga Masuk</p>
                                </a>
                            </li>
                            <!-- <li class="nav-item">
                                <a href="<?php echo $base_url; ?>mutasi/warga_keluar.php" 
                                   class="nav-link <?php echo $current_page == 'warga_keluar' ? 'active' : ''; ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Warga Keluar</p>
                                </a>
                            </li> -->
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Tagihan Air Menu -->
                <li class="nav-item <?php echo in_array($current_page, ['tagihan_air', 'catat_meter', 'tarif_air', 'pengeluaran', 'laporan_tagihan']) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo in_array($current_page, ['tagihan_air', 'catat_meter', 'tarif_air', 'pengeluaran', 'laporan_tagihan']) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-tint"></i>
                        <p>
                            Tagihan Air
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <!-- Catat Meter - All roles can access -->
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>tagihan/air/catat_meter.php" 
                               class="nav-link <?php echo $current_page == 'catat_meter' ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Catat Meter</p>
                            </a>
                        </li>

                        <!-- Tagihan - All roles can access -->
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>tagihan/air/tagihan.php" 
                               class="nav-link <?php echo $current_page == 'tagihan_air' ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Tagihan</p>
                            </a>
                        </li>

                        <!-- Other menu items - Only for superadmin and pengurus -->
                        <?php if (in_array($_SESSION['user']['role'], ['superadmin', 'pengurus'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo $base_url; ?>tagihan/air/tarif_air.php" 
                                   class="nav-link <?php echo $current_page == 'tarif_air' ? 'active' : ''; ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Tarif Air</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo $base_url; ?>tagihan/air/pengeluaran.php" 
                                   class="nav-link <?php echo $current_page == 'pengeluaran' ? 'active' : ''; ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Pengeluaran</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo $base_url; ?>tagihan/air/laporan.php" 
                                   class="nav-link <?php echo $current_page == 'laporan_tagihan' ? 'active' : ''; ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Laporan</p>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- Laporan Keuangan - Superadmin and Pengurus only -->
                <?php if (in_array($_SESSION['user']['role'], ['superadmin', 'pengurus'])): ?>
                <li class="nav-item <?php echo in_array($current_page, ['laporan_keuangan', 'laporan_kas']) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo in_array($current_page, ['laporan_keuangan', 'laporan_kas']) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>
                            Laporan Keuangan
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>laporan/kas.php" 
                               class="nav-link <?php echo $current_page == 'laporan_kas' ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Kas</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Settings - Superadmin only -->
                <?php if ($_SESSION['user']['role'] === 'superadmin'): ?>
                <li class="nav-item <?php echo in_array($current_page, ['pengaturan', 'users']) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo in_array($current_page, ['pengaturan', 'users']) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>
                            Pengaturan
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>admin/settings.php" 
                               class="nav-link <?php echo $current_page == 'pengaturan' ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Pengaturan Umum</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $base_url; ?>admin/users.php" 
                               class="nav-link <?php echo $current_page == 'users' ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Pengguna</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</aside> 