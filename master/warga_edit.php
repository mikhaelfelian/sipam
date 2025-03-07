<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/auth.php';
require_once '../config/flash_message.php';
require_once '../config/security.php';

startSession();
checkLogin();


// Get ID from URL and validate
if (!isset($_GET['id'])) {
    setFlashMessage('danger', 'ID warga tidak ditemukan');
    header("Location: {$base_url}master/warga.php");
    exit();
}

$id = (int)$_GET['id'];

// Get warga data
$query = "SELECT * FROM tbl_m_warga WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
if (!mysqli_stmt_execute($stmt)) {
    die("Query execution failed: " . mysqli_error($conn));
}

$result = mysqli_stmt_get_result($stmt);
$sql_warga = mysqli_fetch_assoc($result);

if (!$sql_warga) {
    setFlashMessage('danger', 'Data warga tidak ditemukan!');
    header("Location: {$base_url}master/warga.php");
    exit();
}

$page_title = "Edit Warga";
require_once '../template/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit Warga</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Master Data</li>
                    <li class="breadcrumb-item"><a href="warga.php">Data Warga</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <form action="warga_update.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $sql_warga['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo createCSRFToken(); ?>">
                <input type="hidden" name="tgl_masuk" value="<?php echo date('d/m/Y', strtotime($sql_warga['tgl_masuk'])); ?>">
                
                <div class="card-header">
                    <h3 class="card-title">Form Edit Warga</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kk">No KK</label>
                                <input type="text" class="form-control" id="kk" name="kk" 
                                       value="<?php echo htmlspecialchars($sql_warga['kk']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="nik">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik" 
                                       value="<?php echo htmlspecialchars($sql_warga['nik']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="nama">Nama</label>
                                <input type="text" class="form-control" id="nama" name="nama" 
                                       value="<?php echo htmlspecialchars($sql_warga['nama']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="alamat">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3"><?php echo htmlspecialchars($sql_warga['alamat']); ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="blok">Blok</label>
                                <input type="text" class="form-control" id="blok" name="blok" 
                                       value="<?php echo htmlspecialchars($sql_warga['blok']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="no_hp">No HP</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">+62</span>
                                    </div>
                                    <input type="text" class="form-control" id="no_hp" name="no_hp" 
                                           value="<?php echo htmlspecialchars($sql_warga['no_hp']); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status_rumah">Status Rumah</label>
                                <select class="form-control" id="status_rumah" name="status_rumah" required>
                                    <option value="1" <?php echo $sql_warga['status_rumah'] == '1' ? 'selected' : ''; ?>>Sendiri</option>
                                    <option value="2" <?php echo $sql_warga['status_rumah'] == '2' ? 'selected' : ''; ?>>Kontrak</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="status_warga">Status Warga</label>
                                <select class="form-control" id="status_warga" name="status_warga" required>
                                    <option value="1" <?php echo $sql_warga['status_warga'] == 1 ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="0" <?php echo $sql_warga['status_warga'] == 0 ? 'selected' : ''; ?>>Tidak Aktif</option>
                                </select>
                            </div>
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
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require_once '../template/footer.php'; ?> 