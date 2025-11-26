<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../../views/admin/login.php?error=belum_login");
    exit();
}

require_once __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../config/db_connect.php';
$db = Database::getInstance()->getConnection();
$conn = $db;

$id_file = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_project = isset($_GET['project']) ? intval($_GET['project']) : 0;

if ($id_file <= 0 || $id_project <= 0) {
    header("Location: ../../views/admin/kelola_karya.php?error=invalid_id");
    exit();
}

// Ambil data file
$stmt = $conn->prepare("SELECT * FROM tbl_project_files WHERE id_file = ?");
$stmt->bind_param("i", $id_file);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();
$stmt->close();

if (!$file) {
    header("Location: ../../views/admin/form_edit_karya.php?id=$id_project&error=file_not_found");
    exit();
}

mysqli_begin_transaction($conn);

try {
    // Hapus file fisik
    $file_path = __DIR__ . '/../../' . $file['file_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    // Cek apakah file yang dihapus adalah snapshot yang sedang digunakan di snapshot_url
    $is_snapshot = strpos($file['file_path'], 'snapshots') !== false;
    
    if ($is_snapshot) {
        // Ambil snapshot_url saat ini dari project
        $stmt_check = $conn->prepare("SELECT snapshot_url FROM tbl_project WHERE id_project = ?");
        $stmt_check->bind_param("i", $id_project);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $project = $result_check->fetch_assoc();
        $stmt_check->close();
        
        // Jika snapshot yang dihapus adalah snapshot_url yang aktif
        if ($project && $project['snapshot_url'] === $file['file_path']) {
            // Hapus dari database dulu
            $stmt = $conn->prepare("DELETE FROM tbl_project_files WHERE id_file = ?");
            $stmt->bind_param("i", $id_file);
            $stmt->execute();
            $stmt->close();
            
            // Cari snapshot lain yang masih ada
            $stmt_find = $conn->prepare(
                "SELECT file_path FROM tbl_project_files 
                 WHERE id_project = ? AND file_path LIKE 'uploads/snapshots/%' 
                 ORDER BY id_file ASC LIMIT 1"
            );
            $stmt_find->bind_param("i", $id_project);
            $stmt_find->execute();
            $result_find = $stmt_find->get_result();
            $new_snapshot = $result_find->fetch_assoc();
            $stmt_find->close();
            
            // Update snapshot_url
            if ($new_snapshot && !empty($new_snapshot['file_path'])) {
                // Ada snapshot lain, gunakan itu
                $stmt_update = $conn->prepare("UPDATE tbl_project SET snapshot_url = ? WHERE id_project = ?");
                $stmt_update->bind_param("si", $new_snapshot['file_path'], $id_project);
                $stmt_update->execute();
                $stmt_update->close();
            } else {
                // Tidak ada snapshot lain, set NULL
                $stmt_update = $conn->prepare("UPDATE tbl_project SET snapshot_url = NULL WHERE id_project = ?");
                $stmt_update->bind_param("i", $id_project);
                $stmt_update->execute();
                $stmt_update->close();
            }
        } else {
            // Snapshot yang dihapus bukan yang aktif, hapus saja
            $stmt = $conn->prepare("DELETE FROM tbl_project_files WHERE id_file = ?");
            $stmt->bind_param("i", $id_file);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        // Bukan snapshot, hapus biasa
        $stmt = $conn->prepare("DELETE FROM tbl_project_files WHERE id_file = ?");
        $stmt->bind_param("i", $id_file);
        $stmt->execute();
        $stmt->close();
    }

    // Fix: Support both old and new session variable names
    $admin_id_log = $_SESSION['admin_id'] ?? $_SESSION['id_admin'] ?? null;
    $admin_username_log = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'Unknown';

    if ($admin_id_log) {
        logActivity(
            $conn, 
            $admin_id_log, 
            $admin_username_log, 
            "Menghapus file: " . $file['nama_file'] . " dari project ID: $id_project"
        );
    }
    
    mysqli_commit($conn);
    
    header("Location: ../../views/admin/form_edit_karya.php?id=$id_project&success=file_deleted");
    exit();
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    error_log('Gagal menghapus file: ' . $e->getMessage());
    header("Location: ../../views/admin/form_edit_karya.php?id=$id_project&error=delete_failed");
    exit();
}
?>