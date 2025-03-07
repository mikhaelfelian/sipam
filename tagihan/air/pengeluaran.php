<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/flash_message.php';
require_once '../../config/auth.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

$settings = getSettings();
$page_title = "Pengeluaran Air";
$current_page = 'pengeluaran';

require_once '../../template/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Pengeluaran</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Tagihan Air</li>
                    <li class="breadcrumb-item active">Pengeluaran</li>
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
                        <h3 class="card-title">Data Pengeluaran</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-tambah">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get data from tbl_akt_kas
                        $query = "SELECT * FROM tbl_akt_kas 
                                  WHERE status_kas = '2' AND jenis = 'Pengeluaran Air'
                                  ORDER BY tgl_masuk DESC";
                        $result = mysqli_query($conn, $query);
                        ?>
                        <table id="pengeluaranTable" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">Tanggal</th>
                                    <th>Keterangan</th>
                                    <th width="15%">Nominal</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)): 
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $no++; ?></td>
                                    <td class="text-center"><?php echo date('d/m/Y', strtotime($row['tgl_masuk'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                    <td class="text-right">Rp <?php echo number_format($row['nominal'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-xs" 
                                                onclick="deleteData(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

<div class="modal fade" id="modal-tambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Pengeluaran</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="pengeluaran_save.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="tanggal">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="catatan">Catatan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="nominal">Nominal (Rp) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nominal" name="nominal" 
                               required onkeyup="formatRupiah(this)">
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function formatRupiah(input) {
    let value = input.value.replace(/[^\d]/g, '');
    if (value !== '') {
        value = parseInt(value).toLocaleString('id-ID');
        input.value = value;
    }
}

$(document).ready(function() {
    $('#pengeluaranTable').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[1, 'desc']], // Order by tanggal descending
        "pageLength": 10,
        "language": {
            "lengthMenu": "Tampilkan _MENU_ data per halaman",
            "zeroRecords": "Data tidak ditemukan",
            "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
            "infoEmpty": "Tidak ada data tersedia",
            "infoFiltered": "(difilter dari _MAX_ total data)",
            "search": "Cari:",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        },
        "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
               "<'row'<'col-sm-12'tr>>" +
               "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        "buttons": [
            {
                extend: 'excel',
                className: 'btn-sm'
            },
            {
                extend: 'pdf',
                className: 'btn-sm'
            },
            {
                extend: 'print',
                className: 'btn-sm'
            }
        ]
    });
});

function deleteData(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'pengeluaran_delete.php?id=' + id;
        }
    });
}
</script>

<!-- Add this to your header section -->
<link rel="stylesheet" href="<?php echo $base_url; ?>assets/plugins/sweetalert2/sweetalert2.min.css">
<script src="<?php echo $base_url; ?>assets/plugins/sweetalert2/sweetalert2.all.min.js"></script>

<?php require_once '../../template/footer.php'; ?> 