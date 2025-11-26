<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../views/admin/login.php?error=belum_login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../views/admin/kelola_kategori.php?error=invalid_request");
    exit();
}

require_once __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../config/db_connect.php';
$db = Database::getInstance()->getConnection();
$conn = $db;

$id_kategori = isset($_POST['id_kategori']) ? intval($_POST['id_kategori']) : 0;
$nama_kategori = isset($_POST['nama_kategori']) ? trim($_POST['nama_kategori']) : '';
$warna_hex = isset($_POST['warna_hex']) ? trim($_POST['warna_hex']) : '#6366F1';

if ($id_kategori <= 0) {
    header("Location: ../../views/admin/kelola_kategori.php?error=not_found");
    exit();
}

if (empty($nama_kategori)) {
    header("Location: ../../views/admin/kelola_kategori.php?error=empty_field");
    exit();
}

$warna_hex = strtoupper($warna_hex);
if (!preg_match('/^#[0-9A-F]{6}$/', $warna_hex)) {
    header("Location: ../../views/admin/kelola_kategori.php?error=invalid_color");
    exit();
}

try {
    $stmt = $conn->prepare("UPDATE tbl_category SET nama_kategori = ?, warna_hex = ? WHERE id_kategori = ?");
    $stmt->bind_param("ssi", $nama_kategori, $warna_hex, $id_kategori);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $stmt->close();
        header("Location: ../../views/admin/kelola_kategori.php?error=not_found");
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
            "Mengubah kategori: $nama_kategori (ID: $id_kategori)"
    
        );
    }

    header("Location: ../../views/admin/kelola_kategori.php?success=updated");
    exit();
} catch (Exception $e) {
    error_log('Gagal mengedit kategori: ' . $e->getMessage());
    header("Location: ../../views/admin/kelola_kategori.php?error=database_error");
    exit();
}

