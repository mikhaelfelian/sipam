<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

$settings = getSettings();
$page_title = "Pembayaran Tagihan Air";
$current_page = 'bayar_air';

// Get transaction data
if (!isset($_GET['id'])) {
    setFlashMessage('danger', 'ID transaksi tidak ditemukan');
    header("Location: {$base_url}tagihan/air/catat_meter.php");
    exit();
}

$id = (int)$_GET['id'];
$query = "SELECT t.*, w.nama, w.blok, 
          (SELECT meter_akhir FROM tbl_trx_air 
           WHERE id_warga = t.id_warga 
           AND (tahun < t.tahun OR (tahun = t.tahun AND bulan < t.bulan))
           ORDER BY tahun DESC, bulan DESC LIMIT 1) as meter_sebelumnya
          FROM tbl_trx_air t
          JOIN tbl_m_warga w ON t.id_warga = w.id
          WHERE t.id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    setFlashMessage('danger', 'Data transaksi tidak ditemukan');
    header("Location: {$base_url}tagihan/air/catat_meter.php");
    exit();
}

$trx = mysqli_fetch_assoc($result);

// Get applicable tariff based on usage
$query_tarif = "SELECT * FROM tbl_m_air_tarif 
                WHERE ? BETWEEN 
                    CAST(SUBSTRING_INDEX(range_pemakaian, '-', 1) AS DECIMAL) 
                    AND 
                    CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(range_pemakaian, '-', -1), ' ', 1) AS DECIMAL)
                LIMIT 1";
$stmt = mysqli_prepare($conn, $query_tarif);
$pemakaian = $trx['pemakaian'];
mysqli_stmt_bind_param($stmt, "d", $pemakaian);
mysqli_stmt_execute($stmt);
$result_tarif = mysqli_stmt_get_result($stmt);
$tarif = mysqli_fetch_assoc($result_tarif);

// Check if tariff exists for the usage range
if (!$tarif) {
    // Get the highest range as default tariff
    $query_default = "SELECT * FROM tbl_m_air_tarif 
                     ORDER BY CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(range_pemakaian, '-', -1), ' ', 1) AS DECIMAL) DESC 
                     LIMIT 1";
    $result_default = mysqli_query($conn, $query_default);
    $tarif = mysqli_fetch_assoc($result_default);
    
    if (!$tarif) {
        setFlashMessage('danger', 'Tidak ditemukan tarif yang sesuai untuk pemakaian ' . $pemakaian . ' m³');
        header("Location: {$base_url}tagihan/air/catat_meter.php");
        exit();
    }
}

// At the beginning of the file after getting the transaction data
$total_tagihan = isset($_GET['total_tagihan']) ? (int)$_GET['total_tagihan'] : 0;

// Update the transaction with total_tagihan if not already set
if ($total_tagihan > 0) {
    $update_query = "UPDATE tbl_trx_air SET total_tagihan = ? WHERE id = ? AND (total_tagihan IS NULL OR total_tagihan = 0)";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "ii", $total_tagihan, $id);
    mysqli_stmt_execute($stmt);
}

