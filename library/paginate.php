<?php
if (!function_exists('getPagination')) {
    function getPagination($total_records, $current_page = 1, $limit = 10) {
        // Ensure all parameters are integers
        $total_records = max(0, (int)$total_records);
        $current_page = max(1, (int)$current_page);
        $limit = max(1, (int)$limit);
        
        // Calculate total pages
        $total_pages = max(1, ceil($total_records / $limit));
        
        // Ensure current page doesn't exceed total pages
        $current_page = min($current_page, $total_pages);
        
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
}

if (!function_exists('renderPagination')) {
    function renderPagination($total_records, $current_page = 1, $limit = 10) {
        $pagination = getPagination($total_records, $current_page, $limit);
        
        if ($pagination['total_pages'] <= 1) {
            return '';
        }
        
        $html = '<nav aria-label="Page navigation">';
        $html .= '<ul class="pagination justify-content-center">';
        
        // Previous button
        if ($pagination['current_page'] > 1) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="?page=' . ($pagination['current_page'] - 1) . '">&laquo;</a>';
            $html .= '</li>';
        }
        
        // Page numbers
        for ($i = 1; $i <= $pagination['total_pages']; $i++) {
            if ($i == $pagination['current_page']) {
                $html .= '<li class="page-item active">';
                $html .= '<span class="page-link">' . $i . '</span>';
                $html .= '</li>';
            } else {
                $html .= '<li class="page-item">';
                $html .= '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
                $html .= '</li>';
            }
        }
        
        // Next button
        if ($pagination['current_page'] < $pagination['total_pages']) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="?page=' . ($pagination['current_page'] + 1) . '">&raquo;</a>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
} 