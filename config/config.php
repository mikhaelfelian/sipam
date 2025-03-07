<?php
require_once dirname(__FILE__) . '/connect.php';

// Add this function at the top of the file
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Dynamic Base URL Configuration
$base_url = '';
if (isset($_SERVER['HTTP_HOST'])) {
    $base_url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $base_url .= $_SERVER['HTTP_HOST'];
    $base_url .= '/pam/';  // Your project folder name
}

// Base URL for styles and assets
$base_url_style = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://'.$_SERVER['HTTP_HOST']. '/pam/assets/theme/admin-lte-3/';

// Error reporting - show only errors
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '1');

// Optional: Log errors instead of displaying them
ini_set('log_errors', '1');
ini_set('error_log', dirname(__FILE__) . '/../logs/error.log');

// Get settings from database with proper error handling
$settings = [];
try {
    $settings_query = "SELECT * FROM tbl_settings LIMIT 1";
    $settings_result = mysqli_query($conn, $settings_query);
    
    if ($settings_result) {
        $settings = mysqli_fetch_assoc($settings_result);
    } else {
        // If query fails, set default values
        $settings = [
            'pagination_limit' => 10,
            'site_title' => 'Default Site Title',
            // Add other default settings as needed
        ];
    }
} catch (Exception $e) {
    // If any error occurs, use default values
    $settings = [
        'pagination_limit' => 10,
        'site_title' => 'Default Site Title',
        // Add other default settings as needed
    ];
}

// Make sure pagination_limit is an integer
$settings['pagination_limit'] = isset($settings['pagination_limit']) ? (int)$settings['pagination_limit'] : 10;

/**
 * Generate pagination data
 * 
 * @param int $total_records Total number of records
 * @param int $current_page Current page number
 * @param int $limit Items per page
 * @return array Pagination data
 */
function getPagination($total_records, $current_page = 1, $limit = 10) {
    // Ensure all parameters are integers
    $total_records = (int)$total_records;
    $current_page = (int)$current_page;
    $limit = (int)$limit;
    
    // Ensure values are valid
    $current_page = max(1, $current_page);
    $limit = max(1, $limit);
    
    // Calculate total pages
    $total_pages = ceil($total_records / $limit);
    
    // Ensure current page doesn't exceed total pages
    $current_page = min($current_page, max(1, $total_pages));
    
    // Calculate offset
    $offset = ($current_page - 1) * $limit;
    
    return [
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'limit' => $limit,
        'offset' => $offset,
        'total_records' => $total_records
    ];
}

/**
 * Generate pagination links HTML
 * 
 * @param int $current_page Current page number
 * @param int $total_pages Total number of pages
 * @param int $limit Items per page
 * @param int $total_records Total number of records
 * @param string $url_pattern URL pattern for links
 * @return string Pagination HTML
 */
function getPaginationLinks($current_page, $total_pages, $limit, $total_records, $url_pattern = '?page=%d') {
    $html = '<div class="row mt-3">
        <div class="col-sm-12 col-md-5">
            <div class="dataTables_info">
                Showing ' . (($current_page - 1) * $limit + 1) . ' to ' 
                . min($current_page * $limit, $total_records) 
                . ' of ' . $total_records . ' entries
            </div>
        </div>
        <div class="col-sm-12 col-md-7">
            <div class="dataTables_paginate paging_simple_numbers">
                <ul class="pagination">';
    
    // Previous button
    if ($current_page > 1) {
        $html .= '<li class="paginate_button page-item previous">
            <a href="' . sprintf($url_pattern, ($current_page-1)) . '" class="page-link">Previous</a>
        </li>';
    }
    
    // Page numbers
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        $html .= '<li class="paginate_button page-item ' . ($i == $current_page ? 'active' : '') . '">
            <a href="' . sprintf($url_pattern, $i) . '" class="page-link">' . $i . '</a>
        </li>';
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $html .= '<li class="paginate_button page-item next">
            <a href="' . sprintf($url_pattern, ($current_page+1)) . '" class="page-link">Next</a>
        </li>';
    }
    
    $html .= '</ul></div></div></div>';
    
    return $html;
}

/**
 * Generate pagination data with search
 * 
 * @param string $table Table name
 * @param int $limit Items per page
 * @param string $search Search term
 * @param array $searchColumns Columns to search in
 * @param string $orderBy Order by clause (optional)
 * @param string $where Additional where clause (optional)
 * @return array Pagination data
 */

function getPaginationSearch($table, $limit = 10, $search = '', $searchColumns = [], $orderBy = '', $where = '') {
    global $conn;
    
    // Ensure limit is valid
    $limit = (isset($limit) && $limit > 0) ? (int)$limit : 10;
    
    // Get current page
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = $page > 0 ? $page : 1;
    $start = ($page - 1) * $limit;
    
    // Build search clause
    $searchClause = '';
    if ($search && !empty($searchColumns)) {
        $searchTerms = [];
        foreach ($searchColumns as $column) {
            $searchTerms[] = "$column LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
        }
        $searchClause = '(' . implode(' OR ', $searchTerms) . ')';
    }
    
    // Combine where clauses
    $whereClause = '';
    if ($searchClause && $where) {
        $whereClause = "WHERE $searchClause AND ($where)";
    } elseif ($searchClause) {
        $whereClause = "WHERE $searchClause";
    } elseif ($where) {
        $whereClause = "WHERE $where";
    }
    
    // Build order by clause
    $orderByClause = $orderBy ? "ORDER BY $orderBy" : '';
    
    // Get total records
    $total_records_query = "SELECT COUNT(*) as count FROM $table $whereClause";
    $total_records_result = mysqli_query($conn, $total_records_query);
    $total_records = mysqli_fetch_assoc($total_records_result)['count'];
    
    // Calculate total pages
    $total_pages = $total_records > 0 ? ceil($total_records / $limit) : 1;
    
    // Adjust page if it exceeds total pages
    if ($page > $total_pages) {
        $page = $total_pages;
        $start = ($page - 1) * $limit;
    }
    
    // Get records
    $query = "SELECT * FROM $table $whereClause $orderByClause LIMIT $start, $limit";
    $result = mysqli_query($conn, $query);
    
    return [
        'page' => $page,
        'start' => $start,
        'limit' => $limit,
        'total_records' => $total_records,
        'total_pages' => $total_pages,
        'result' => $result,
        'search' => $search
    ];
}

// Add this function to get total warga count
function getTotalWarga($conn) {
    $query = "SELECT COUNT(*) as total FROM tbl_m_warga";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Add functions to get warga counts by status
function getWargaByStatus($conn, $status) {
    $status = (int)$status;
    $query = "SELECT COUNT(*) as total FROM tbl_m_warga WHERE status_rumah = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Add this function to calculate duration of stay
function calculateDuration($start_date) {
    $start = new DateTime($start_date);
    $now = new DateTime();
    $interval = $start->diff($now);
    
    $years = $interval->y;
    $months = $interval->m;
    
    $duration = '';
    
    if ($years > 0) {
        $duration .= $years . ' tahun ';
    }
    
    if ($months > 0 || $years > 0) {
        $duration .= $months . ' bulan';
    }
    
    return trim($duration) ?: '< 1 bulan';
}

// Add this function for Indonesian date formatting
function formatTanggal($date) {
    if (!$date) return '-';
    return date('d/m/Y', strtotime($date));
}

// Add security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:;");