<?php
require_once 'config/connect.php';
require_once 'config/config.php';
require_once 'config/auth.php';

startSession();

// Check if user is logged in
checkLogin();

$settings = getSettings();
$page_title = "Dashboard";

// Get warga counts
$total_warga = getTotalWarga($conn);
$warga_sendiri = getWargaByStatus($conn, 1);
$warga_kontrak = getWargaByStatus($conn, 2);

// Get unpaid water bills count
$query_tagihan = "SELECT 
    SUM(CASE WHEN status_bayar = 0 THEN 1 ELSE 0 END) as total_belum_bayar,
    SUM(CASE WHEN status_bayar = 1 THEN 1 ELSE 0 END) as total_lunas
FROM tbl_trx_air";
$result_tagihan = mysqli_query($conn, $query_tagihan);
$tagihan = mysqli_fetch_assoc($result_tagihan);
$tagihan_belum_bayar = $tagihan['total_belum_bayar'];
$tagihan_lunas = $tagihan['total_lunas'];

// Get chart data
$query_kas = "SELECT 
    DATE_FORMAT(tgl_masuk, '%Y-%m') as bulan,
    SUM(nominal) as total_masuk
FROM tbl_akt_kas 
WHERE tipe = 1 AND status_kas = 1
GROUP BY DATE_FORMAT(tgl_masuk, '%Y-%m')
ORDER BY bulan DESC
LIMIT 6";
$result_kas = mysqli_query($conn, $query_kas);
if (!$result_kas) {
    die("Error in query: " . mysqli_error($conn));
}
$kas_data = array_reverse(mysqli_fetch_all($result_kas, MYSQLI_ASSOC));

// Prepare data for chart
$labels = [];
$values = [];
foreach ($kas_data as $data) {
    $labels[] = date('M Y', strtotime($data['bulan']));
    $values[] = floatval($data['total_masuk']);
}

// Format numbers for display in boxes
$formatted_total_masuk = 'Rp ' . number_format(array_sum($values), 0, ',', '.');

require_once 'template/header.php';
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Add welcome message -->
<section class="content">
    <div class="container-fluid">
        <div class="alert alert-info">
            <h5><i class="icon fas fa-info"></i> Selamat Datang!</h5>
            <p class="mb-0">
                <?php 
                $greeting = '';
                $hour = date('H');
                if ($hour >= 5 && $hour < 12) {
                    $greeting = 'Pagi';
                } elseif ($hour >= 12 && $hour < 15) {
                    $greeting = 'Siang';
                } elseif ($hour >= 15 && $hour < 18) {
                    $greeting = 'Sore';
                } else {
                    $greeting = 'Malam';
                }
                echo "Selamat $greeting, " . htmlspecialchars($_SESSION['user']['nik']) . "!";
                ?>
            </p>
        </div>

        <!-- Small boxes (Stat box) - First Row -->
        <div class="row">
            <div class="col-lg-4 col-12">
                <!-- small box -->
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo number_format($total_warga); ?></h3>
                        <p>Total Warga</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person"></i>
                    </div>
                    <a href="<?php echo $base_url; ?>master/warga.php" class="small-box-footer">
                        Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-4 col-12">
                <!-- small box -->
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo number_format($warga_sendiri); ?></h3>
                        <p>Rumah Sendiri</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-home"></i>
                    </div>
                    <a href="<?php echo $base_url; ?>master/warga.php?status=1" class="small-box-footer">
                        Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-4 col-12">
                <!-- small box -->
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php echo number_format($warga_kontrak); ?></h3>
                        <p>Rumah Kontrak</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-key"></i>
                    </div>
                    <a href="<?php echo $base_url; ?>master/warga.php?status=2" class="small-box-footer">
                        Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Small boxes (Stat box) - Second Row -->
        <div class="row">
            <div class="col-lg-4 col-12">
                <!-- small box for unpaid bills -->
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?php echo number_format($tagihan_belum_bayar); ?></h3>
                        <p>Tagihan Air Belum Lunas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tint"></i>
                    </div>
                    <a href="<?php echo $base_url; ?>tagihan/air/tagihan.php?filter_status=0" class="small-box-footer">
                        Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-4 col-12">
                <!-- small box for paid bills -->
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo number_format($tagihan_lunas); ?></h3>
                        <p>Tagihan Air Lunas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <a href="<?php echo $base_url; ?>tagihan/air/tagihan.php?filter_status=1" class="small-box-footer">
                        Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- After the small boxes sections, add this -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-1"></i>
                            Grafik Pendapatan Air 6 Bulan Terakhir
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="kasChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'template/footer.php'; ?>

<!-- Add this JavaScript before closing body tag -->
<script src="<?php echo $base_url; ?>assets/theme/admin-lte-3/plugins/chart.js/Chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart configuration
    var ctx = document.getElementById('kasChart').getContext('2d');
    var kasChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Uang Masuk',
                data: <?php echo json_encode($values); ?>,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 2
                            }).format(value);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 2
                            }).format(context.raw);
                        }
                    }
                }
            }
        }
    });
});
</script>