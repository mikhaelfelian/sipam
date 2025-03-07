<?php
require_once 'config/connect.php';

// Clear existing data
mysqli_query($conn, "TRUNCATE TABLE tbl_m_warga");

// Sample data arrays
$names_first = ['Budi', 'Siti', 'Ahmad', 'Dewi', 'Joko', 'Ani', 'Rudi', 'Sri', 'Agus', 'Rina'];
$names_last = ['Santoso', 'Wijaya', 'Saputra', 'Kusuma', 'Hidayat', 'Purnama', 'Wibowo', 'Susanto', 'Nugroho', 'Pratama'];
$blocks = ['A', 'B', 'C', 'D'];

// Generate 50 unique records
for ($i = 1; $i <= 50; $i++) {
    // Generate random data
    $kk = '3374' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    $nik = '3374' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    $nama = $names_first[array_rand($names_first)] . ' ' . $names_last[array_rand($names_last)];
    $alamat = 'Jalan Mutiara Pandanaran No. ' . mt_rand(1, 100);
    $blok = $blocks[array_rand($blocks)] . '-' . str_pad(mt_rand(1, 25), 2, '0', STR_PAD_LEFT);
    $status_rumah = mt_rand(1, 2);
    $no_hp = '8' . str_pad(mt_rand(1, 99999999), 10, '0', STR_PAD_LEFT);
    
    // Generate random dates within the last 2 years
    $tgl_masuk = date('Y-m-d', strtotime('-' . mt_rand(0, 730) . ' days'));
    $tgl_keluar = ($status_rumah == 2) ? date('Y-m-d', strtotime($tgl_masuk . ' +1 year')) : null;

    // Insert data
    $query = "INSERT INTO tbl_m_warga (kk, nik, nama, alamat, blok, status_rumah, no_hp, tgl_masuk, tgl_keluar) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
              
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssssss", 
        $kk, 
        $nik, 
        $nama, 
        $alamat, 
        $blok, 
        $status_rumah,
        $no_hp,
        $tgl_masuk,
        $tgl_keluar
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        echo "Error inserting record: " . mysqli_error($conn) . "<br>";
    }
}

echo "Done! Generated 50 records.<br>";

// Display the generated data
$result = mysqli_query($conn, "SELECT * FROM tbl_m_warga ORDER BY blok, nama");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generated Warga Data</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Generated Warga Data</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>KK</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>Blok</th>
                <th>Status</th>
                <th>No HP</th>
                <th>Tgl Masuk</th>
                <th>Tgl Keluar</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)): 
            ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($row['kk']); ?></td>
                <td><?php echo htmlspecialchars($row['nik']); ?></td>
                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                <td><?php echo htmlspecialchars($row['alamat']); ?></td>
                <td><?php echo htmlspecialchars($row['blok']); ?></td>
                <td><?php echo $row['status_rumah'] == 1 ? 'Sendiri' : 'Kontrak'; ?></td>
                <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                <td><?php echo $row['tgl_masuk']; ?></td>
                <td><?php echo $row['tgl_keluar'] ?: '-'; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html> 