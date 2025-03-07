<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/auth.php';

startSession();
checkLogin();

$page_title = "Data Warga Masuk";

// Get filters
$filter_tanggal = isset($_GET['filter_tanggal']) ? $_GET['filter_tanggal'] : '';
$filter_nik = isset($_GET['filter_nik']) ? $_GET['filter_nik'] : '';
$filter_blok = isset($_GET['filter_blok']) ? $_GET['filter_blok'] : '';

// Build WHERE clause
$where = "WHERE jenis_mutasi = '1'";
if (!empty($filter_tanggal)) {
    $where .= " AND DATE(tgl_masuk) = '" . mysqli_real_escape_string($conn, $filter_tanggal) . "'";
}
if (!empty($filter_nik)) {
    $where .= " AND nik LIKE '%" . mysqli_real_escape_string($conn, $filter_nik) . "%'";
}
if (!empty($filter_blok)) {
    $where .= " AND blok LIKE '%" . mysqli_real_escape_string($conn, $filter_blok) . "%'";
}

// Main query
$query = "SELECT id, tgl_masuk, nik, nama, blok, status_rumah, status_berkas, jenis_mutasi 
          FROM tbl_trx_mutasi_warga 
          {$where} 
          ORDER BY tgl_masuk DESC";

$result = mysqli_query($conn, $query);
$rows = [];
while ($row = mysqli_fetch_object($result)) {
    $rows[] = $row;
}

require_once '../template/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?php echo $page_title; ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Mutasi</li>
                    <li class="breadcrumb-item active">Warga Masuk</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Warga Masuk</h3>
                <div class="card-tools">
                    <a href="warga_masuk_form.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Tambah
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal Masuk</th>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Blok</th>
                                <th>Status Rumah</th>
                                <th>Status Berkas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rows)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1;
                                foreach ($rows as $row): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row->tgl_masuk)); ?></td>
                                        <td><?php echo htmlspecialchars($row->nik); ?></td>
                                        <td><?php echo htmlspecialchars($row->nama); ?></td>
                                        <td><?php echo htmlspecialchars($row->blok); ?></td>
                                        <td><?php echo $row->status_rumah == '1' ? 'Sendiri' : 'Kontrak'; ?></td>
                                        <td>
                                            <?php if ($row->status_berkas == 1): ?>
                                                <span class="badge badge-success">Lengkap</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Belum Lengkap</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="warga_masuk_berkas.php?id=<?php echo $row->id; ?>" 
                                               class="btn btn-info btn-sm">
                                                <i class="fas fa-file-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../template/footer.php'; ?> 