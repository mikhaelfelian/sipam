<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();

// Check if user is logged in and is superadmin
checkLogin();
checkRole(['superadmin']);

// Add CSRF protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }

        // Validate and sanitize all inputs
        $judul = filter_var(trim($_POST['judul']), FILTER_SANITIZE_STRING);
        $judul_app = filter_var(trim($_POST['judul_app']), FILTER_SANITIZE_STRING);
        $url = filter_var(trim($_POST['url']), FILTER_SANITIZE_URL);
        $pagination_limit = filter_var($_POST['pagination_limit'], FILTER_VALIDATE_INT, [
            "options" => ["min_range" => 1, "max_range" => 100]
        ]);
        $alamat = filter_var(trim($_POST['alamat']), FILTER_SANITIZE_STRING);
        $kota = filter_var(trim($_POST['kota']), FILTER_SANITIZE_STRING);

        // Validate required fields
        if (!$judul || !$judul_app || !$url || !$pagination_limit || !$alamat || !$kota) {
            throw new Exception('Semua field harus diisi dengan benar');
        }

        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('Format URL tidak valid');
        }

        $upload_path = '../assets/images/app/';
        
        // Secure file upload handling
        $allowed_extensions = ['ico', 'png', 'jpg', 'jpeg'];
        $max_file_size = 2 * 1024 * 1024; // 2MB

        $favicon = $settings['favicon'] ?? '';
        $logo = $settings['logo'] ?? '';
        
        // Handle favicon upload
        if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
            // Validate file size
            if ($_FILES['favicon']['size'] > $max_file_size) {
                throw new Exception('Ukuran favicon maksimal 2MB');
            }

            $favicon_info = pathinfo($_FILES['favicon']['name']);
            $favicon_ext = strtolower($favicon_info['extension']);
            
            // Validate file type
            if (!in_array($favicon_ext, $allowed_extensions)) {
                throw new Exception('Format favicon tidak valid');
            }

            // Generate secure filename
            $favicon = 'favicon_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $favicon_ext;
            
            // Verify file is actually an image
            if (!getimagesize($_FILES['favicon']['tmp_name'])) {
                throw new Exception('File favicon bukan gambar yang valid');
            }

            if (!move_uploaded_file($_FILES['favicon']['tmp_name'], $upload_path . $favicon)) {
                throw new Exception('Gagal mengupload favicon');
            }

            // Securely delete old favicon
            if (!empty($settings['favicon'])) {
                $old_file = $upload_path . $settings['favicon'];
                if (file_exists($old_file) && is_file($old_file)) {
                    unlink($old_file);
                }
            }
        }

        // Similar secure handling for logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['logo']['size'] > $max_file_size) {
                throw new Exception('Ukuran logo maksimal 2MB');
            }

            $logo_info = pathinfo($_FILES['logo']['name']);
            $logo_ext = strtolower($logo_info['extension']);
            
            if (!in_array($logo_ext, $allowed_extensions)) {
                throw new Exception('Format logo tidak valid');
            }

            $logo = 'logo_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $logo_ext;
            
            if (!getimagesize($_FILES['logo']['tmp_name'])) {
                throw new Exception('File logo bukan gambar yang valid');
            }

            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path . $logo)) {
                throw new Exception('Gagal mengupload logo');
            }

            if (!empty($settings['logo'])) {
                $old_file = $upload_path . $settings['logo'];
                if (file_exists($old_file) && is_file($old_file)) {
                    unlink($old_file);
                }
            }
        }

        // Use prepared statement for update
        $query = "UPDATE tbl_pengaturan SET 
                    judul = ?, 
                    judul_app = ?, 
                    url = ?, 
                    pagination_limit = ?,
                    alamat = ?,
                    kota = ?,
                    favicon = ?,
                    logo = ?
                 WHERE id = 1";
                 
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt === false) {
            throw new Exception('Database error: ' . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "sssissss", 
            $judul, 
            $judul_app, 
            $url, 
            $pagination_limit, 
            $alamat, 
            $kota, 
            $favicon, 
            $logo
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Database error: ' . mysqli_stmt_error($stmt));
        }

        mysqli_stmt_close($stmt);
        setFlashMessage('success', "<strong>Berhasil!</strong> Pengaturan telah diperbarui.");
        
    } catch (Exception $e) {
        setFlashMessage('danger', "<strong>Error!</strong> " . htmlspecialchars($e->getMessage()));
    }
    
    // Prevent form resubmission
    header("Location: {$base_url}admin/settings.php");
    exit();
}

