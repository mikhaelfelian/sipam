<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';
require_once '../../library/general.php';

startSession();
checkLogin();

$page_title = "Tagihan Air";
$current_page = 'tagihan_air';

// Check if tables have data
$check_query = "SELECT COUNT(*) as count FROM tbl_trx_air";
$check_result = mysqli_query($conn, $check_query);
$air_count = mysqli_fetch_assoc($check_result)['count'];
echo "<!-- tbl_trx_air count: $air_count -->";

// Base query with default filter for unpaid bills
$query = "SELECT t.*, w.nama, w.blok, u.username as pencatat
          FROM tbl_trx_air t 
          JOIN tbl_m_warga w ON t.id_warga = w.id 
          LEFT JOIN tbl_users u ON t.created_by = u.id 
          WHERE 1=1"; // Changed from WHERE t.status_bayar = 0

// Add role-based filter for warga
if ($_SESSION['user']['role'] === 'warga') {
    $query .= " AND t.id_warga = '" . $_SESSION['user']['id_warga'] . "'";
} else {
    // For non-warga users, show only unpaid by default unless filtered
    if (!isset($_GET['status_bayar'])) {
        $query .= " AND t.status_bayar = 0";
    }
}

// Add filter for status if explicitly set
if (isset($_GET['status_bayar'])) {
    $status_bayar = mysqli_real_escape_string($conn, $_GET['status_bayar']);
    $query .= " AND t.status_bayar = '$status_bayar'";
}

// Add month filter if set
if (isset($_GET['filter_bulan']) && $_GET['filter_bulan'] !== '') {
    $bulan = mysqli_real_escape_string($conn, $_GET['filter_bulan']);
    $query .= " AND t.bulan = '$bulan'";
}

// Add year filter if set
if (isset($_GET['filter_tahun']) && $_GET['filter_tahun'] !== '') {
    $tahun = mysqli_real_escape_string($conn, $_GET['filter_tahun']);
    $query .= " AND t.tahun = '$tahun'";
}

$query .= " ORDER BY t.created_at DESC";

// Debug query
echo "<!-- Debug Query: " . $query . " -->";

$result = mysqli_query($conn, $query);

// Debug result
if (!$result) {
    echo "<!-- Query Error: " . mysqli_error($conn) . " -->";
} else {
    echo "<!-- Number of rows: " . mysqli_num_rows($result) . " -->";
}

