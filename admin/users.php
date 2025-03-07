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
$page_title = "Manajemen Pengguna";

// Get search term
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get pagination data with search
$limit = isset($settings['pagination_limit']) && $settings['pagination_limit'] > 0 ? 
        (int)$settings['pagination_limit'] : 10;

$pagination = getPaginationSearch(
    'tbl_users', 
    $limit,
    $search,
    ['username', 'role'],
    'created_at DESC'
);

require_once '../template/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Manajemen Pengguna</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Pengaturan</li>
                    <li class="breadcrumb-item active">Pengguna</li>
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
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="user_add.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Pengguna
                                </a>
                            </div>
                            <div class="card-tools">
                                <form action="" method="GET">
                                    <div class="input-group input-group-sm" style="width: 250px;">
                                        <input type="text" name="search" class="form-control float-right" 
                                               placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($flash_message = getFlashMessage()): ?>
                            <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?php echo $flash_message['text']; ?>
                            </div>
                        <?php endif; ?>

                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Warga</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = $pagination['start'] + 1;
                                while ($row = mysqli_fetch_assoc($pagination['result'])) {
                                    // Get warga name if id_warga exists
                                    $warga_name = '-';
                                    if ($row['id_warga']) {
                                        $stmt = mysqli_prepare($conn, "SELECT nama FROM tbl_m_warga WHERE id = ?");
                                        mysqli_stmt_bind_param($stmt, "i", $row['id_warga']);
                                        mysqli_stmt_execute($stmt);
                                        $warga_result = mysqli_stmt_get_result($stmt);
                                        if ($warga = mysqli_fetch_assoc($warga_result)) {
                                            $warga_name = $warga['nama'];
                                        }
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo ucfirst($row['role']); ?></td>
                                        <td><?php echo htmlspecialchars($warga_name); ?></td>
                                        <td>
                                            <a href="user_edit.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($row['username'] !== 'admin'): ?>
                                            <a href="user_delete.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <?php 
                        echo getPaginationLinks(
                            $pagination['page'], 
                            $pagination['total_pages'],
                            $pagination['limit'],
                            $pagination['total_records'],
                            '?page=%d' . ($search ? '&search=' . urlencode($search) : '')
                        ); 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../template/footer.php'; ?> 