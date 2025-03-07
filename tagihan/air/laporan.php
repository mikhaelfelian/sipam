<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

$settings = getSettings();
$page_title = "Laporan Tagihan Air";
$current_page = 'laporan_tagihan';

require_once '../../template/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Laporan Tagihan Air</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Tagihan Air</li>
                    <li class="breadcrumb-item active">Laporan</li>
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
                        <h3 class="card-title">Filter Laporan</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Warga</label>
                                        <select class="form-control select2" name="filter_warga">
                                            <option value="">Semua Warga</option>
                                            <?php
                                            $query_warga = "SELECT DISTINCT w.id, w.nama, w.blok 
                                                   FROM tbl_m_warga w 
                                                   JOIN tbl_trx_air t ON w.id = t.id_warga 
                                                   ORDER BY w.nama";
                                            $result_warga = mysqli_query($conn, $query_warga);
                                            while ($warga = mysqli_fetch_assoc($result_warga)):
                                                $selected = (isset($_GET['filter_warga']) && $_GET['filter_warga'] == $warga['id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?php echo $warga['id']; ?>" <?php echo $selected; ?>>
                                                    <?php echo htmlspecialchars($warga['nama'] . ' - Blok ' . $warga['blok']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Bulan</label>
                                        <select class="form-control" name="filter_bulan">
                                            <option value="">Semua Bulan</option>
                                            <?php
                                            $bulan_list = [
                                                '01' => 'Januari',
                                                '02' => 'Februari',
                                                '03' => 'Maret',
                                                '04' => 'April',
                                                '05' => 'Mei',
                                                '06' => 'Juni',
                                                '07' => 'Juli',
                                                '08' => 'Agustus',
                                                '09' => 'September',
                                                '10' => 'Oktober',
                                                '11' => 'November',
                                                '12' => 'Desember'
                                            ];
                                            foreach ($bulan_list as $value => $label):
                                                $selected = (isset($_GET['filter_bulan']) && $_GET['filter_bulan'] == $value) ? 'selected' : '';
                                            ?>
                                                <option value="<?php echo $value; ?>" <?php echo $selected; ?>>
                                                    <?php echo $label; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tahun</label>
                                        <select class="form-control" name="filter_tahun">
                                            <option value="">Semua Tahun</option>
                                            <?php
                                            $tahun_sekarang = date('Y');
                                            for ($tahun = $tahun_sekarang; $tahun >= 2020; $tahun--):
                                                $selected = (isset($_GET['filter_tahun']) && $_GET['filter_tahun'] == $tahun) ? 'selected' : '';
                                            ?>
                                                <option value="<?php echo $tahun; ?>" <?php echo $selected; ?>>
                                                    <?php echo $tahun; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select class="form-control" name="filter_status">
                                            <option value="">Semua Status</option>
                                            <option value="0" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === '0') ? 'selected' : ''; ?>>Belum Bayar</option>
                                            <option value="1" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === '1') ? 'selected' : ''; ?>>Lunas</option>
                                            <option value="2" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === '2') ? 'selected' : ''; ?>>Kurang Bayar</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="<?php echo $base_url; ?>tagihan/air/laporan.php" class="btn btn-default">Reset</a>
                            <button type="button" class="btn btn-success" onclick="exportToExcel()">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                            <button type="button" class="btn btn-danger" onclick="exportToPDF()">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                        </form>

                        <!-- Table -->
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Warga</th>
                                    <th>Blok</th>
                                    <th>Periode</th>
                                    <th>Meter Awal</th>
                                    <th>Meter Akhir</th>
                                    <th>Pemakaian</th>
                                    <th>Total Tagihan</th>
                                    <th>Total Bayar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT t.*, w.nama, w.blok, 
                                          COALESCE((
                                              SELECT meter_akhir 
                                              FROM tbl_trx_air prev
                                              WHERE prev.id_warga = t.id_warga 
                                              AND (
                                                  (prev.tahun = t.tahun AND prev.bulan < t.bulan)
                                                  OR 
                                                  (prev.tahun < t.tahun)
                                              )
                                              AND prev.id != t.id
                                              ORDER BY prev.tahun DESC, prev.bulan DESC 
                                              LIMIT 1
                                          ), t.meter_awal) as meter_sebelumnya,
                                          COALESCE((
                                              SELECT SUM(jumlah_bayar) 
                                              FROM tbl_trx_air_pembayaran 
                                              WHERE id_trx_air = t.id
                                          ), 0) as total_bayar
                                          FROM tbl_trx_air t 
                                          JOIN tbl_m_warga w ON t.id_warga = w.id
                                          WHERE 1=1";

                                if (isset($_GET['filter_warga']) && $_GET['filter_warga'] !== '') {
                                    $warga_id = mysqli_real_escape_string($conn, $_GET['filter_warga']);
                                    $query .= " AND t.id_warga = '$warga_id'";
                                }

                                if (isset($_GET['filter_bulan']) && $_GET['filter_bulan'] !== '') {
                                    $bulan = mysqli_real_escape_string($conn, $_GET['filter_bulan']);
                                    $query .= " AND t.bulan = '$bulan'";
                                }
                                
                                if (isset($_GET['filter_tahun']) && $_GET['filter_tahun'] !== '') {
                                    $tahun = mysqli_real_escape_string($conn, $_GET['filter_tahun']);
                                    $query .= " AND t.tahun = '$tahun'";
                                }

                                if (isset($_GET['filter_status']) && $_GET['filter_status'] !== '') {
                                    $status = mysqli_real_escape_string($conn, $_GET['filter_status']);
                                    $query .= " AND t.status_bayar = '$status'";
                                }
                                
                                $query .= " ORDER BY t.tahun DESC, t.bulan DESC, w.nama ASC";
                                $result = mysqli_query($conn, $query);

                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)):
                                    $meter_awal = number_format($row['meter_sebelumnya'] ?? 0, 0, '', '.');
                                    $meter_akhir = number_format($row['meter_akhir'], 0, '', '.');
                                    $pemakaian = number_format($row['pemakaian'], 0, '', '.');
                                    $total_tagihan = number_format($row['total_tagihan'], 0, '', '.');
                                    $total_bayar = number_format($row['total_bayar'], 0, '', '.');
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($row['blok']); ?></td>
                                    <td><?php echo $row['bulan'] . '/' . $row['tahun']; ?></td>
                                    <td class="text-right"><?php echo $meter_awal; ?> m³</td>
                                    <td class="text-right"><?php echo $meter_akhir; ?> m³</td>
                                    <td class="text-right"><?php echo $pemakaian; ?> m³</td>
                                    <td class="text-right">Rp <?php echo $total_tagihan; ?></td>
                                    <td class="text-right">Rp <?php echo $total_bayar; ?></td>
                                    <td>
                                        <?php 
                                        switch($row['status_bayar']) {
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
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add this after the table and before the export scripts -->
<script src="<?php echo $base_url_style; ?>plugins/jquery/jquery.min.js"></script>
<script src="<?php echo $base_url_style; ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Then your existing export scripts -->
<script>
$(document).ready(function() {
    // Initialize select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });
});

function exportToExcel() {
    // Get current filter parameters
    var urlParams = new URLSearchParams(window.location.search);
    var filterParams = '';
    if (urlParams.toString()) {
        filterParams = '?' + urlParams.toString();
    }
    
    // Redirect to export script with current filters
    window.location.href = 'export_laporan.php' + filterParams;
}

function exportToPDF() {
    // Get current filter parameters
    var urlParams = new URLSearchParams(window.location.search);
    var filterParams = '';
    if (urlParams.toString()) {
        filterParams = '?' + urlParams.toString();
    }
    
    // Redirect to PDF export script with current filters
    window.location.href = 'export_pdf.php' + filterParams;
}
</script>

<?php require_once '../../template/footer.php'; ?> 