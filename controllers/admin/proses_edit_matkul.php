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

$id_matkul = isset($_POST['id_matkul']) ? intval($_POST['id_matkul']) : 0;
$kode = isset($_POST['kode']) ? trim($_POST['kode']) : '';
$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$sks = isset($_POST['sks']) ? intval($_POST['sks']) : 0;
$semester = isset($_POST['semester']) ? intval($_POST['semester']) : 0;
$kategori = isset($_POST['kategori']) ? trim($_POST['kategori']) : '';
$deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
$urutan = isset($_POST['urutan']) ? intval($_POST['urutan']) : 0;
$status = isset($_POST['status']) && $_POST['status'] === 'inactive' ? 'inactive' : 'active';

if ($id_matkul <= 0 || empty($kode) || empty($nama) || $semester <= 0) {
    header("Location: ../../views/admin/kelola_matkul.php?error=empty_field");
    exit();
}

try {
    $stmt = $conn->prepare("UPDATE tbl_matkul SET kode = ?, nama = ?, sks = ?, semester = ?, kategori = ?, deskripsi = ?, urutan = ?, status = ? WHERE id_matkul = ?");
    $stmt->bind_param("ssiissisi", $kode, $nama, $sks, $semester, $kategori, $deskripsi, $urutan, $status, $id_matkul);
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
            "Mengubah mata kuliah: $kode - $nama"
    
        );
    }

    header("Location: ../../views/admin/kelola_matkul.php?success=updated");
    exit();
} catch (Exception $e) {
    error_log('Gagal mengedit mata kuliah: ' . $e->getMessage());
    header("Location: ../../views/admin/kelola_matkul.php?error=database_error");
    exit();
}

