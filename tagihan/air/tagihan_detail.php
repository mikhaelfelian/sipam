<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';
require_once '../../library/general.php';

startSession();
checkLogin();

$page_title = "Detail Tagihan Air";
$current_page = 'tagihan_air';

// Get tagihan ID from URL
$id_tagihan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get tagihan data
$query = "SELECT t.*, w.nama, w.blok, w.no_hp, u.username as pencatat
          FROM tbl_trx_air t 
          JOIN tbl_m_warga w ON t.id_warga = w.id 
          LEFT JOIN tbl_users u ON t.created_by = u.id 
          WHERE t.id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_tagihan);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$tagihan = mysqli_fetch_assoc($result);

// Check if data exists and user has access
if (!$tagihan || ($_SESSION['user']['role'] === 'warga' && $tagihan['id_warga'] != $_SESSION['user']['id_warga'])) {
    setFlashMessage('danger', 'Data tagihan tidak ditemukan');
    header("Location: tagihan.php");
    exit();
}

require_once '../../template/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Detail Tagihan Air</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Tagihan Air</li>
                    <li class="breadcrumb-item"><a href="tagihan.php">Tagihan</a></li>
                    <li class="breadcrumb-item active">Detail</li>
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
                <h3 class="card-title">Detail Tagihan</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered">
                    <tr>
                        <th style="width: 200px">Nama Warga</th>
                        <td><?php echo htmlspecialchars($tagihan['nama']); ?></td>
                    </tr>
                    <tr>
                        <th>Blok</th>
                        <td><?php echo htmlspecialchars($tagihan['blok']); ?></td>
                    </tr>
                    <tr>
                        <th>No WhatsApp</th>
                        <td>
                            <?php if (!empty($tagihan['no_hp'])): ?>
                                +62<?php echo htmlspecialchars($tagihan['no_hp']); ?>
                                <a href="https://wa.me/62<?php echo $tagihan['no_hp']; ?>" target="_blank" 
                                   class="btn btn-success btn-sm ml-2">
                                    <i class="fab fa-whatsapp mr-1"></i> Chat WhatsApp
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Periode</th>
                        <td><?php echo getMonthName($tagihan['bulan']) . ' ' . $tagihan['tahun']; ?></td>
                    </tr>
                    <tr>
                        <th>Meter Awal</th>
                        <td><?php echo formatNumber($tagihan['meter_awal']); ?> m³</td>
                    </tr>
                    <tr>
                        <th>Meter Akhir</th>
                        <td><?php echo formatNumber($tagihan['meter_akhir']); ?> m³</td>
                    </tr>
                    <tr>
                        <th>Pemakaian</th>
                        <td><?php echo formatNumber($tagihan['pemakaian']); ?> m³</td>
                    </tr>
                    <tr>
                        <th>Total Tagihan</th>
                        <td>Rp <?php echo formatNumber($tagihan['pemakaian'] * $settings['tarif_air']); ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <?php
                            switch ($tagihan['status_bayar']) {
                                case 1:
                                    echo '<span class="badge badge-success">Lunas</span>';
                                    break;
                                case 2:
                                    echo '<span class="badge badge-warning">Kurang Bayar</span>';
                                    break;
                                default:
                                    echo '<span class="badge badge-danger">Belum Bayar</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Dicatat Oleh</th>
                        <td><?php echo htmlspecialchars($tagihan['pencatat']); ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Catat</th>
                        <td><?php echo date('d/m/Y H:i', strtotime($tagihan['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="tagihan.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <?php if (in_array($_SESSION['user']['role'], ['superadmin', 'pengurus'])): ?>
                        <div>
                            <?php if ($tagihan['status_bayar'] != 1): ?>
                                <a href="bayar.php?id=<?php echo $tagihan['id']; ?>" class="btn btn-success mr-2">
                                    <i class="fas fa-money-bill-wave mr-1"></i> Bayar
                                </a>
                            <?php endif; ?>
                            <a href="print_bill.php?id=<?php echo $tagihan['id']; ?>" 
                               class="btn btn-primary" target="_blank">
                                <i class="fas fa-print mr-1"></i> Cetak Tagihan
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../../template/footer.php'; ?> 