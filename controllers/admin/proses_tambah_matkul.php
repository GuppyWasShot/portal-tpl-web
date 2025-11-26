<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../views/admin/login.php?error=belum_login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../views/admin/kelola_matkul.php?error=invalid_request");
    exit();
}

require_once __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../config/db_connect.php';

$db = Database::getInstance()->getConnection();
$conn = $db;

$kode = isset($_POST['kode']) ? trim($_POST['kode']) : '';
$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$sks = isset($_POST['sks']) ? intval($_POST['sks']) : 0;
$semester = isset($_POST['semester']) ? intval($_POST['semester']) : 0;
$kategori = isset($_POST['kategori']) ? trim($_POST['kategori']) : '';
$deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
$urutan = isset($_POST['urutan']) ? intval($_POST['urutan']) : 0;
$status = isset($_POST['status']) && $_POST['status'] === 'inactive' ? 'inactive' : 'active';

if (empty($kode) || empty($nama) || $semester <= 0) {
    header("Location: ../../views/admin/kelola_matkul.php?error=empty_field");
    exit();
}

try {
    $stmt = $conn->prepare("INSERT INTO tbl_matkul (kode, nama, sks, semester, kategori, deskripsi, urutan, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiissis", $kode, $nama, $sks, $semester, $kategori, $deskripsi, $urutan, $status);
    $stmt->execute();
    $stmt->close();

    // Fix: Support both old and new session variable names
    $admin_id_log = $_SESSION['admin_id'] ?? $_SESSION['id_admin'] ?? null;
    $admin_username_log = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'Unknown';
    
    if ($admin_id_log) {
        logActivity(
            $conn,
            $admin_id_log,
            $admin_username_log,
            "Menambah mata kuliah: $kode - $nama"
    
        );
    }

    header("Location: ../../views/admin/kelola_matkul.php?success=created");
    exit();
} catch (Exception $e) {
    error_log('Gagal menambah mata kuliah: ' . $e->getMessage());
    header("Location: ../../views/admin/kelola_matkul.php?error=database_error");
    exit();
}

