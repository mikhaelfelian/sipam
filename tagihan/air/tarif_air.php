<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

$settings = getSettings();
$page_title = "Tarif Air";
$current_page = 'tarif_air';

require_once '../../template/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Tarif Air</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Tagihan Air</li>
                    <li class="breadcrumb-item active">Tarif Air</li>
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
                        <a href="tarif_air_add.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Tarif
                        </a>
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
                                    <th>Range Pemakaian</th>
                                    <th>Biaya per m³</th>
                                    <th>Biaya Maintenance</th>
                                    <th>Biaya Admin</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM tbl_m_air_tarif ORDER BY created_at DESC";
                                $result = mysqli_query($conn, $query);
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['range_pemakaian']); ?></td>
                                    <td>Rp <?php echo number_format($row['biaya_m3'], 0, ',', '.'); ?></td>
                                    <td>Rp <?php echo number_format($row['biaya_mtc'], 0, ',', '.'); ?></td>
                                    <td>Rp <?php echo number_format($row['biaya_adm'], 0, ',', '.'); ?></td>
                                    <td>
                                        <a href="tarif_air_edit.php?id=<?php echo $row['id']; ?>" 
                                           class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="tarif_air_delete.php?id=<?php echo $row['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus tarif ini?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
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

<?php require_once '../../template/footer.php'; ?> 