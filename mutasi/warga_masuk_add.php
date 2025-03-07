<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

$settings = getSettings();
$page_title = "Tambah Mutasi Masuk";
$current_page = 'warga_masuk';

require_once '../template/header.php';
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Tambah Mutasi Masuk</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="warga_masuk.php">Warga Masuk</a></li>
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
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Form Mutasi Masuk</h3>
                    </div>
                    <form action="warga_masuk_process.php" method="POST">
                        <div class="card-body">
                            <?php if ($flash_message = getFlashMessage()): ?>
                                <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show" role="alert">
                                    <?php echo $flash_message['text']; ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="tgl_masuk">Tanggal Masuk <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="tgl_masuk" name="tgl_masuk" 
                                           placeholder="dd/mm/yyyy" required 
                                           value="<?php echo date('d/m/Y'); ?>" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="no_kk">Nomor KK <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="no_kk" name="no_kk" maxlength="16" required>
                            </div>

                            <div class="form-group">
                                <label for="nik">NIK <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nik" name="nik" maxlength="16" required>
                            </div>

                            <div class="form-group">
                                <label for="nama">Nama <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="nama" maxlength="100" required>
                            </div>

                            <div class="form-group">
                                <label for="blok">Blok <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="blok" name="blok" required>
                            </div>

                            <div class="form-group">
                                <label for="alamat_asal">Alamat Asal <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="alamat_asal" name="alamat_asal" rows="3" required></textarea>
                            </div>

                            <div class="form-group">
                                <label for="keterangan">Keterangan</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Status Rumah <span class="text-danger">*</span></label>
                                <select class="form-control" name="status_rumah" required>
                                    <option value="">Pilih Status Rumah</option>
                                    <option value="1">Sendiri</option>
                                    <option value="2">Kontrak</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="warga_masuk.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                                    </a>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="submit" class="btn btn-primary">
                                        Lanjutkan <i class="fas fa-arrow-right ml-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../template/footer.php'; ?>

<script>
// Initialize date picker
$(document).ready(function() {
    $('#tgl_masuk').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: {
            format: 'DD/MM/YYYY',
            separator: ' - ',
            applyLabel: 'Pilih',
            cancelLabel: 'Batal',
            fromLabel: 'Dari',
            toLabel: 'Ke',
            customRangeLabel: 'Custom',
            weekLabel: 'W',
            daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            firstDay: 1
        }
    });
});

// Add client-side validation for NIK and No KK (numbers only)
document.getElementById('nik').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

document.getElementById('no_kk').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script> 

<link rel="stylesheet" href="<?php echo $base_url; ?>assets/theme/admin-lte-3/plugins/daterangepicker/daterangepicker.css">
<script src="<?php echo $base_url; ?>assets/theme/admin-lte-3/plugins/moment/moment.min.js"></script>
<script src="<?php echo $base_url; ?>assets/theme/admin-lte-3/plugins/daterangepicker/daterangepicker.js"></script> 