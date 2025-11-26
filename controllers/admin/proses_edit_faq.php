<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../views/admin/login.php?error=belum_login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../views/admin/kelola_faq.php?error=invalid_request");
    exit();
}

require_once __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../config/db_connect.php';

$db = Database::getInstance()->getConnection();
$conn = $db;

$id_faq = isset($_POST['id_faq']) ? intval($_POST['id_faq']) : 0;
$pertanyaan = isset($_POST['pertanyaan']) ? trim($_POST['pertanyaan']) : '';
$jawaban = isset($_POST['jawaban']) ? trim($_POST['jawaban']) : '';
$kategori = isset($_POST['kategori']) ? trim($_POST['kategori']) : null;
$urutan = isset($_POST['urutan']) ? intval($_POST['urutan']) : 0;
$status = isset($_POST['status']) && $_POST['status'] === 'inactive' ? 'inactive' : 'active';

if ($id_faq <= 0 || empty($pertanyaan) || empty($jawaban)) {
    header("Location: ../../views/admin/kelola_faq.php?error=empty_field");
    exit();
}

try {
    $stmt = $conn->prepare("UPDATE tbl_faq SET pertanyaan = ?, jawaban = ?, kategori = ?, urutan = ?, status = ? WHERE id_faq = ?");
    $stmt->bind_param("sssisi", $pertanyaan, $jawaban, $kategori, $urutan, $status, $id_faq);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $stmt->close();
        header("Location: ../../views/admin/kelola_faq.php?error=not_found");
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
            "Mengubah FAQ: $pertanyaan"
    
        );
    }

    header("Location: ../../views/admin/kelola_faq.php?success=updated");
    exit();
} catch (Exception $e) {
    error_log('Gagal mengedit FAQ: ' . $e->getMessage());
    header("Location: ../../views/admin/kelola_faq.php?error=database_error");
    exit();
}

