<?php
function formatTanggal($date, $format = 'FULL') {
    if (empty($date)) return '-';
    
    $timestamp = strtotime($date);
    
    switch ($format) {
        case 'DATE_ONLY':
            return date('d/m/Y', $timestamp);
            
        case 'TIME_ONLY':
            return date('H:i', $timestamp);
            
        case 'FULL':
            return date('d/m/Y H:i', $timestamp);
            
        case 'MONTH_YEAR':
            return date('m/Y', $timestamp);
            
        case 'YEAR_ONLY':
            return date('Y', $timestamp);
            
        case 'MYSQL_DATE':
            return date('Y-m-d', $timestamp);
            
        case 'MYSQL_DATETIME':
            return date('Y-m-d H:i:s', $timestamp);
            
        default:
            return date($format, $timestamp);
    }
}

function getNamaBulan($bulan) {
    $nama_bulan = [
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
    
    return isset($nama_bulan[$bulan]) ? $nama_bulan[$bulan] : '';
}

function getNamaBulanSingkat($bulan) {
    $nama_bulan = [
        '01' => 'Jan',
        '02' => 'Feb',
        '03' => 'Mar',
        '04' => 'Apr',
        '05' => 'Mei',
        '06' => 'Jun',
        '07' => 'Jul',
        '08' => 'Agt',
        '09' => 'Sep',
        '10' => 'Okt',
        '11' => 'Nov',
        '12' => 'Des'
    ];
    
    return isset($nama_bulan[$bulan]) ? $nama_bulan[$bulan] : '';
}

function formatTanggalIndo($date) {
    if (empty($date)) return '-';
    
    $timestamp = strtotime($date);
    $tanggal = date('d', $timestamp);
    $bulan = getNamaBulan(date('m', $timestamp));
    $tahun = date('Y', $timestamp);
    
    return $tanggal . ' ' . $bulan . ' ' . $tahun;
}

function formatTanggalIndoSingkat($date) {
    if (empty($date)) return '-';
    
    $timestamp = strtotime($date);
    $tanggal = date('d', $timestamp);
    $bulan = getNamaBulanSingkat(date('m', $timestamp));
    $tahun = date('Y', $timestamp);
    
    return $tanggal . ' ' . $bulan . ' ' . $tahun;
}

function formatPeriode($bulan, $tahun) {
    return getNamaBulan($bulan) . ' ' . $tahun;
}

function formatPeriodeSingkat($bulan, $tahun) {
    return getNamaBulanSingkat($bulan) . ' ' . $tahun;
} 