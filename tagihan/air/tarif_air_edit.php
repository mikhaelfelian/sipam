<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

$settings = getSettings();
$page_title = "Edit Tarif Air";
$current_page = 'tarif_air';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_GET['id'])) {
    setFlashMessage('danger', 'ID tarif tidak ditemukan');
    header("Location: {$base_url}tagihan/air/tarif_air.php");
    exit();
}

$tarif_id = (int)$_GET['id'];

try {
    // Get tarif data
    $stmt = mysqli_prepare($conn, "SELECT * FROM tbl_m_air_tarif WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $tarif_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $tarif = mysqli_fetch_assoc($result);

    if (!$tarif) {
        throw new Exception("Data tarif tidak ditemukan");
    }

} catch (Exception $e) {
    setFlashMessage('danger', $e->getMessage());
    header("Location: {$base_url}tagihan/air/tarif_air.php");
    exit();
}

require_once '../../template/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit Tarif Air</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Tagihan Air</li>
                    <li class="breadcrumb-item"><a href="tarif_air.php">Tarif Air</a></li>
                    <li class="breadcrumb-item active">Edit</li>
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
                        <h3 class="card-title">Form Edit Tarif Air</h3>
                    </div>
                    <form action="tarif_air_update.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="id" value="<?php echo $tarif['id']; ?>">
                        
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
                                           value="<?php echo str_replace(' m³', '', $tarif['range_pemakaian']); ?>"
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
                                    <input type="text" class="form-control number-format" id="biaya_m3" name="biaya_m3" 
                                           value="<?php echo (float)$tarif['biaya_m3']; ?>"
                                           required>
                                    <input type="hidden" name="biaya_m3_hidden" id="biaya_m3_hidden" 
                                           value="<?php echo (float)$tarif['biaya_m3']; ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="biaya_mtc">Biaya Maintenance</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="text" class="form-control number-format" id="biaya_mtc" name="biaya_mtc" 
                                           value="<?php echo (float)$tarif['biaya_mtc']; ?>"
                                           required>
                                    <input type="hidden" name="biaya_mtc_hidden" id="biaya_mtc_hidden" 
                                           value="<?php echo (float)$tarif['biaya_mtc']; ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="biaya_adm">Biaya Admin</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="text" class="form-control number-format" id="biaya_adm" name="biaya_adm" 
                                           value="<?php echo (float)$tarif['biaya_adm']; ?>"
                                           required>
                                    <input type="hidden" name="biaya_adm_hidden" id="biaya_adm_hidden" 
                                           value="<?php echo (float)$tarif['biaya_adm']; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="tarif_air.php" class="btn btn-default">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../../template/footer.php'; ?>

<!-- First load jQuery -->
<script src="<?php echo $base_url_style; ?>plugins/jquery/jquery.min.js"></script>
<!-- Then jQuery UI -->
<script src="<?php echo $base_url_style; ?>plugins/jquery-ui/jquery-ui.min.js"></script>base_url
<!-- Bootstrap 4 -->
<script src="<?php echo $base_url_style; ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="<?php echo $base_url_style; ?>dist/js/adminlte.min.js"></script>
<!-- Then your custom script -->
<script>
$(document).ready(function() {
    // Function to format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Function to clean number (remove formatting)
    function cleanNumber(num) {
        return num.toString().replace(/\./g, '');
    }

    // Initialize number format
    $('.number-format').each(function() {
        var value = $(this).val();
        $(this).val(formatNumber(value));
        $('#' + $(this).attr('id') + '_hidden').val(value);
    });

    // Handle numeric input
    $('.number-format').on('input', function() {
        var value = $(this).val().replace(/[^\d]/g, '');
        $(this).val(formatNumber(value));
        $('#' + $(this).attr('id') + '_hidden').val(value);
    });

    // Handle range_pemakaian input
    $('#range_pemakaian').on('input', function() {
        let value = $(this).val();
        value = value.replace(/[^0-9\-]/g, '');
        let parts = value.split('-');
        if (parts.length > 2) {
            value = parts[0] + '-' + parts[1];
        }
        $(this).val(value);
    });

    // Handle form submission
    $('form').on('submit', function(e) {
        // Validate range_pemakaian
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

        // Add m³ to the range value before submitting
        $('#range_pemakaian').val(range + ' m³');

        // Update hidden fields with unformatted values
        $('.number-format').each(function() {
            var value = $(this).val().replace(/[^\d]/g, '');
            $('#' + $(this).attr('id') + '_hidden').val(value);
        });
    });
});
</script>
</body>
</html> 