<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../views/admin/login.php?error=belum_login");
    exit();
}

$id_dosen = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_dosen <= 0) {
    header("Location: ../../views/admin/kelola_dosen.php?error=invalid_id");
    exit();
}

require_once __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../config/db_connect.php';

$db = Database::getInstance()->getConnection();
$conn = $db;

$stmt = $conn->prepare("SELECT foto_url FROM tbl_dosen WHERE id_dosen = ?");
$stmt->bind_param("i", $id_dosen);
$stmt->execute();
$result = $stmt->get_result();
$dosen = $result->fetch_assoc();
$stmt->close();

if (!$dosen) {
    header("Location: ../../views/admin/kelola_dosen.php?error=not_found");
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM tbl_dosen WHERE id_dosen = ?");
    $stmt->bind_param("i", $id_dosen);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $stmt->close();
        header("Location: ../../views/admin/kelola_dosen.php?error=not_found");
        exit();
    }

    $stmt->close();

    if (!empty($dosen['foto_url'])) {
        $photo_path = __DIR__ . '/../../' . $dosen['foto_url'];
        if (file_exists($photo_path) && is_file($photo_path)) {
            unlink($photo_path);
        }
    }

    // Fix: Support both old and new session variable names
    $admin_id_log = $_SESSION['admin_id'] ?? $_SESSION['id_admin'] ?? null;
    $admin_username_log = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'Unknown';
    
    if ($admin_id_log) {
        logActivity(
            $conn,
            $admin_id_log,
            $admin_username_log,
            "Menghapus data dosen ID: $id_dosen"
    
        );
    }

    header("Location: ../../views/admin/kelola_dosen.php?success=deleted");
    exit();
} catch (Exception $e) {
    error_log('Gagal menghapus dosen: ' . $e->getMessage());
    header("Location: ../../views/admin/kelola_dosen.php?error=database_error");
    exit();
}

