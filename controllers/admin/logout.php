<?php
session_start();
require_once __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../config/db_connect.php';
$db = Database::getInstance()->getConnection();
$conn = $db;

// Log aktivitas logout sebelum menghapus session
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_username'])) {
    try {
        logActivity(
            $conn, 
            $_SESSION['admin_id'], 
            $_SESSION['admin_username'], 
            'Logout dari sistem'
        );
    } catch (Exception $e) {
        // Log error tapi lanjutkan proses logout
    }
}

// Hapus semua session
session_unset();
session_destroy();

// Pastikan tidak ada output sebelum header
if (ob_get_level()) {
    ob_end_clean();
}

// Gunakan URL absolut untuk redirect
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script_path = dirname($_SERVER['PHP_SELF']); // /portal_tpl/controllers/admin
$base_path = dirname(dirname($script_path)); // /portal_tpl
$redirect_url = $protocol . '://' . $host . $base_path . '/views/admin/login.php?error=logout';

header("Location: " . $redirect_url);
exit();
?>