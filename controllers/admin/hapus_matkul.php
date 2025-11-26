<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../views/admin/login.php?error=belum_login");
    exit();
}

$id_matkul = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_matkul <= 0) {
    header("Location: ../../views/admin/kelola_matkul.php?error=invalid_id");
    exit();
}

require_once __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../config/db_connect.php';

$db = Database::getInstance()->getConnection();
$conn = $db;

try {
    $stmt = $conn->prepare("DELETE FROM tbl_matkul WHERE id_matkul = ?");
    $stmt->bind_param("i", $id_matkul);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $stmt->close();
        header("Location: ../../views/admin/kelola_matkul.php?error=not_found");
        exit();
    }

    $stmt->close();

    // Fix: Support both old and new session variable names
    $admin_id_log = $_SESSION['admin_id'] ?? $_SESSION['id_admin'] ?? null;
    $admin_username_log = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'Unknown';
    
    if ($admin_id_log) {
        logActivity(
            $conn,
            $admin_id_log,
            $admin_username_log,
            "Menghapus mata kuliah ID: $id_matkul"
    
        );
    }

    header("Location: ../../views/admin/kelola_matkul.php?success=deleted");
    exit();
} catch (Exception $e) {
    error_log('Gagal menghapus mata kuliah: ' . $e->getMessage());
    header("Location: ../../views/admin/kelola_matkul.php?error=database_error");
    exit();
}

