<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

$settings = getSettings();
$page_title = "Laporan Kas";
$current_page = 'laporan_kas';

// Initialize data array and saldo
$data = [];
$saldo = 0;

// Build query for kas
$query = "SELECT 
    id,
    tgl_masuk as tanggal,
    keterangan,
    CASE WHEN status_kas = 1 THEN nominal ELSE 0 END as kas_masuk,
    CASE WHEN status_kas = 2 THEN nominal ELSE 0 END as kas_keluar
FROM tbl_akt_kas
WHERE tipe = 1"; // tipe 1 = Tagihan Air

// Add date filters if provided
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $start_date = mysqli_real_escape_string($conn, $_GET['start_date']);
    $query .= " AND DATE(tgl_masuk) >= '$start_date'";
}

if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $end_date = mysqli_real_escape_string($conn, $_GET['end_date']);
    $query .= " AND DATE(tgl_masuk) <= '$end_date'";
}

$query .= " ORDER BY tgl_masuk ASC";
$result = mysqli_query($conn, $query);

// Process the results
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}

require_once '../template/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Laporan Kas</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Laporan</li>
                    <li class="breadcrumb-item active">Kas</li>
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
                        <h3 class="card-title">Filter Laporan Kas</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Periode Dari</label>
                                        <input type="date" class="form-control" name="start_date" 
                                               value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Sampai</label>
                                        <input type="date" class="form-control" name="end_date" 
                                               value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="<?php echo $base_url; ?>laporan/kas.php" class="btn btn-default">Reset</a>
                            <a href="<?php echo $base_url; ?>laporan/kas_export.php<?php echo isset($_GET['start_date']) ? '?start_date='.$_GET['start_date'].'&end_date='.$_GET['end_date'] : ''; ?>" 
                               class="btn btn-success float-right">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                        </form>

                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th class="text-right">Kas Masuk</th>
                                    <th class="text-right">Kas Keluar</th>
                                    <th class="text-right">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($data as $row):
                                    $saldo += $row['kas_masuk'] - $row['kas_keluar'];
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                    <td class="text-right"><?php echo number_format($row['kas_masuk'], 0, ',', '.'); ?></td>
                                    <td class="text-right"><?php echo number_format($row['kas_keluar'], 0, ',', '.'); ?></td>
                                    <td class="text-right"><?php echo number_format($saldo, 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($data)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Total:</th>
                                    <th class="text-right"><?php echo number_format(array_sum(array_column($data, 'kas_masuk')), 0, ',', '.'); ?></th>
                                    <th class="text-right"><?php echo number_format(array_sum(array_column($data, 'kas_keluar')), 0, ',', '.'); ?></th>
                                    <th class="text-right"><?php echo number_format($saldo, 0, ',', '.'); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../template/footer.php'; ?>