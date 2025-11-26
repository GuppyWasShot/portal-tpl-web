<?php
/**
 * Helper function untuk redirect dengan URL absolut
 * Mengatasi masalah path relatif yang tidak konsisten
 */

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
    
    // Build URL absolut
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Get base path dari current script
    $script_path = dirname($_SERVER['PHP_SELF']);
    
    // Normalize path (remove controllers/admin atau controllers/public)
    $base_path = dirname(dirname($script_path));
    
    // Build full URL
    $url = $protocol . '://' . $host . $base_path . '/' . ltrim($path, '/') . $query_string;
    
    header("Location: " . $url);
    exit();
}

// Helper untuk redirect ke admin views
function redirectToAdmin($view, $query_params = []) {
    redirectTo('views/admin/' . $view, $query_params);
}

// Helper untuk redirect ke public views
function redirectToPublic($view, $query_params = []) {
    redirectTo('views/public/' . $view, $query_params);
}

