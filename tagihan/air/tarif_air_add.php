<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

$settings = getSettings();
$page_title = "Tambah Tarif Air";
$current_page = 'tarif_air';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../../template/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Tambah Tarif Air</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Tagihan Air</li>
                    <li class="breadcrumb-item"><a href="tarif_air.php">Tarif Air</a></li>
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
                        <h3 class="card-title">Form Tambah Tarif Air</h3>
                    </div>
                    <form action="tarif_air_save.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="card-body">
                            <?php if ($flash_message = getFlashMessage()): ?>
                                <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <?php echo $flash_message['text']; ?>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="range_pemakaian">Range Pemakaian</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="range_pemakaian" name="range_pemakaian" 
                                           placeholder="Contoh: 0-10" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">m³</span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Format: angka-angka (Contoh: 0-10)</small>
                            </div>

                            <div class="form-group">
                                <label for="biaya_m3">Biaya per m³</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" class="form-control" id="biaya_m3" name="biaya_m3" 
                                           min="0" step="0.01" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="biaya_mtc">Biaya Maintenance</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" class="form-control" id="biaya_mtc" name="biaya_mtc" 
                                           min="0" step="0.01" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="biaya_adm">Biaya Admin</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" class="form-control" id="biaya_adm" name="biaya_adm" 
                                           min="0" step="0.01" required>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="tarif_air.php" class="btn btn-default">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(function() {
    $('#range_pemakaian').on('input', function() {
        let value = $(this).val();
        
        // Only allow numbers and hyphen
        value = value.replace(/[^0-9\-]/g, '');
        
        // Ensure only one hyphen
        let parts = value.split('-');
        if (parts.length > 2) {
            value = parts[0] + '-' + parts[1];
        }
        
        $(this).val(value);
    });

    $('form').on('submit', function(e) {
        let range = $('#range_pemakaian').val();
        let parts = range.split('-');
        
        if (parts.length !== 2) {
            alert('Format range pemakaian harus: angka-angka (Contoh: 0-10)');
            e.preventDefault();
            return false;
        }

        let start = parseInt(parts[0]);
        let end = parseInt(parts[1]);

        if (isNaN(start) || isNaN(end)) {
            alert('Range pemakaian harus berupa angka');
            e.preventDefault();
            return false;
        }

        if (start >= end) {
            alert('Angka awal harus lebih kecil dari angka akhir');
            e.preventDefault();
            return false;
        }

        // Add m³ to the value before submitting
        $('#range_pemakaian').val(range + ' m³');
    });
});
</script>

<?php require_once '../../template/footer.php'; ?> 