require_once '../../template/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Pembayaran Tagihan Air</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="catat_meter.php">Tagihan Air</a></li>
                    <li class="breadcrumb-item active">Pembayaran</li>
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
                        <h3 class="card-title">Form Pembayaran Tagihan Air</h3>
                    </div>
                    <form action="bayar_save.php" method="POST">
                        <input type="hidden" name="id_trx" value="<?php echo $trx['id']; ?>">
                        <div class="card-body">
                            <?php if ($flash_message = getFlashMessage()): ?>
                                <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <?php echo $flash_message['text']; ?>
                                </div>
                            <?php endif; ?>

                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Warga</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($trx['nama']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Blok</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($trx['blok']); ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Meter Awal</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control text-right" 
                                                   value="<?php echo number_format($trx['meter_awal'], 0, '', '.'); ?>" readonly>
                                            <div class="input-group-append">
                                                <span class="input-group-text">m³</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Meter Akhir</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control text-right" 
                                                   value="<?php echo number_format($trx['meter_akhir'], 0, '', '.'); ?>" readonly>
                                            <div class="input-group-append">
                                                <span class="input-group-text">m³</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Pemakaian</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control text-right" 
                                                   value="<?php echo number_format($trx['pemakaian'], 0, '', '.'); ?>" readonly>
                                            <div class="input-group-append">
                                                <span class="input-group-text">m³</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Periode</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo $trx['bulan'] . '/' . $trx['tahun']; ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label>Rincian Biaya</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tr>
                                            <td>Biaya Pemakaian (<?php echo $trx['pemakaian']; ?> m³ × Rp <?php echo number_format($tarif['biaya_m3'], 0, '', '.'); ?>)</td>
                                            <td width="200" class="text-right">
                                                <?php 
                                                $biaya_pemakaian = $trx['pemakaian'] * $tarif['biaya_m3'];
                                                echo 'Rp ' . number_format($biaya_pemakaian, 0, '', '.');
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Biaya Maintenance</td>
                                            <td class="text-right">
                                                <?php echo 'Rp ' . number_format($tarif['biaya_mtc'], 0, '', '.'); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Biaya Admin</td>
                                            <td class="text-right">
                                                <?php echo 'Rp ' . number_format($tarif['biaya_adm'], 0, '', '.'); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Tagihan</strong></td>
                                            <td class="text-right">
                                                <strong>
                                                    <?php
                                                    $total = $biaya_pemakaian + $tarif['biaya_mtc'] + $tarif['biaya_adm'];
                                                    echo 'Rp ' . number_format($total, 0, '', '.');
                                                    ?>
                                                </strong>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <input type="hidden" name="biaya_pemakaian" value="<?php echo $biaya_pemakaian; ?>">
                            <input type="hidden" name="biaya_mtc" value="<?php echo $tarif['biaya_mtc']; ?>">
                            <input type="hidden" name="biaya_adm" value="<?php echo $tarif['biaya_adm']; ?>">
                            <input type="hidden" name="total" value="<?php echo $total; ?>">
                            <input type="hidden" name="total_tagihan" value="<?php echo $total_tagihan; ?>">

                            <div class="form-group">
                                <label>Metode Pembayaran</label>
                                <select class="form-control" name="platform" required>
                                    <option value="">Pilih Platform Pembayaran</option>
                                    <?php
                                    $query_bayar = "SELECT * FROM tbl_m_platform WHERE status = 1 ORDER BY platform";
                                    $result_bayar = mysqli_query($conn, $query_bayar);
                                    while ($bayar = mysqli_fetch_assoc($result_bayar)):
                                    ?>
                                        <option value="<?php echo $bayar['id']; ?>">
                                            <?php echo htmlspecialchars($bayar['platform']); ?>
                                            <?php if ($bayar['keterangan']): ?>
                                                (<?php echo htmlspecialchars($bayar['keterangan']); ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Jumlah Bayar</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" class="form-control text-right" id="jumlah_bayar" name="jumlah_bayar" 
                                                   required data-total="<?php echo $total; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kembalian</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" class="form-control text-right" id="kembalian" readonly>
                                            <input type="hidden" name="kembalian" id="kembalian_hidden">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <a href="catat_meter.php" class="btn btn-default">Kembali</a>
                                <button type="submit" class="btn btn-primary">Bayar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?php echo $base_url; ?>assets/theme/admin-lte-3/plugins/jquery/jquery.min.js"></script>
<script src="<?php echo $base_url; ?>assets/theme/admin-lte-3/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $base_url; ?>assets/theme/admin-lte-3/plugins/sweetalert2/sweetalert2.all.min.js"></script>
<link rel="stylesheet" href="<?php echo $base_url; ?>assets/theme/admin-lte-3/plugins/sweetalert2/sweetalert2.min.css">

<script>
$(function() {
    // Format number inputs
    $('#jumlah_bayar').on('input', function() {
        var value = $(this).val().replace(/[^\d]/g, '');
        var total = parseInt($(this).data('total'));
        var jumlahBayar = parseInt(value) || 0;
        var kembalian = jumlahBayar - total;

        // Format jumlah bayar
        $(this).val(formatRupiah(value));
        
        // Calculate and display kembalian
        if (kembalian >= 0) {
            $('#kembalian').val(formatRupiah(kembalian.toString()));
            $('#kembalian_hidden').val(kembalian);
        } else {
            $('#kembalian').val('0');
            $('#kembalian_hidden').val('0');
        }
    });

    // Format rupiah function
    function formatRupiah(angka) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        return rupiah;
    }

    // Form validation before submit
    $('form').on('submit', function(e) {
        var jumlahBayar = parseInt($('#jumlah_bayar').val().replace(/[^\d]/g, '')) || 0;
        var total = parseInt($('#jumlah_bayar').data('total'));

        if (jumlahBayar <= 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Pembayaran Invalid',
                text: 'Jumlah bayar harus lebih dari 0!'
            });
        } else if (jumlahBayar < total) {
            // Show confirmation for partial payment
            e.preventDefault();
            Swal.fire({
                title: 'Pembayaran Sebagian',
                text: 'Jumlah bayar kurang dari total tagihan. Lanjutkan pembayaran sebagian?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(e.target).unbind('submit').submit();
                }
            });
        }
    });
});
</script>

<?php require_once '../../template/footer.php'; ?> 