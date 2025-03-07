<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();

// Check if user is logged in
checkLogin();

// Only superadmin and pengurus can access this page
checkRole(['superadmin', 'pengurus']);

$settings = getSettings();
$page_title = isset($settings['judul']) ? $settings['judul'] : 'Tambah Warga';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../template/header.php';

?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Tambah Warga</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Master Data</li>
                    <li class="breadcrumb-item"><a href="warga.php">Data Warga</a></li>
                    <li class="breadcrumb-item active">Tambah</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Form Tambah Warga</h3>
                    </div>
                    <form method="POST" action="warga_save.php">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="card-body">
                            <?php if ($flash_message = getFlashMessage()): ?>
                                <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <?php echo $flash_message['text']; ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label for="kk">Nomor KK</label>
                                <input type="text" class="form-control" id="kk" name="kk" 
                                       value="<?php echo isset($_POST['kk']) ? htmlspecialchars($_POST['kk']) : ''; ?>" 
                                       required>
                            </div>
                            <div class="form-group">
                                <label for="nik">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik" required>
                            </div>
                            <div class="form-group">
                                <label for="nama">Nama</label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                            </div>
                            <div class="form-group">
                                <label for="alamat">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="blok">Blok</label>
                                <input type="text" class="form-control" id="blok" name="blok" required>
                            </div>
                            <div class="form-group">
                                <label for="status_rumah">Status Rumah</label>
                                <select class="form-control" id="status_rumah" name="status_rumah" required>
                                    <option value="">Pilih Status</option>
                                    <option value="1">Sendiri</option>
                                    <option value="2">Kontrak</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tgl_masuk">Tanggal Masuk</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="tgl_masuk" name="tgl_masuk" 
                                           value="<?php echo date('d/m/Y'); ?>" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Format: dd/mm/yyyy</small>
                            </div>
                            <div class="form-group" id="kontrak_end_date" style="display: none;">
                                <label for="tgl_keluar">Tanggal Berakhir Kontrak</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="tgl_keluar" name="tgl_keluar">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Format: dd/mm/yyyy</small>
                            </div>
                            <div class="form-group">
                                <label for="no_hp">No WhatsApp</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">+62</span>
                                    </div>
                                    <input type="text" class="form-control" id="no_hp" name="no_hp" 
                                           placeholder="8xxxxxxxxxx" pattern="[0-9]{10,13}">
                                </div>
                                <small class="form-text text-muted">Format: 8xxxxxxxxxx (tanpa awalan 0)</small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="warga.php" class="btn btn-default">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(function() {
    // Initialize datepicker
    $("#tgl_masuk").datepicker({
        dateFormat: 'dd/mm/yy',
        changeMonth: true,
        changeYear: true,
        yearRange: '2000:+0'
    });

    $("#tgl_keluar").datepicker({
        dateFormat: 'dd/mm/yy',
        changeMonth: true,
        changeYear: true,
        yearRange: '2000:+10'
    });

    // Handle status_rumah change
    $('#status_rumah').change(function() {
        const kontrakEndDate = $('#kontrak_end_date');
        const tglKeluarInput = $('#tgl_keluar');
        
        if (this.value === '2') {
            kontrakEndDate.show();
            tglKeluarInput.prop('required', true);
        } else {
            kontrakEndDate.hide();
            tglKeluarInput.prop('required', false).val('');
        }
    });
});
</script>

<?php require_once '../template/footer.php'; ?> 