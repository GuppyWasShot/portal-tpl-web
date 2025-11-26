<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../views/admin/login.php?error=belum_login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../views/admin/kelola_dosen.php?error=invalid_request");
    exit();
}

require_once __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../../helpers/text_helper.php';

$db = Database::getInstance()->getConnection();
$conn = $db;

$id_dosen = isset($_POST['id_dosen']) ? intval($_POST['id_dosen']) : 0;
$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$gelar = isset($_POST['gelar']) ? trim($_POST['gelar']) : '';
$jabatan = isset($_POST['jabatan']) ? trim($_POST['jabatan']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
$urutan = isset($_POST['urutan']) ? intval($_POST['urutan']) : 0;
$status = isset($_POST['status']) && $_POST['status'] === 'inactive' ? 'inactive' : 'active';

if ($id_dosen <= 0 || empty($nama)) {
    header("Location: ../../views/admin/kelola_dosen.php?error=empty_field");
    exit();
}

$stmt = $conn->prepare("SELECT foto_url FROM tbl_dosen WHERE id_dosen = ?");
$stmt->bind_param("i", $id_dosen);
$stmt->execute();
$result = $stmt->get_result();
$existing = $result->fetch_assoc();
$stmt->close();

if (!$existing) {
    header("Location: ../../views/admin/kelola_dosen.php?error=not_found");
    exit();
}

$foto_path = $existing['foto_url'];

if (isset($_FILES['foto']) && !empty($_FILES['foto']['name'])) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 2 * 1024 * 1024;
    $file_type = $_FILES['foto']['type'];
    $file_size = $_FILES['foto']['size'];
    $file_tmp = $_FILES['foto']['tmp_name'];
    $original_name = $_FILES['foto']['name'];

    if (!in_array($file_type, $allowed_types) || $file_size > $max_size) {
        header("Location: ../../views/admin/kelola_dosen.php?error=invalid_file");
        exit();
    }

    $upload_dir = __DIR__ . '/../../assets/img/dosen/';
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            header("Location: ../../views/admin/kelola_dosen.php?error=upload_dir");
            exit();
        }
    }

    if (!is_writable($upload_dir)) {
        header("Location: ../../views/admin/kelola_dosen.php?error=upload_dir");
        exit();
    }

    $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $slug = slugify_text($nama, 60);
    $file_name = 'dosen_' . $slug . '_' . time() . '.' . $file_ext;
    $file_path = $upload_dir . $file_name;

    if (move_uploaded_file($file_tmp, $file_path)) {
        // Hapus foto lama jika ada
        if (!empty($foto_path)) {
            $old_path = __DIR__ . '/../../' . $foto_path;
            if (file_exists($old_path) && is_file($old_path)) {
                unlink($old_path);
            }
        }
        $foto_path = 'assets/img/dosen/' . $file_name;
    } else {
        header("Location: ../../views/admin/kelola_dosen.php?error=upload_failed");
        exit();
    }
}

try {
    $stmt = $conn->prepare("UPDATE tbl_dosen SET nama = ?, gelar = ?, jabatan = ?, email = ?, foto_url = ?, deskripsi = ?, urutan = ?, status = ? WHERE id_dosen = ?");
    $stmt->bind_param("ssssssisi", $nama, $gelar, $jabatan, $email, $foto_path, $deskripsi, $urutan, $status, $id_dosen);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $stmt->close();
        header("Location: ../../views/admin/kelola_dosen.php?error=not_found");
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
            "Mengubah data dosen: $nama"
    
        );
    }

    header("Location: ../../views/admin/kelola_dosen.php?success=updated");
    exit();
} catch (Exception $e) {
    error_log('Gagal mengedit dosen: ' . $e->getMessage());
    header("Location: ../../views/admin/kelola_dosen.php?error=database_error");
    exit();
}

