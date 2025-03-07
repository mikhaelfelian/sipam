<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';

startSession();
checkLogin();

$page_title = "Catat Meter Air";
$current_page = 'catat_meter';

// Get current user's warga ID if they are a warga
$user_warga_id = null;
if ($_SESSION['user']['role'] === 'warga') {
    $user_warga_id = $_SESSION['user']['id_warga'];
}

// Modify the query based on user role
if ($_SESSION['user']['role'] === 'warga') {
    // Warga can only see their own meter
    $query = "SELECT t.*, w.nama, w.blok, u.username as pencatat
              FROM tbl_trx_air t 
              JOIN tbl_m_warga w ON t.id_warga = w.id 
              LEFT JOIN tbl_users u ON t.created_by = u.id 
              WHERE t.id_warga = ?
              ORDER BY t.created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_warga_id);
} else {
    // Superadmin and pengurus can see all meters
    $query = "SELECT t.*, w.nama, w.blok, u.username as pencatat
              FROM tbl_trx_air t 
              JOIN tbl_m_warga w ON t.id_warga = w.id 
              LEFT JOIN tbl_users u ON t.created_by = u.id 
              ORDER BY t.created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get warga data for logged in user
if ($_SESSION['user']['role'] === 'warga') {
    $stmt = mysqli_prepare($conn, "SELECT nama FROM tbl_m_warga WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_warga_id);
    mysqli_stmt_execute($stmt);
    $warga_result = mysqli_stmt_get_result($stmt);
    $warga = mysqli_fetch_assoc($warga_result);
}

require_once '../../template/header.php';
?>

<!-- CSS -->
<!-- Select2 -->
<link href="<?php echo $base_url; ?>assets/theme/admin-lte-3/plugins/select2/css/select2.min.css" rel="stylesheet">
<link href="<?php echo $base_url; ?>assets/theme/admin-lte-3/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css" rel="stylesheet">

<!-- JavaScript -->
<!-- jQuery -->
<script src="<?php echo $base_url; ?>assets/theme/admin-lte-3/plugins/jquery/jquery.min.js"></script>
<!-- Select2 -->
<script src="<?php echo $base_url; ?>assets/theme/admin-lte-3/plugins/select2/js/select2.full.min.js"></script>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Catat Meter Air</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Tagihan Air</li>
                    <li class="breadcrumb-item active">Catat Meter</li>
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
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Form Catat Meter Air</h3>
                    </div>
                    <form action="catat_meter_save.php" method="POST">
                        <div class="card-body">
                            <?php if ($flash_message = getFlashMessage()): ?>
                                <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <?php echo $flash_message['text']; ?>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="nama_warga">Nama Warga</label>
                                <?php if ($_SESSION['user']['role'] === 'warga'): ?>
                                    <!-- For warga: Show locked field with their name -->
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($warga['nama']); ?>" readonly>
                                    <input type="hidden" id="id_warga" name="id_warga" value="<?php echo $_SESSION['user']['id_warga']; ?>">
                                <?php else: ?>
                                    <!-- For admin/pengurus: Show Select2 dropdown with initial data -->
                                    <select class="form-control select2" id="nama_warga" name="id_warga" style="width: 100%;" required>
                                        <option value="">Masukkan Nama Warga / Blok Rumah</option>
                                        <?php
                                        // Get initial warga data
                                        $query_warga = "SELECT id, nama, nik, blok FROM tbl_m_warga WHERE status_warga = 1 ORDER BY nama LIMIT 10";
                                        $result_warga = mysqli_query($conn, $query_warga);
                                        while ($warga = mysqli_fetch_assoc($result_warga)): 
                                        ?>
                                            <option value="<?php echo $warga['id']; ?>">
                                                <?php echo htmlspecialchars($warga['nama']) . ' (' . $warga['nik'] . ') - Blok ' . $warga['blok']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="meter_akhir">Meter Saat Ini</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="meter_akhir" name="meter_akhir"
                                        step="0.01" min="0" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">m³</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="meter_awal">Meter Sebelumnya</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="meter_awal" name="meter_awal"
                                        step="0.01" min="0" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">m³</span>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group">
                                <label for="pemakaian">Pemakaian</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="pemakaian" name="pemakaian"
                                        step="0.01" min="0" readonly required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">m³</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Periode</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <select class="form-control" name="bulan" required>
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
                                                $selected = $value == date('m') ? 'selected' : '';
                                                ?>
                                                <option value="<?php echo $value; ?>" <?php echo $selected; ?>>
                                                    <?php echo $label; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <select class="form-control" name="tahun" required>
                                            <?php
                                            $tahun_sekarang = date('Y');
                                            for ($tahun = $tahun_sekarang; $tahun >= $tahun_sekarang - 2; $tahun--):
                                                $selected = $tahun == $tahun_sekarang ? 'selected' : '';
                                                ?>
                                                <option value="<?php echo $tahun; ?>" <?php echo $selected; ?>>
                                                    <?php echo $tahun; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <button type="reset" class="btn btn-default">Reset</button>
                        </div>
                    </form>
                </div>

                <!-- Tabel Riwayat Pencatatan -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Riwayat Pencatatan Meter</h3>
                    </div>
                    <div class="card-body">
                        <!-- Add filter form - only show for superadmin and pengurus -->
                        <?php if (in_array($_SESSION['user']['role'], ['superadmin', 'pengurus'])): ?>
                            <form method="GET" class="mb-4">
                                <div class="row">
                                    <div class="col-md-4">
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
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tahun</label>
                                            <select class="form-control" name="filter_tahun">
                                                <option value="">Semua Tahun</option>
                                                <?php
                                                $tahun_sekarang = date('Y');
                                                for ($tahun = $tahun_sekarang; $tahun >= $tahun_sekarang - 2; $tahun--):
                                                    $selected = (isset($_GET['filter_tahun']) && $_GET['filter_tahun'] == $tahun) ? 'selected' : '';
                                                    ?>
                                                    <option value="<?php echo $tahun; ?>" <?php echo $selected; ?>>
                                                        <?php echo $tahun; ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
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
                        <?php endif; ?>

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
                        } else {
                            // For non-warga users, show only unpaid by default unless filtered
                            if (!isset($_GET['filter_status'])) {
                                $query .= " AND t.status_bayar != 1";
                            }
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

                        // Add status filter if set
                        if (isset($_GET['filter_status'])) {
                            $status = mysqli_real_escape_string($conn, $_GET['filter_status']);
                            $query .= " AND t.status_bayar = '$status'";
                        }

                        $query .= " ORDER BY t.created_at DESC";

                        // Pagination configuration
                        $per_page = $settings['pagination_limit'];
                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        $start = ($page - 1) * $per_page;

                        // Add LIMIT to the query for pagination
                        $query_count = $query; // Save the query without LIMIT for counting
                        $query .= " LIMIT $start, $per_page";

                        // Get total records for pagination
                        $total_records_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM ($query_count) as t");
                        $total_records = mysqli_fetch_assoc($total_records_result)['total'];
                        $total_pages = ceil($total_records / $per_page);

                        // Execute the query with LIMIT
                        $result = mysqli_query($conn, $query);

                        // Add error checking
                        if (!$result) {
                            die("Query failed: " . mysqli_error($conn));
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
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)):
                                    // Format numbers with Indonesian format - no comma
                                    $meter_awal = number_format($row['meter_awal'], 0, '', '.');
                                    $meter_akhir = number_format($row['meter_akhir'], 0, '', '.');
                                    $pemakaian = number_format($row['pemakaian'], 0, '', '.');
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
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
                                        <td>
                                            <div class="btn-group">
                                                <?php if ($row['status_bayar'] != 1): ?>
                                                    <?php if ($_SESSION['user']['role'] === 'superadmin'): ?>
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

                                                    <?php if (in_array($_SESSION['user']['role'], ['superadmin', 'pengurus'])): ?>
                                                        <a href="bayar.php?id=<?php echo $row['id']; ?>" 
                                                           class="btn btn-success btn-sm">
                                                            <i class="fas fa-money-bill-wave"></i> 
                                                            <?php echo ($row['status_bayar'] == 2) ? 'Lunasi' : 'Bayar'; ?>
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php if (mysqli_num_rows($result) === 0): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Tidak ada data</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <?php if ($total_pages > 1): ?>
                            <div class="d-flex justify-content-center mt-4">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination">
                                        <!-- First page -->
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=1<?php echo isset($_GET['filter_bulan']) ? '&filter_bulan='.$_GET['filter_bulan'] : ''; ?><?php echo isset($_GET['filter_tahun']) ? '&filter_tahun='.$_GET['filter_tahun'] : ''; ?><?php echo isset($_GET['filter_status']) ? '&filter_status='.$_GET['filter_status'] : ''; ?>">
                                                    <i class="fas fa-angle-double-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Previous page -->
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo isset($_GET['filter_bulan']) ? '&filter_bulan='.$_GET['filter_bulan'] : ''; ?><?php echo isset($_GET['filter_tahun']) ? '&filter_tahun='.$_GET['filter_tahun'] : ''; ?><?php echo isset($_GET['filter_status']) ? '&filter_status='.$_GET['filter_status'] : ''; ?>">
                                                    <i class="fas fa-angle-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Page numbers -->
                                        <?php
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $page + 2);

                                        for ($i = $start_page; $i <= $end_page; $i++):
                                        ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['filter_bulan']) ? '&filter_bulan='.$_GET['filter_bulan'] : ''; ?><?php echo isset($_GET['filter_tahun']) ? '&filter_tahun='.$_GET['filter_tahun'] : ''; ?><?php echo isset($_GET['filter_status']) ? '&filter_status='.$_GET['filter_status'] : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- Next page -->
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo isset($_GET['filter_bulan']) ? '&filter_bulan='.$_GET['filter_bulan'] : ''; ?><?php echo isset($_GET['filter_tahun']) ? '&filter_tahun='.$_GET['filter_tahun'] : ''; ?><?php echo isset($_GET['filter_status']) ? '&filter_status='.$_GET['filter_status'] : ''; ?>">
                                                    <i class="fas fa-angle-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Last page -->
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo isset($_GET['filter_bulan']) ? '&filter_bulan='.$_GET['filter_bulan'] : ''; ?><?php echo isset($_GET['filter_tahun']) ? '&filter_tahun='.$_GET['filter_tahun'] : ''; ?><?php echo isset($_GET['filter_status']) ? '&filter_status='.$_GET['filter_status'] : ''; ?>">
                                                    <i class="fas fa-angle-double-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Update the delete modal -->
