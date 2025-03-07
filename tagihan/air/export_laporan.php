<?php
require_once '../../config/connect.php';
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set document properties
$spreadsheet->getProperties()
    ->setCreator('PAM Warga')
    ->setLastModifiedBy('PAM Warga')
    ->setTitle('Laporan Tagihan Air')
    ->setSubject('Laporan Tagihan Air')
    ->setDescription('Laporan Tagihan Air PAM Warga');

// Style the header
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4B5563'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
        ],
    ],
];

// Set the headers
$headers = [
    'A' => 'No',
    'B' => 'Nama Warga',
    'C' => 'Blok',
    'D' => 'Periode',
    'E' => 'Meter Awal',
    'F' => 'Meter Akhir',
    'G' => 'Pemakaian',
    'H' => 'Total Tagihan',
    'I' => 'Total Bayar',
    'J' => 'Status'
];

foreach ($headers as $column => $header) {
    $sheet->setCellValue($column . '1', $header);
}

// Apply header style
$sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

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

// Data style
$dataStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
        ],
    ],
];

// Fill the data
$row = 2;
$no = 1;
while ($data = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A' . $row, $no++);
    $sheet->setCellValue('B' . $row, $data['nama']);
    $sheet->setCellValue('C' . $row, $data['blok']);
    $sheet->setCellValue('D' . $row, $data['bulan'] . '/' . $data['tahun']);
    $sheet->setCellValue('E' . $row, (int)$data['meter_sebelumnya']);
    $sheet->setCellValue('F' . $row, (int)$data['meter_akhir']);
    $sheet->setCellValue('G' . $row, (int)$data['pemakaian']);
    $sheet->setCellValue('H' . $row, (float)$data['total_tagihan']);
    $sheet->setCellValue('I' . $row, (float)$data['total_bayar']);
    
    switch($data['status_bayar']) {
        case 1:
            $status = 'Lunas';
            break;
        case 2:
            $status = 'Kurang Bayar';
            break;
        default:
            $status = 'Belum Bayar';
    }
    $sheet->setCellValue('J' . $row, $status);
    
    // Apply data style
    $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray($dataStyle);
    
    $row++;
}

// Format number columns
$sheet->getStyle('H2:I' . ($row-1))->getNumberFormat()->setFormatCode('#,##0');
$sheet->getStyle('E2:G' . ($row-1))->getNumberFormat()->setFormatCode('0');

// Auto-size columns
foreach(range('A','J') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Clean any output buffers
ob_end_clean();

// Set headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Laporan_Tagihan_Air_' . date('Y-m-d_H-i-s') . '.xlsx"');
header('Cache-Control: max-age=0');

// Create Excel file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>