require_once '../../template/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Tagihan Air</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Tagihan Air</li>
                    <li class="breadcrumb-item active">Tagihan</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Tabel Riwayat Pencatatan -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tagihan Meter Air</h3>
            </div>
            <div class="card-body">
                <!-- Add filter form -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Bulan</label>
                                <select class="form-control" name="filter_bulan">
                                    <option value="">Semua Bulan</option>
                                    <?php
                                    $bulan_list = getMonthList();
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tahun</label>
                                <select class="form-control" name="filter_tahun">
                                    <option value="">Semua Tahun</option>
                                    <?php
                                    foreach (getYearOptions() as $tahun):
                                        $selected = (isset($_GET['filter_tahun']) && $_GET['filter_tahun'] == $tahun) ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $tahun; ?>" <?php echo $selected; ?>>
                                            <?php echo $tahun; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="catat_meter.php" class="btn btn-default">
                                        <i class="fas fa-sync"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Update the query with filters -->
                <?php
                $query = "SELECT t.*, w.nama, w.blok, u.username as pencatat
                                  FROM tbl_trx_air t 
                                  JOIN tbl_m_warga w ON t.id_warga = w.id 
                                  LEFT JOIN tbl_users u ON t.created_by = u.id 
                                  WHERE 1=1";

                // Add role-based filter for warga
                if ($_SESSION['user']['role'] === 'warga') {
                    $query .= " AND t.id_warga = '" . $_SESSION['user']['id_warga'] . "'";
                }

                // Add month filter if set
                if (isset($_GET['filter_bulan']) && $_GET['filter_bulan'] !== '') {
                    $bulan = mysqli_real_escape_string($conn, $_GET['filter_bulan']);
                    $query .= " AND t.bulan = '$bulan'";
                }

                if (isset($_GET['filter_tahun']) && $_GET['filter_tahun'] !== '') {
                    $tahun = mysqli_real_escape_string($conn, $_GET['filter_tahun']);
                    $query .= " AND t.tahun = '$tahun'";
                }

                // Debug the query and session
                echo "<!-- Debug: User Role = " . $_SESSION['user']['role'] . " -->";
                echo "<!-- Debug: User ID Warga = " . $_SESSION['user']['id_warga'] . " -->";
                echo "<!-- Debug: Final Query = " . $query . " -->";

                $query .= " ORDER BY t.created_at DESC";
                $result = mysqli_query($conn, $query);

                // Debug the result
                if (!$result) {
                    echo "<!-- Query Error: " . mysqli_error($conn) . " -->";
                } else {
                    echo "<!-- Number of rows: " . mysqli_num_rows($result) . " -->";
                }
                ?>

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Warga</th>
                            <th>Blok</th>
                            <th>Meter Awal</th>
                            <th>Meter Akhir</th>
                            <th>Pemakaian</th>
                            <th>Periode</th>
                            <th>Dicatat Oleh</th>
                            <th>Status</th>
                            <?php if (in_array($_SESSION['user']['role'], ['superadmin', 'pengurus'])): ?>
                                <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)):
                            // Format numbers with Indonesian format - no comma
                            $meter_awal = formatNumber($row['meter_awal']);
                            $meter_akhir = formatNumber($row['meter_akhir']);
                            $pemakaian = formatNumber($row['pemakaian']);
                            $total_tagihan = $row['pemakaian'] * $settings['tarif_air'];
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <a href="tagihan_detail.php?id=<?php echo $row['id']; ?>" class="text-dark">
                                        <?php echo htmlspecialchars($row['nama']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($row['blok']); ?></td>
                                <td class="text-right"><?php echo $meter_awal; ?></td>
                                <td class="text-right"><?php echo $meter_akhir; ?></td>
                                <td class="text-right"><?php echo $pemakaian; ?></td>
                                <td><?php echo $row['bulan'] . '/' . $row['tahun']; ?></td>
                                <td><?php echo htmlspecialchars($row['pencatat']); ?></td>
                                <td>
                                    <?php
                                    switch ($row['status_bayar']) {
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
                                <?php if (in_array($_SESSION['user']['role'], ['superadmin', 'pengurus'])): ?>
                                    <td>
                                        <div class="btn-group">
                                            <?php if ($row['status_bayar'] != 1): ?>
                                                <a href="bayar.php?id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-success btn-sm">
                                                    <i class="fas fa-money-bill-wave"></i> 
                                                    <?php echo ($row['status_bayar'] == 2) ? 'Lunasi' : 'Bayar'; ?>
                                                </a>
                                                
                                                <?php if (in_array($_SESSION['user']['role'], ['superadmin', 'pengurus'])): ?>
                                                    <a href="#" 
                                                       class="btn btn-danger btn-sm btn-delete"
                                                       data-toggle="modal" 
                                                       data-target="#modal-delete"
                                                       data-id="<?php echo $row['id']; ?>"
                                                       data-nama="<?php echo htmlspecialchars($row['nama']); ?>"
                                                       data-periode="<?php echo $row['bulan'] . '/' . $row['tahun']; ?>">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (mysqli_num_rows($result) === 0): ?>
                            <tr>
                                <td colspan="<?php echo in_array($_SESSION['user']['role'], ['superadmin', 'pengurus']) ? '10' : '9'; ?>" 
                                    class="text-center">
                                    Tidak ada data
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php require_once '../../template/footer.php'; ?>

<!-- Add this before closing </body> tag -->
<?php if (in_array($_SESSION['user']['role'], ['superadmin', 'pengurus'])): ?>
    <div class="modal fade" id="modal-delete">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Konfirmasi Hapus</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="tagihan_delete.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="delete-id">
                        <p>Anda yakin ingin menghapus tagihan untuk:</p>
                        <p id="delete-info" class="font-weight-bold"></p>
                        <div class="form-group">
                            <label for="delete-reason">Alasan Hapus <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="delete-reason" name="reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
$(function() {
    // Handle delete button click
    $('.btn-delete').click(function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        var periode = $(this).data('periode');
        
        $('#delete-id').val(id);
        $('#delete-info').html(nama + ' (Periode: ' + periode + ')');
    });
});
</script>