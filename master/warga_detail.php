<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/auth.php';
require_once '../config/flash_message.php';

// Add error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

startSession();
checkLogin();

// Get ID from URL and validate
if (!isset($_GET['id'])) {
    setFlashMessage('danger', 'ID warga tidak ditemukan');
    header("Location: {$base_url}master/warga.php");
    exit();
}

$id = (int) $_GET['id'];

// Define the query
$query = "SELECT * FROM tbl_m_warga WHERE id = ?";

// Prepare and execute query with error checking
$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
if (!mysqli_stmt_execute($stmt)) {
    die("Query execution failed: " . mysqli_error($conn));
}

$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    die("Result fetch failed: " . mysqli_error($conn));
}

// Get warga data
$sql_warga = mysqli_fetch_assoc($result);
if (!$sql_warga) {
    setFlashMessage('danger', 'Data warga tidak ditemukan!');
    header("Location: {$base_url}master/warga.php");
    exit();
}

// Get user data separately
$user_query = "SELECT username, profile_picture FROM tbl_users WHERE id_warga = ? AND role = 'warga'";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$page_title = "Detail Warga";
require_once '../template/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Detail Warga</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Master Data</li>
                    <li class="breadcrumb-item"><a href="warga.php">Data Warga</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Warga</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-4">
                        <?php if ($user && !empty($user['profile_picture']) && file_exists('../assets/images/profile/' . $user['profile_picture'])): ?>
                            <img src="<?php echo $base_url; ?>assets/images/profile/<?php echo htmlspecialchars($user['profile_picture']); ?>"
                                class="img-fluid rounded" alt="Profile Picture" style="max-width: 200px; height: auto;">
                        <?php else: ?>
                            <img src="<?php echo $base_url; ?>assets/theme/admin-lte-3/dist/img/avatar5.png"
                                class="img-fluid rounded" alt="Default Profile" style="max-width: 200px; height: auto;">
                        <?php endif; ?>

                        <div class="mt-2">
                            <h5 class="mb-0"><?php echo htmlspecialchars($sql_warga['nama']); ?></h5>
                            <small class="text-muted">Blok <?php echo htmlspecialchars($sql_warga['blok']); ?></small>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-striped table-bordered">
                            <tr>
                                <th width="200px">No KK</th>
                                <td><?php echo htmlspecialchars($sql_warga['kk'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th>NIK</th>
                                <td><?php echo htmlspecialchars($sql_warga['nik'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <td><?php echo htmlspecialchars($sql_warga['nama'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td><?php echo htmlspecialchars($sql_warga['alamat'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Blok</th>
                                <td><?php echo htmlspecialchars($sql_warga['blok'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th>No HP</th>
                                <td>
                                    <?php if (!empty($sql_warga['no_hp'])): ?>
                                        +62<?php echo htmlspecialchars($sql_warga['no_hp']); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Masuk</th>
                                <td><?php echo !empty($sql_warga['tgl_masuk']) ? date('d/m/Y', strtotime($sql_warga['tgl_masuk'])) : '-'; ?>
                                </td>
                            </tr>
                            <?php if (!empty($sql_warga['tgl_keluar'])): ?>
                                <tr>
                                    <th>Tanggal Keluar</th>
                                    <td><?php echo date('d/m/Y', strtotime($sql_warga['tgl_keluar'])); ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Status Rumah</th>
                                <td><?php echo isset($sql_warga['status_rumah']) ? ($sql_warga['status_rumah'] == '1' ? 'Sendiri' : 'Kontrak') : '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Status Warga</th>
                                <td>
                                    <?php if (isset($sql_warga['status_warga'])): ?>
                                        <?php if ($sql_warga['status_warga'] == 1): ?>
                                            <span class="badge badge-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Tidak Aktif</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Terdaftar Sejak</th>
                                <td><?php echo !empty($sql_warga['created_at']) ? date('d/m/Y H:i', strtotime($sql_warga['created_at'])) : '-'; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-6">
                        <a href="warga.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>
                    <div class="col-6 text-right">
                            <a href="warga_edit.php?id=<?php echo $id; ?>" class="btn btn-primary">
                                <i class="fas fa-edit mr-2"></i>Edit
                            </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../template/footer.php'; ?>