<?php if (in_array($_SESSION['user']['role'], ['superadmin'])): ?>
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

<?php require_once '../../template/footer.php'; ?>

<!-- jQuery -->
<script src="<?php echo $base_url; ?>assets/theme/admin-lte-3/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="<?php echo $base_url; ?>assets/theme/admin-lte-3/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="<?php echo $base_url; ?>assets/theme/admin-lte-3/plugins/select2/js/select2.full.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    $('#nama_warga').select2({
        theme: 'bootstrap4',
        placeholder: 'Masukkan Nama Warga / Blok Rumah',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: 'get_warga.php',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data // Data is already in correct format
                };
            },
            cache: true
        }
    }).on('select2:select', function(e) {
        var id_warga = e.params.data.id;
        // Get last meter reading
        $.ajax({
            url: 'get_last_meter.php',
            type: 'POST',
            data: { id_warga: id_warga },
            success: function(response) {
                if (response.success) {
                    // Set meter_awal to the last meter reading
                    $('#meter_awal').val(response.last_meter);
                    // Focus on meter_akhir input
                    $('#meter_akhir').focus();
                } else {
                    console.error('Failed to get last meter:', response.message);
                    $('#meter_awal').val(0);
                }
                // Clear other fields
                $('#meter_akhir').val('');
                $('#pemakaian').val('');
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', error);
                $('#meter_awal').val(0);
                $('#meter_akhir').val('');
                $('#pemakaian').val('');
            }
        });
    });

    // Calculate usage when either meter_awal or meter_akhir changes
    $('#meter_awal, #meter_akhir').on('input', function() {
        calculatePemakaian();
    });

    function calculatePemakaian() {
        var meter_awal = parseFloat($('#meter_awal').val()) || 0;
        var meter_akhir = parseFloat($('#meter_akhir').val()) || 0;
        var pemakaian = meter_akhir - meter_awal;
        
        // Just calculate pemakaian without showing alert
        $('#pemakaian').val(pemakaian.toFixed(2));
    }

    // Add form submit validation
    $('form').on('submit', function(e) {
        var meter_awal = parseFloat($('#meter_awal').val()) || 0;
        var meter_akhir = parseFloat($('#meter_akhir').val()) || 0;
        
        if (meter_akhir < meter_awal) {
            e.preventDefault(); // Prevent form submission
            // Let the server-side validation handle the error message
            return false;
        }
    });
});
</script>