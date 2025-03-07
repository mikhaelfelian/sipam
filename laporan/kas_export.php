<?php
// Ensure no output has been sent before
if (ob_get_length()) ob_end_clean();

require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/auth.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

startSession();
checkLogin();
checkRole(['superadmin', 'pengurus']);

// Build query
$query = "SELECT 
    id,
    tgl_masuk as tanggal,
    keterangan,
    CASE WHEN status_kas = 1 THEN nominal ELSE 0 END as kas_masuk,
    CASE WHEN status_kas = 2 THEN nominal ELSE 0 END as kas_keluar
FROM tbl_akt_kas
WHERE tipe = 1";

if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $start_date = mysqli_real_escape_string($conn, $_GET['start_date']);
    $query .= " AND DATE(tgl_masuk) >= '$start_date'";
}

if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $end_date = mysqli_real_escape_string($conn, $_GET['end_date']);
    $query .= " AND DATE(tgl_masuk) <= '$end_date'";
}

$query .= " ORDER BY tgl_masuk ASC";
$result = mysqli_query($conn, $query);

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();

try {
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('Sistem Informasi Air')
        ->setLastModifiedBy('Sistem Informasi Air')
        ->setTitle('Laporan Kas')
        ->setSubject('Laporan Kas')
        ->setDescription('Laporan Kas Air');

    // Add header
    $sheet->setCellValue('A1', 'LAPORAN KAS');
    $sheet->mergeCells('A1:F1');

    // Add period if dates are set
    if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
        $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($_GET['start_date'])) . 
                             ' - ' . date('d/m/Y', strtotime($_GET['end_date'])));
        $sheet->mergeCells('A2:F2');
        $currentRow = 3;
    } else {
        $currentRow = 2;
    }

    // Add table headers
    $headers = ['No', 'Tanggal', 'Keterangan', 'Kas Masuk', 'Kas Keluar', 'Saldo'];
    $sheet->fromArray($headers, NULL, 'A' . $currentRow);
    $currentRow++;

    // Add data
    $no = 1;
    $saldo = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $saldo += $row['kas_masuk'] - $row['kas_keluar'];
        
        $sheet->setCellValue('A' . $currentRow, $no);
        $sheet->setCellValue('B' . $currentRow, date('d/m/Y', strtotime($row['tanggal'])));
        $sheet->setCellValue('C' . $currentRow, $row['keterangan']);
        $sheet->setCellValue('D' . $currentRow, $row['kas_masuk']);
        $sheet->setCellValue('E' . $currentRow, $row['kas_keluar']);
        $sheet->setCellValue('F' . $currentRow, $saldo);
        
        // Format numbers
        $sheet->getStyle('D' . $currentRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E' . $currentRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('F' . $currentRow)->getNumberFormat()->setFormatCode('#,##0');
        
        $currentRow++;
        $no++;
    }

    // Add totals
    $totalRow = $currentRow;
    $sheet->setCellValue('A' . $totalRow, 'Total:');
    $sheet->mergeCells('A' . $totalRow . ':C' . $totalRow);

    // Calculate totals
    mysqli_data_seek($result, 0);
    $total_masuk = 0;
    $total_keluar = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $total_masuk += $row['kas_masuk'];
        $total_keluar += $row['kas_keluar'];
    }

    $sheet->setCellValue('D' . $totalRow, $total_masuk);
    $sheet->setCellValue('E' . $totalRow, $total_keluar);
    $sheet->setCellValue('F' . $totalRow, $saldo);

    // Format numbers in total row
    $sheet->getStyle('D' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
    $sheet->getStyle('E' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
    $sheet->getStyle('F' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');

    // Style the header
    $sheet->getStyle('A1:F1')->applyFromArray([
        'font' => [
            'bold' => true,
            'size' => 14
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER
        ]
    ]);

    // Style the table headers
    $headerStyle = [
        'font' => [
            'bold' => true
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN
            ]
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => 'E2EFDA'
            ]
        ]
    ];
    $sheet->getStyle('A' . ($currentRow - $no) . ':F' . ($currentRow - $no))->applyFromArray($headerStyle);

    // Style the data
    $dataStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN
            ]
        ]
    ];
    $sheet->getStyle('A' . ($currentRow - $no + 1) . ':F' . ($currentRow - 1))->applyFromArray($dataStyle);

    // Style the total row
    $totalStyle = [
        'font' => [
            'bold' => true
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN
            ]
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => 'E2EFDA'
            ]
        ]
    ];
    $sheet->getStyle('A' . $totalRow . ':F' . $totalRow)->applyFromArray($totalStyle);

    // Auto-size columns
    foreach (range('A', 'F') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Set alignment for specific columns
    $sheet->getStyle('A3:A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('B3:B' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('D3:F' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    // Create the Excel file
    $writer = new Xlsx($spreadsheet);
    
    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="laporan_kas_'.date('Y-m-d').'.xlsx"');
    header('Cache-Control: max-age=0');
    
    // Ensure all other output buffers are cleaned
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Save to PHP output
    $writer->save('php://output');
    
} catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
    echo "Error creating Excel file: " . $e->getMessage();
} catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
    echo "Error in PhpSpreadsheet: " . $e->getMessage();
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage();
}

exit();
?>