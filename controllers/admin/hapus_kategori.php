<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../views/admin/login.php?error=belum_login");
    exit();
}

$id_kategori = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_kategori <= 0) {
    header("Location: ../../views/admin/kelola_kategori.php?error=not_found");
    exit();
}

require_once __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../config/db_connect.php';
$db = Database::getInstance()->getConnection();
$conn = $db;

mysqli_begin_transaction($conn);

try {
    $stmt = $conn->prepare("SELECT nama_kategori FROM tbl_category WHERE id_kategori = ?");
    $stmt->bind_param("i", $id_kategori);
    $stmt->execute();
    $result = $stmt->get_result();
    $kategori = $result->fetch_assoc();
    $stmt->close();

    if (!$kategori) {
        mysqli_rollback($conn);
        header("Location: ../../views/admin/kelola_kategori.php?error=not_found");
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM tbl_project_category WHERE id_kategori = ?");
    $stmt->bind_param("i", $id_kategori);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM tbl_category WHERE id_kategori = ?");
    $stmt->bind_param("i", $id_kategori);
    $stmt->execute();
    $stmt->close();

    mysqli_commit($conn);

    // Fix: Support both old and new session variable names
    $admin_id_log = $_SESSION['admin_id'] ?? $_SESSION['id_admin'] ?? null;
    $admin_username_log = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'Unknown';
    
    if ($admin_id_log) {
        logActivity(
            $conn,
            $admin_id_log,
            $admin_username_log,
            "Menghapus kategori: {$kategori['nama_kategori']} (ID: $id_kategori)"
        );
    }


    header("Location: ../../views/admin/kelola_kategori.php?success=deleted");
    exit();
} catch (Exception $e) {
    mysqli_rollback($conn);
    error_log('Gagal menghapus kategori: ' . $e->getMessage());
    header("Location: ../../views/admin/kelola_kategori.php?error=database_error");
    exit();
}

