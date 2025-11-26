<?php
/**
 * Helper function untuk generate URL absolut
 */

function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script_path = dirname($_SERVER['PHP_SELF']);
    
    // Deteksi apakah dari controllers/admin atau controllers/public
    if (strpos($script_path, '/controllers/admin') !== false) {
        $base_path = dirname(dirname($script_path)); // /portal_tpl
    } elseif (strpos($script_path, '/controllers/public') !== false) {
        $base_path = dirname(dirname($script_path)); // /portal_tpl
    } else {
        // Default: assume dari root
        $base_path = $script_path;
    }
    
    return $protocol . '://' . $host . $base_path;
}

function redirectTo($path, $query_params = []) {
    // Pastikan tidak ada output sebelum header
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Build query string
    $query_string = '';
    if (!empty($query_params)) {
        $query_string = '?' . http_build_query($query_params);
    }
    
    // Build full URL
    $base_url = getBaseUrl();
    $url = $base_url . '/' . ltrim($path, '/') . $query_string;
    
    header("Location: " . $url);
    exit();
}

