<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();

// Check if user is logged in
checkLogin();

$settings = getSettings();
$page_title = "Data Warga";

// Get search term
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get pagination data with search
$limit = isset($settings['pagination_limit']) && $settings['pagination_limit'] > 0 ? 
        (int)$settings['pagination_limit'] : 10;

// Update the search query to include status filter
$where = '';
if (isset($_GET['status']) && in_array($_GET['status'], ['1', '2'])) {
    $where = "status_rumah = '" . mysqli_real_escape_string($conn, $_GET['status']) . "'";
}

$pagination = getPaginationSearch(
    'tbl_m_warga', 
    $limit,
    $search,
    ['kk', 'nama', 'blok', 'status_rumah'],
    'created_at DESC',
    $where
);

require_once '../template/header.php';
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-12 text-center">
                <h2 class="m-0">
                    <?php echo isset($settings['judul']) ? $settings['judul'] : 'Pendataan Warga'; ?>
                </h2>
                <p class="text-muted">
                    <?php echo isset($settings['judul_app']) ? $settings['judul_app'] : 'PAM Warga'; ?>
                </p>
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
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <?php if (in_array($_SESSION['user']['role'], ['superadmin', 'pengurus'])): ?>
                                <a href="warga_add.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Warga
                                </a>
                                <?php endif; ?>
                            </div>
                            <div class="card-tools">
                                <form action="" method="GET">
                                    <div class="input-group input-group-sm" style="width: 250px;">
                                        <input type="text" name="search" class="form-control float-right" 
                                               placeholder="Search warga..." value="<?php echo htmlspecialchars($search); ?>">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <?php if ($search): ?>
                                            <a href="?" class="btn btn-default border-left-0">
                                                <i class="fas fa-times"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <?php if ($flash_message = getFlashMessage()): ?>
                        <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show mx-3 mt-3">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <?php echo $flash_message['text']; ?>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No. KK</th>
                                    <th>Nama</th>
                                    <th>No WhatsApp</th>
                                    <th>Blok</th>
                                    <th>Status Rumah</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Lama Tinggal</th>
                                    <?php if (in_array($_SESSION['user']['role'], ['superadmin', 'pengurus'])): ?>
                                    <th>Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = $pagination['start'] + 1;
                                while ($row = mysqli_fetch_assoc($pagination['result'])) {
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <a href="warga_detail.php?id=<?php echo $row['id']; ?>" 
                                               class="text-primary" 
                                               title="Lihat detail warga">
                                                <?php echo $row['kk']; ?>
                                            </a>
                                        </td>
                                        <td><?php echo $row['nama']; ?></td>
                                        <td><?php echo $row['no_hp'] ? '+62' . htmlspecialchars($row['no_hp']) : '-'; ?></td>
                                        <td><?php echo $row['blok']; ?></td>
                                        <td><?php echo $row['status_rumah'] == '1' ? 'Sendiri' : 'Kontrak'; ?></td>
                                        <td><?php echo formatTanggal($row['tgl_masuk']); ?></td>
                                        <td><?php echo calculateDuration($row['tgl_masuk']); ?></td>
                                        <?php if (in_array($_SESSION['user']['role'], ['superadmin', 'pengurus'])): ?>
                                        <td>
                                            <a href="warga_edit.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php
                                            // Check if user account exists for this warga
                                            $user_exists = false;
                                            $check_user_sql = "SELECT id FROM tbl_users WHERE id_warga = ?";
                                            if ($stmt = mysqli_prepare($conn, $check_user_sql)) {
                                                mysqli_stmt_bind_param($stmt, "i", $row['id']);
                                                mysqli_stmt_execute($stmt);
                                                $user_exists = mysqli_stmt_fetch($stmt);
                                                mysqli_stmt_close($stmt);
                                            }
                                            
                                            if (!$user_exists): // Only show create user button if no user account exists
                                            ?>
                                            <a href="warga_create_user.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-info btn-sm"
                                               title="Buat Akun User"
                                               onclick="return confirm('Buat akun user untuk warga ini? Username: <?php echo $row['nik']; ?>, Password: password');">
                                                <i class="fas fa-user-plus"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="warga_delete.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-danger btn-sm delete-btn" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <!-- Pagination with search parameter -->
                        <?php 
                        $url_pattern = '?page=%d' . ($search ? '&search=' . urlencode($search) : '');
                        echo getPaginationLinks(
                            $pagination['page'], 
                            $pagination['total_pages'],
                            $pagination['limit'],
                            $pagination['total_records'],
                            $url_pattern
                        ); 
                        ?>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>

<?php require_once '../template/footer.php'; ?> 