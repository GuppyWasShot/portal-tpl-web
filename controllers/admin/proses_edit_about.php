<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../views/admin/login.php?error=belum_login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../views/admin/kelola_tentang.php?error=invalid_request");
    exit();
}

require_once __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../../helpers/text_helper.php';

$db = Database::getInstance()->getConnection();
$conn = $db;

$id_section = isset($_POST['id_section']) ? intval($_POST['id_section']) : 0;
$judul = isset($_POST['judul']) ? trim($_POST['judul']) : '';
$slug_input = isset($_POST['slug']) ? trim($_POST['slug']) : '';
$konten = isset($_POST['konten']) ? trim($_POST['konten']) : '';
$urutan = isset($_POST['urutan']) ? intval($_POST['urutan']) : 0;
$status = isset($_POST['status']) && $_POST['status'] === 'inactive' ? 'inactive' : 'active';

if ($id_section <= 0 || empty($judul) || empty($konten)) {
    header("Location: ../../views/admin/kelola_tentang.php?error=empty_field");
    exit();
}

$slug_base = !empty($slug_input) ? $slug_input : $judul;
$slug = generate_unique_slug($conn, 'tbl_about_sections', 'slug', $slug_base, 'id_section', $id_section);

try {
    $stmt = $conn->prepare("UPDATE tbl_about_sections SET judul = ?, slug = ?, konten = ?, urutan = ?, status = ? WHERE id_section = ?");
    $stmt->bind_param("sssisi", $judul, $slug, $konten, $urutan, $status, $id_section);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $stmt->close();
        header("Location: ../../views/admin/kelola_tentang.php?error=not_found");
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
            "Mengubah section Tentang: $judul"
    
        );
    }

    header("Location: ../../views/admin/kelola_tentang.php?success=updated");
    exit();
} catch (Exception $e) {
    error_log('Gagal mengedit section Tentang: ' . $e->getMessage());
    header("Location: ../../views/admin/kelola_tentang.php?error=database_error");
    exit();
}