// Get current settings using prepared statement
$query = "SELECT * FROM tbl_pengaturan WHERE id = 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$settings = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

require_once '../template/header.php';
?>

<!-- Form with CSRF token -->
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <!-- Rest of your form fields -->
</form>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Pengaturan</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Pengaturan</li>
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
                        <h3 class="card-title">Form Pengaturan</h3>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="card-body">
                            <?php if ($flash_message = getFlashMessage()): ?>
                                <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <?php echo $flash_message['text']; ?>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="judul">Judul Website</label>
                                <input type="text" class="form-control" id="judul" name="judul" 
                                       value="<?php echo htmlspecialchars($settings['judul']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="judul_app">Judul Aplikasi</label>
                                <input type="text" class="form-control" id="judul_app" name="judul_app" 
                                       value="<?php echo htmlspecialchars($settings['judul_app']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="url">URL</label>
                                <input type="url" class="form-control" id="url" name="url" 
                                       value="<?php echo htmlspecialchars($settings['url']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="pagination_limit">Batas Pagination</label>
                                <input type="number" class="form-control" id="pagination_limit" name="pagination_limit" 
                                       value="<?php echo (int)$settings['pagination_limit']; ?>" required min="1">
                            </div>
                            <div class="form-group">
                                <label for="alamat">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="2" required><?php echo htmlspecialchars($settings['alamat']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="kota">Kota</label>
                                <input type="text" class="form-control" id="kota" name="kota" 
                                       value="<?php echo htmlspecialchars($settings['kota']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="favicon">Favicon</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="favicon" name="favicon" accept=".ico,.png,.jpg,.jpeg">
                                        <label class="custom-file-label" for="favicon">Pilih file</label>
                                    </div>
                                </div>
                                <?php if (!empty($settings['favicon'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo $base_url; ?>assets/images/app/<?php echo $settings['favicon']; ?>" 
                                             alt="Current Favicon" style="max-width: 32px;">
                                        <small class="text-muted ml-2">Favicon saat ini</small>
                                    </div>
                                <?php endif; ?>
                                <small class="form-text text-muted">Format yang diizinkan: ICO, PNG, JPG. Ukuran maksimal: 1MB</small>
                            </div>

                            <div class="form-group">
                                <label for="logo">Logo Aplikasi</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="logo" name="logo" accept=".png,.jpg,.jpeg">
                                        <label class="custom-file-label" for="logo">Pilih file</label>
                                    </div>
                                </div>
                                <?php if (!empty($settings['logo'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo $base_url; ?>assets/images/app/<?php echo $settings['logo']; ?>" 
                                             alt="Current Logo" style="max-height: 50px;">
                                        <small class="text-muted ml-2">Logo saat ini</small>
                                    </div>
                                <?php endif; ?>
                                <small class="form-text text-muted">Format yang diizinkan: PNG, JPG. Ukuran maksimal: 2MB</small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add this JavaScript for file input labels -->
<script>
document.querySelectorAll('.custom-file-input').forEach(input => {
    input.addEventListener('change', function(e) {
        const fileName = this.files[0].name;
        const label = this.nextElementSibling;
        label.textContent = fileName;
    });
});
</script>

<?php require_once '../template/footer.php'; ?> 
<?php

// Basic form for testing
?>
<form method="POST">
    <input type="text" name="judul" value="<?php echo isset($settings['judul']) ? $settings['judul'] : ''; ?>">
    <button type="submit">Save</button>
</form>