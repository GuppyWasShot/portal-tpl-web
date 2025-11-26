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

$judul = isset($_POST['judul']) ? trim($_POST['judul']) : '';
$slug_input = isset($_POST['slug']) ? trim($_POST['slug']) : '';
$konten = isset($_POST['konten']) ? trim($_POST['konten']) : '';
$urutan = isset($_POST['urutan']) ? intval($_POST['urutan']) : 0;
$status = isset($_POST['status']) && $_POST['status'] === 'inactive' ? 'inactive' : 'active';

if (empty($judul) || empty($konten)) {
    header("Location: ../../views/admin/kelola_tentang.php?error=empty_field");
    exit();
}

$slug_base = !empty($slug_input) ? $slug_input : $judul;
$slug = generate_unique_slug($conn, 'tbl_about_sections', 'slug', $slug_base, 'id_section');

try {
    $stmt = $conn->prepare("INSERT INTO tbl_about_sections (judul, slug, konten, urutan, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $judul, $slug, $konten, $urutan, $status);
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
            "Menambah section Tentang: $judul"
    
        );
    }

    header("Location: ../../views/admin/kelola_tentang.php?success=created");
    exit();
} catch (Exception $e) {
    error_log('Gagal menambah section Tentang: ' . $e->getMessage());
    header("Location: ../../views/admin/kelola_tentang.php?error=database_error");
    exit();
}

