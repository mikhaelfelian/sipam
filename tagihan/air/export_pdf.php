<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../vendor/setasign/fpdf/fpdf.php';

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

// Get settings data
$query_settings = "SELECT * FROM tbl_pengaturan WHERE id = 1";
$result_settings = mysqli_query($conn, $query_settings);
$settings = mysqli_fetch_assoc($result_settings);

class PDF extends FPDF {
    private $settings;
    
    function __construct($settings) {
        parent::__construct();
        $this->settings = $settings;
    }
    
    function Header() {
        // Logo from settings
        if (!empty($this->settings['logo'])) {
            $logoPath = '../../assets/images/app/' . $this->settings['logo'];
            if (file_exists($logoPath)) {
                $this->Image($logoPath, 10, 6, 30);
            }
        }
        
        // Header Text
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 6, strtoupper($this->settings['judul']), 0, 1, 'C');
        
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, $this->settings['alamat'], 0, 1, 'C');
        $this->Cell(0, 6, $this->settings['kota'], 0, 1, 'C');
        
        // Line break and horizontal line
        $this->Ln(7);
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 287, $this->GetY());
        $this->SetLineWidth(0.2);
        $this->Line(10, $this->GetY() + 1, 287, $this->GetY() + 1);
        $this->Ln(5);
        
        // Report Title
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'LAPORAN TAGIHAN AIR', 0, 1, 'C');
        
        // Period info if filtered
        if (isset($_GET['filter_bulan']) && isset($_GET['filter_tahun'])) {
            $bulan = $_GET['filter_bulan'];
            $tahun = $_GET['filter_tahun'];
            $bulanStr = date("F", mktime(0, 0, 0, $bulan, 10));
            $this->SetFont('Arial', '', 10);
            $this->Cell(0, 6, "Periode: $bulanStr $tahun", 0, 1, 'C');
        }
        $this->Ln(5);
        
        // Table Header
        $this->SetFillColor(230, 230, 230);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(10, 7, 'No', 1, 0, 'C', true);
        $this->Cell(50, 7, 'Nama Warga', 1, 0, 'C', true);
        $this->Cell(20, 7, 'Blok', 1, 0, 'C', true);
        $this->Cell(25, 7, 'Periode', 1, 0, 'C', true);
        $this->Cell(20, 7, 'M. Awal', 1, 0, 'C', true);
        $this->Cell(20, 7, 'M. Akhir', 1, 0, 'C', true);
        $this->Cell(20, 7, 'Pakai', 1, 0, 'C', true);
        $this->Cell(35, 7, 'Total Tagihan', 1, 0, 'C', true);
        $this->Cell(35, 7, 'Total Bayar', 1, 0, 'C', true);
        $this->Cell(25, 7, 'Status', 1, 1, 'C', true);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', '', 8);
        
        // Print date on the left
        $this->Cell(0, 10, 'Dicetak pada: ' . date('d/m/Y H:i:s'), 0, 0, 'L');
        
        // Page number on the right
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }
}

// Create PDF object
$pdf = new PDF($settings);
$pdf->AliasNbPages();
$pdf->AddPage('L', 'A4');
$pdf->SetAutoPageBreak(true, 20);

// Set default font
$pdf->SetFont('Arial', '', 9);

// Build the query with filters
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

// Add filters
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

// Fill data with alternating row colors
$no = 1;
$fillColor = false;
while ($row = mysqli_fetch_assoc($result)) {
    $pdf->SetFillColor(245, 245, 245);
    $pdf->Cell(10, 6, $no++, 1, 0, 'C', $fillColor);
    $pdf->Cell(50, 6, $row['nama'], 1, 0, 'L', $fillColor);
    $pdf->Cell(20, 6, $row['blok'], 1, 0, 'C', $fillColor);
    $pdf->Cell(25, 6, $row['bulan'] . '/' . $row['tahun'], 1, 0, 'C', $fillColor);
    $pdf->Cell(20, 6, number_format($row['meter_sebelumnya'], 0, ',', '.'), 1, 0, 'R', $fillColor);
    $pdf->Cell(20, 6, number_format($row['meter_akhir'], 0, ',', '.'), 1, 0, 'R', $fillColor);
    $pdf->Cell(20, 6, number_format($row['pemakaian'], 0, ',', '.'), 1, 0, 'R', $fillColor);
    $pdf->Cell(35, 6, 'Rp ' . number_format($row['total_tagihan'], 0, ',', '.'), 1, 0, 'R', $fillColor);
    $pdf->Cell(35, 6, 'Rp ' . number_format($row['total_bayar'], 0, ',', '.'), 1, 0, 'R', $fillColor);
    
    $status = '';
    switch($row['status_bayar']) {
        case 1:
            $status = 'Lunas';
            break;
        case 2:
            $status = 'Kurang Bayar';
            break;
        default:
            $status = 'Belum Bayar';
    }
    $pdf->Cell(25, 6, $status, 1, 1, 'C', $fillColor);
    $fillColor = !$fillColor; // Alternate row colors
}

// Add summary at the bottom
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, 'Total Data: ' . ($no - 1), 0, 1, 'L');

// Output PDF
$pdf->Output('I', 'Laporan_Tagihan_Air.pdf');
?> 