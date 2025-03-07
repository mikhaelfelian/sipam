<?php

/**
 * Get list of months in Indonesian
 * @return array Array of months with keys as numbers and values as Indonesian month names
 */
function getMonthList() {
    return [
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
}

/**
 * Get month name in Indonesian
 * @param string $month_number Month number (01-12)
 * @return string Month name in Indonesian
 */
function getMonthName($month_number) {
    $months = getMonthList();
    return isset($months[$month_number]) ? $months[$month_number] : '';
}

/**
 * Generate year options for dropdown
 * @param int $range Number of years to show before current year
 * @return array Array of years
 */
function getYearOptions($range = 2) {
    $years = [];
    $current_year = date('Y');
    for ($year = $current_year; $year >= $current_year - $range; $year--) {
        $years[] = $year;
    }
    return $years;
}

/**
 * Format number to Indonesian format
 * @param float $number Number to format
 * @param int $decimals Number of decimal points
 * @return string Formatted number
 */
function formatNumber($number, $decimals = 0) {
    return number_format($number, $decimals, ',', '.');
} 