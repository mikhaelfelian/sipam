<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

$settings = getSettings();
$page_title = "Data Platform Pembayaran";
$current_page = 'platform';

require_once '../template/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Data Platform Pembayaran</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Master Data</li>
                    <li class="breadcrumb-item active">Platform Pembayaran</li>
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
                        <h3 class="card-title">Daftar Platform Pembayaran</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-tambah">
                                <i class="fas fa-plus"></i> Tambah Platform
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($flash_message = getFlashMessage()): ?>
                            <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show" role="alert">
                                <?php echo $flash_message['text']; ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Platform Pembayaran</th>
                                    <th>Keterangan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM tbl_m_platform ORDER BY platform";
                                $result = mysqli_query($conn, $query);

                                // Add error checking
                                if (!$result) {
                                    die("Query failed: " . mysqli_error($conn));
                                }

                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['platform']); ?></td>
                                    <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                    <td>
                                        <?php if ($row['status'] == 1): ?>
                                            <span class="badge badge-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-warning btn-sm btn-edit" 
                                                    data-id="<?php echo $row['id']; ?>"
                                                    data-platform="<?php echo htmlspecialchars($row['platform']); ?>"
                                                    data-keterangan="<?php echo htmlspecialchars($row['keterangan']); ?>"
                                                    data-status="<?php echo $row['status']; ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <a href="platform_delete.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-danger btn-sm btn-delete"
                                               data-id="<?php echo $row['id']; ?>"
                                               data-platform="<?php echo htmlspecialchars($row['platform']); ?>">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Tambah -->
<div class="modal fade" id="modal-tambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Platform Pembayaran</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="platform_save.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="platform">Platform Pembayaran</label>
                        <input type="text" class="form-control" id="platform" name="platform" required>
                    </div>
                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modal-edit">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Platform Pembayaran</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="platform_update.php" method="POST">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit-platform">Platform Pembayaran</label>
                        <input type="text" class="form-control" id="edit-platform" name="platform" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-keterangan">Keterangan</label>
                        <textarea class="form-control" id="edit-keterangan" name="keterangan" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-status">Status</label>
                        <select class="form-control" id="edit-status" name="status" required>
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function() {
    // Handle edit button
    $('.btn-edit').click(function() {
        var id = $(this).data('id');
        var platform = $(this).data('platform');
        var keterangan = $(this).data('keterangan');
        var status = $(this).data('status');

        $('#edit-id').val(id);
        $('#edit-platform').val(platform);
        $('#edit-keterangan').val(keterangan);
        $('#edit-status').val(status);

        $('#modal-edit').modal('show');
    });

    // Handle delete button
    $('.btn-delete').click(function() {
        var id = $(this).data('id');
        var platform = $(this).data('platform');
        
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Platform pembayaran '" + platform + "' akan dihapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'platform_delete.php?id=' + id;
            }
        });
    });
});
</script>

<style>
.table {
    width: 100%;
    margin-bottom: 1rem;
    background-color: transparent;
}

.table th,
.table td {
    padding: 0.75rem;
    vertical-align: middle;
    border: 1px solid #dee2e6;
}

.table thead th {
    vertical-align: bottom;
    background-color: #f4f6f9;
    border-bottom: 2px solid #dee2e6;
}

.table tbody tr:hover {
    background-color: rgba(0,0,0,.075);
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
}

.badge {
    display: inline-block;
    padding: 0.25em 0.4em;
    font-size: 75%;
    font-weight: 700;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.25rem;
}

.badge-success {
    color: #fff;
    background-color: #28a745;
}

.badge-danger {
    color: #fff;
    background-color: #dc3545;
}

.btn-group-sm>.btn, .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}

.btn-warning {
    color: #1f2d3d;
    background-color: #ffc107;
    border-color: #ffc107;
}

.btn-danger {
    color: #fff;
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn i {
    margin-right: 0.3rem;
}
</style>

<?php require_once '../template/footer.php'; ?> 