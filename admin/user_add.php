<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();

// Check if user is logged in
checkLogin();

// Only superadmin can access user management
checkRole(['superadmin']);

$settings = getSettings();
$page_title = "Tambah Pengguna";

// Get list of warga for dropdown
$warga_query = "SELECT id, nama, nik FROM tbl_m_warga ORDER BY nama ASC";
$warga_result = mysqli_query($conn, $warga_query);

require_once '../template/header.php';
?>



<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Tambah Pengguna</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Pengaturan</li>
                    <li class="breadcrumb-item"><a href="users.php">Pengguna</a></li>
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
                        <h3 class="card-title">Form Tambah Pengguna</h3>
                    </div>
                    <form action="user_save.php" method="POST">
                        <div class="card-body">
                            <?php if ($flash_message = getFlashMessage()): ?>
                                <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <?php echo $flash_message['text']; ?>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="username">Username (NIK)</label>
                                <select class="form-control select2" id="username" name="username" required>
                                    <option value="">Pilih Warga (NIK)</option>
                                    <?php 
                                    $warga_nik_query = "SELECT id, nik, nama, blok FROM tbl_m_warga 
                                                       WHERE id NOT IN (SELECT id_warga FROM tbl_users WHERE id_warga IS NOT NULL) 
                                                       ORDER BY nama ASC";
                                    $warga_nik_result = mysqli_query($conn, $warga_nik_query);
                                    while ($warga = mysqli_fetch_assoc($warga_nik_result)): 
                                    ?>
                                        <option value="<?php echo $warga['nik']; ?>" 
                                                data-id="<?php echo $warga['id']; ?>">
                                            <?php echo htmlspecialchars($warga['nik'] . ' - ' . $warga['nama'] . ' (' . $warga['blok'] . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <small class="form-text text-muted">Username akan menggunakan NIK warga</small>
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="form-group">
                                <label for="role">Role</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="superadmin">Superadmin</option>
                                    <option value="pengurus">Pengurus</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="id_warga">Warga</label>
                                <select class="form-control" id="id_warga" name="id_warga">
                                    <option value="">Pilih Warga</option>
                                    <?php while ($warga = mysqli_fetch_assoc($warga_result)): ?>
                                        <option value="<?php echo $warga['id']; ?>">
                                            <?php echo htmlspecialchars($warga['nama'] . ' - ' . $warga['nik']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <small class="form-text text-muted">Pilih warga jika role adalah "warga"</small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="users.php" class="btn btn-default">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add this JavaScript for role-based warga field -->
<script>
document.getElementById('role').addEventListener('change', function() {
    const idWargaField = document.getElementById('id_warga');
    if (this.value === 'warga') {
        idWargaField.required = true;
        idWargaField.parentElement.style.display = 'block';
    } else {
        idWargaField.required = false;
        idWargaField.parentElement.style.display = 'none';
    }
});

// Trigger the change event on page load
document.getElementById('role').dispatchEvent(new Event('change'));
</script>

<!-- Add this JavaScript before closing </body> tag -->
<script>
$(function() {
    // Initialize select2
    $('#username').select2({
        theme: 'bootstrap4',
        placeholder: 'Pilih Warga (NIK)',
        allowClear: true
    });

    // When username/NIK is selected, update id_warga field
    $('#username').on('select2:select', function(e) {
        var $option = $(e.params.data.element);
        var wargaId = $option.data('id');
        $('#id_warga').val(wargaId).trigger('change');
    });
});
</script>

<?php require_once '../template/footer.php'; ?> 