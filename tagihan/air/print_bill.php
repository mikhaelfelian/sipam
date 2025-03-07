<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../vendor/setasign/fpdf/fpdf.php'; // Make sure FPDF is installed

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

// Get transaction ID from either POST or GET
$id_trx = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if (!$id_trx) {
    die("ID Transaksi tidak valid");
}

// Get transaction details
$query = "SELECT t.*, w.nama, w.blok, w.no_hp,
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
          WHERE t.id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_trx);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data tidak ditemukan");
}

// Get settings
$settings = getSettings();

// Create custom PDF class for 58mm thermal paper
class ReceiptPDF extends FPDF {
    function Header() {
        // No header
    }
    
    function Footer() {
        // No footer
    }
}

// Initialize PDF (58mm = 219.685 pixels)
$pdf = new ReceiptPDF('P', 'mm', array(58, 150));
$pdf->AddPage();
$pdf->SetMargins(4, 4, 4);
$pdf->SetAutoPageBreak(true, 4);

// Add content
$pdf->SetFont('Arial', '', 8);

// Header
$pdf->Cell(50, 4, $settings['judul_app'], 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(50, 4, 'TAGIHAN AIR', 0, 1, 'C');
$pdf->SetFont('Arial', '', 8);

// Line
$pdf->Line(4, $pdf->GetY(), 54, $pdf->GetY());
$pdf->Ln(2);

// Transaction details
$pdf->Cell(50, 4, 'No. Tagihan: ' . str_pad($data['id'], 4, '0', STR_PAD_LEFT), 0, 1);
$pdf->Cell(50, 4, 'Periode: ' . $data['bulan'] . '/' . $data['tahun'], 0, 1);
$pdf->Cell(50, 4, 'Tanggal: ' . date('d/m/Y H:i'), 0, 1);

$pdf->Ln(2);
$pdf->Cell(50, 4, 'Nama: ' . $data['nama'], 0, 1);
$pdf->Cell(50, 4, 'Blok: ' . $data['blok'], 0, 1);
$pdf->Cell(50, 4, 'No HP: ' . $data['no_hp'], 0, 1);

$pdf->Ln(2);
$pdf->Cell(50, 4, 'RINCIAN PEMAKAIAN:', 0, 1);
$pdf->Cell(25, 4, 'Meter Awal:', 0, 0);
$pdf->Cell(25, 4, number_format($data['meter_sebelumnya'], 0, ',', '.') . ' m3', 0, 1, 'R');
$pdf->Cell(25, 4, 'Meter Akhir:', 0, 0);
$pdf->Cell(25, 4, number_format($data['meter_akhir'], 0, ',', '.') . ' m3', 0, 1, 'R');
$pdf->Cell(25, 4, 'Pemakaian:', 0, 0);
$pdf->Cell(25, 4, number_format($data['pemakaian'], 0, ',', '.') . ' m3', 0, 1, 'R');

$pdf->Ln(2);
$pdf->Cell(25, 4, 'Total Tagihan:', 0, 0);
$pdf->Cell(25, 4, 'Rp ' . number_format($data['total_tagihan'], 0, ',', '.'), 0, 1, 'R');
$pdf->Cell(25, 4, 'Total Bayar:', 0, 0);
$pdf->Cell(25, 4, 'Rp ' . number_format($data['total_bayar'], 0, ',', '.'), 0, 1, 'R');
$pdf->Cell(25, 4, 'Status:', 0, 0);

$status = '';
switch($data['status_bayar']) {
    case 1:
        $status = 'LUNAS';
        break;
    case 2:
        $status = 'KURANG BAYAR';
        break;
    default:
        $status = 'BELUM BAYAR';
}
$pdf->Cell(25, 4, $status, 0, 1, 'R');

// Line
$pdf->Ln(2);
$pdf->Line(4, $pdf->GetY(), 54, $pdf->GetY());
$pdf->Ln(2);

// Footer text
$pdf->SetFont('Arial', 'I', 7);
$pdf->Cell(50, 3, 'Terima kasih atas pembayaran Anda.', 0, 1, 'C');
$pdf->Cell(50, 3, 'Simpan struk ini sebagai bukti pembayaran.', 0, 1, 'C');

// Before outputting PDF, clear any output buffers
ob_end_clean();

// Output PDF
$pdf->Output('I', 'Tagihan_Air_' . $data['id'] . '.pdf');
exit();
?>