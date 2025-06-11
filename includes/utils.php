<?php
/**
 * Common utility functions for Resume Builder
 */

/**
 * Sanitize and validate input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a secure CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set flash message
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $message, $type = 'success') {
    setFlashMessage($message, $type);
    header("Location: $url");
    exit;
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'M j, Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Generate breadcrumbs HTML
 */
function generateBreadcrumbs($items) {
    $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    
    $count = count($items);
    foreach ($items as $index => $item) {
        $isLast = ($index === $count - 1);
        
        if ($isLast) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($item['title']) . '</li>';
        } else {
            $html .= '<li class="breadcrumb-item"><a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['title']) . '</a></li>';
        }
    }
    
    $html .= '</ol></nav>';
    return $html;
}

/**
 * Generate pagination HTML
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) return '';
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($currentPage > 1) {
        $prevUrl = $baseUrl . '?page=' . ($currentPage - 1);
        $html .= '<li class="page-item"><a class="page-link" href="' . $prevUrl . '">Previous</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=1">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i === $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $totalPages . '">' . $totalPages . '</a></li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $nextUrl = $baseUrl . '?page=' . ($currentPage + 1);
        $html .= '<li class="page-item"><a class="page-link" href="' . $nextUrl . '">Next</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Check if user is on mobile device
 */
function isMobile() {
    return preg_match('/Mobile|Android|iPhone|iPad/', $_SERVER['HTTP_USER_AGENT'] ?? '');
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

/**
 * Convert array to CSV
 */
function arrayToCSV($array, $filename = 'export.csv') {
    if (empty($array)) return false;
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Write header
    fputcsv($output, array_keys($array[0]));
    
    // Write data
    foreach ($array as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    return true;
}

/**
 * Log activity
 */
function logActivity($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
    error_log($logMessage, 3, __DIR__ . '/../logs/activity.log');
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate URL
 */
function isValidURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Truncate text with ellipsis
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Convert bytes to human readable format
 */
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Generate slug from text
 */
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}