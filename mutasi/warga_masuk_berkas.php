<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';
require_once '../config/document_types.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

// Get mutasi ID from URL
$id_mutasi = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get mutasi data
$query = "SELECT * FROM tbl_trx_mutasi_warga WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_mutasi);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$mutasi = mysqli_fetch_assoc($result);

if (!$mutasi) {
    setFlashMessage('danger', 'Data mutasi tidak ditemukan');
    header("Location: warga_masuk.php");
    exit();
}

// Get uploaded files for this mutasi
$query_files = "SELECT * FROM tbl_trx_mutasi_warga_file WHERE id_mutasi = ? ORDER BY tgl_masuk DESC";
$stmt_files = mysqli_prepare($conn, $query_files);
mysqli_stmt_bind_param($stmt_files, "i", $id_mutasi);
mysqli_stmt_execute($stmt_files);
$result_files = mysqli_stmt_get_result($stmt_files);

$settings = getSettings();
$page_title = "Upload Berkas";
$current_page = 'warga_masuk';

require_once '../template/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Upload Berkas</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="warga_masuk.php">Warga Masuk</a></li>
                    <li class="breadcrumb-item active">Upload Berkas</li>
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
                <!-- Data Warga Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data Warga</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered">
                            <tr>
                                <th width="20%">Tanggal Masuk</th>
                                <td><?php echo date('d/m/Y', strtotime($mutasi['tgl_masuk'])); ?></td>
                            </tr>
                            <tr>
                                <th>No KK</th>
                                <td><?php echo htmlspecialchars($mutasi['no_kk']); ?></td>
                            </tr>
                            <tr>
                                <th>NIK</th>
                                <td><?php echo htmlspecialchars($mutasi['nik']); ?></td>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <td><?php echo htmlspecialchars($mutasi['nama']); ?></td>
                            </tr>
                            <tr>
                                <th>Blok</th>
                                <td><?php echo htmlspecialchars($mutasi['blok']); ?></td>
                            </tr>
                            <tr>
                                <th>Status Rumah</th>
                                <td>
                                    <?php 
                                    echo $mutasi['status_rumah'] == '1' ? 'Sendiri' : 'Kontrak';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Status Berkas</th>
                                <td>
                                    <?php if ($mutasi['status_berkas'] == '1'): ?>
                                        <span class="badge badge-success">Sudah Disetujui</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Belum Disetujui</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer text-right">
                        <?php if ($mutasi['status_berkas'] == 0): ?>
                            <a href="warga_masuk_approve.php?id=<?php echo $id_mutasi; ?>" 
                               class="btn btn-primary"
                               onclick="return confirm('Apakah Anda yakin ingin menambahkan warga ini?');">
                                <i class="fas fa-plus mr-1"></i> Tambah Warga
                            </a>
                        <?php else: ?>
                            <button class="btn btn-success" disabled>
                                <i class="fas fa-check mr-1"></i> Warga Sudah Ditambahkan
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- After Data Warga card -->
                <div class="row">
                    <!-- Upload Form on Left -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Upload Berkas</h3>
                            </div>
                            <div class="card-body">
                                <?php if ($mutasi['status_berkas'] == 0): ?>
                                    <form action="warga_masuk_berkas_process.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                                        <input type="hidden" name="id_mutasi" value="<?php echo $id_mutasi; ?>">
                                        <div class="form-group">
                                            <label>Jenis Berkas <span class="text-danger">*</span></label>
                                            <select class="form-control" name="jenis_berkas[]" required>
                                                <?php echo DocumentTypes::getSelectOptions(); ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Nama Berkas <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="nama[]" required>
                                        </div>
                                        <div class="form-group">
                                            <label>File <span class="text-danger">*</span></label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="file[]" required 
                                                       accept=".pdf,.jpg,.jpeg,.png">
                                                <label class="custom-file-label">Pilih file...</label>
                                            </div>
                                            <small class="form-text text-muted">Format: PDF, JPG, JPEG, PNG. Maksimal 2MB</small>
                                        </div>
                                        <div class="form-group mb-0">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <a href="warga_masuk.php" class="btn btn-secondary btn-block">
                                                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                                                    </a>
                                                </div>
                                                <div class="col-md-6">
                                                    <button type="submit" class="btn btn-primary btn-block">
                                                        <i class="fas fa-upload mr-1"></i> Upload Berkas
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle mr-1"></i> Data warga sudah ditambahkan, tidak dapat mengupload berkas baru.
                                    </div>
                                    <div class="mt-3">
                                        <a href="warga_masuk.php" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Files Table on Right -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Daftar Berkas</h3>
                            </div>
                            <div class="card-body">
                                <?php if ($flash_message = getFlashMessage()): ?>
                                    <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show">
                                        <?php echo $flash_message['text']; ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <?php if (mysqli_num_rows($result_files) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Jenis Berkas</th>
                                                <th>Nama Berkas</th>
                                                <th>File</th>
                                                <th>Status</th>
                                                <th width="10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($file = mysqli_fetch_assoc($result_files)): ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y', strtotime($file['tgl_masuk'])); ?></td>
                                                    <td><?php echo DocumentTypes::getName($file['jenis_berkas']); ?></td>
                                                    <td><?php echo htmlspecialchars($file['nama']); ?></td>
                                                    <td>
                                                        <a href="<?php echo $base_url; ?>assets/files/<?php echo $file['file']; ?>" 
                                                           target="_blank" 
                                                           class="btn btn-info btn-sm"
                                                           onclick="window.open(this.href, '_blank'); return false;">
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <?php if ($mutasi['status_berkas'] == 0): ?>
                                                            <?php if ($file['status'] == 0): ?>
                                                                <button type="button" class="btn btn-success btn-sm" 
                                                                        onclick="verifyFile(<?php echo $file['id']; ?>, 1)">
                                                                    <i class="fas fa-check"></i> Verifikasi
                                                                </button>
                                                            <?php elseif ($file['status'] == 1): ?>
                                                                <button type="button" class="btn btn-warning btn-sm" 
                                                                        onclick="verifyFile(<?php echo $file['id']; ?>, 0)">
                                                                    <i class="fas fa-times"></i> Batal Verifikasi
                                                                </button>
                                                            <?php else: ?>
                                                                <span class="badge badge-danger">Tidak Valid</span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <?php if ($file['status'] == 1): ?>
                                                                <span class="badge badge-success">Terverifikasi</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-secondary">Belum Diverifikasi</span>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($mutasi['status_berkas'] == 0 && $file['status'] == 0): // Only show delete for unverified files and when not approved ?>
                                                            <button type="button" class="btn btn-danger btn-sm" 
                                                                    onclick="deleteFile(<?php echo $file['id']; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info">
                                    Belum ada berkas yang diupload
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../template/footer.php'; ?>

<script>
// Initialize custom file input
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    const fileName = e.target.files[0].name;
    const label = e.target.nextElementSibling;
    label.textContent = fileName;
});

function verifyFile(fileId, status) {
    let action = status == 1 ? 'verifikasi' : 'batalkan verifikasi';
    if (confirm(`Apakah Anda yakin ingin ${action} berkas ini?`)) {
        window.location.href = `warga_masuk_berkas_verify.php?id=${fileId}&status=${status}&id_mutasi=<?php echo $id_mutasi; ?>`;
    }
}

function deleteFile(fileId) {
    if (confirm('Apakah Anda yakin ingin menghapus file ini?')) {
        window.location.href = `warga_masuk_berkas_delete.php?id=${fileId}&id_mutasi=<?php echo $id_mutasi; ?>`;
    }
}
</script> 