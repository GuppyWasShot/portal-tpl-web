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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../views/admin/kelola_karya.php?tab=tambah&error=invalid_request");
    exit();
}

// Ambil data dari form
$judul = trim($_POST['judul']);
$pembuat = trim($_POST['pembuat']);
$deskripsi = trim($_POST['deskripsi']);
$tanggal_selesai = $_POST['tanggal_selesai'];
$link_utama = trim($_POST['link_utama']);
$kategori_array = isset($_POST['kategori']) ? $_POST['kategori'] : [];
$action = $_POST['action']; // draft atau publish

// Tentukan status
$status = ($action === 'publish') ? 'Published' : 'Draft';

// Validasi input wajib
if (empty($judul) || empty($pembuat) || empty($deskripsi) || empty($tanggal_selesai) || empty($link_utama)) {
    header("Location: ../../views/admin/form_tambah_karya.php?error=empty_field");
    exit();
}

// Siapkan data link tambahan
$link_labels = isset($_POST['link_label']) ? $_POST['link_label'] : [];
$link_urls = isset($_POST['link_url']) ? $_POST['link_url'] : [];

// Begin transaction
mysqli_begin_transaction($conn);

try {
    // 1. Insert ke tbl_project - DIPERBAIKI SYNTAX ERROR
    $stmt = $conn->prepare(
        "INSERT INTO tbl_project (judul, deskripsi, pembuat, tanggal_selesai, status, link_external) 
         VALUES (?, ?, ?, ?, ?, NULL)"
    );
    $stmt->bind_param("sssss", $judul, $deskripsi, $pembuat, $tanggal_selesai, $status);
    $stmt->execute();
    $id_project = $stmt->insert_id;
    $stmt->close();
    
    // 2. Insert link utama ke tbl_project_links
    $stmt_link = $conn->prepare(
        "INSERT INTO tbl_project_links (id_project, label, url, is_primary) VALUES (?, ?, ?, 1)"
    );
    $label_utama = "Link Project";
    $stmt_link->bind_param("iss", $id_project, $label_utama, $link_utama);
    $stmt_link->execute();
    $stmt_link->close();
    
    // 3. Insert link tambahan
    if (!empty($link_labels) && !empty($link_urls)) {
        $stmt_link = $conn->prepare(
            "INSERT INTO tbl_project_links (id_project, label, url, is_primary) VALUES (?, ?, ?, 0)"
        );
        
        foreach ($link_labels as $idx => $label) {
            if (!empty($label) && !empty($link_urls[$idx])) {
                $stmt_link->bind_param("iss", $id_project, $label, $link_urls[$idx]);
                $stmt_link->execute();
            }
        }
        $stmt_link->close();
    }
    
    // 4. Insert kategori
    $stmt_kategori = $conn->prepare(
        "INSERT INTO tbl_project_category (id_project, id_kategori) VALUES (?, ?)"
    );
    
    foreach ($kategori_array as $id_kategori) {
        $stmt_kategori->bind_param("ii", $id_project, $id_kategori);
        $stmt_kategori->execute();
    }
    $stmt_kategori->close();
    
    // 5. Handle multiple snapshot upload
    if (isset($_FILES['snapshots']) && !empty($_FILES['snapshots']['name'][0])) {
        // Gunakan path absolut untuk kompatibilitas dengan hosting
        $upload_dir = __DIR__ . '/../../uploads/snapshots/';
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                throw new Exception("Gagal membuat folder uploads/snapshots. Pastikan folder uploads memiliki permission write.");
            }
        }
        
        // Pastikan folder writable
        if (!is_writable($upload_dir)) {
            throw new Exception("Folder uploads/snapshots tidak memiliki permission write. Hubungi administrator.");
        }
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        foreach ($_FILES['snapshots']['name'] as $idx => $original_name) {
            if ($_FILES['snapshots']['error'][$idx] === UPLOAD_ERR_OK) {
                $file_type = $_FILES['snapshots']['type'][$idx];
                $file_size = $_FILES['snapshots']['size'][$idx];
                $file_tmp = $_FILES['snapshots']['tmp_name'][$idx];
                
                // Validasi tipe dan ukuran
                if (!in_array($file_type, $allowed_types)) {
                    continue; // Skip file yang tidak valid
                }
                
                if ($file_size > $max_size) {
                    continue; // Skip file yang terlalu besar
                }
                
                // Generate nama file unik
                $file_ext = pathinfo($original_name, PATHINFO_EXTENSION);
                $file_name = 'snapshot_' . $id_project . '_' . time() . '_' . $idx . '.' . $file_ext;
                $file_path = $upload_dir . $file_name;
                
                // Upload file
                if (move_uploaded_file($file_tmp, $file_path)) {
                    $db_path = 'uploads/snapshots/' . $file_name;
                    
                    // Simpan ke database sebagai file
                    $stmt_snapshot = $conn->prepare(
                        "INSERT INTO tbl_project_files (id_project, label, nama_file, file_path, file_size, mime_type) 
                         VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $label_snapshot = "Snapshot " . ($idx + 1);
                    $stmt_snapshot->bind_param("isssis", $id_project, $label_snapshot, $original_name, $db_path, $file_size, $file_type);
                    $stmt_snapshot->execute();
                    $stmt_snapshot->close();
                    
                    // Update snapshot_url di tbl_project (hanya yang pertama)
                    if ($idx === 0) {
                        $stmt_update = $conn->prepare("UPDATE tbl_project SET snapshot_url = ? WHERE id_project = ?");
                        $stmt_update->bind_param("si", $db_path, $id_project);
                        $stmt_update->execute();
                        $stmt_update->close();
                    }
                } else {
                    // Log error upload tapi lanjutkan proses
                    error_log("Gagal upload snapshot: " . $original_name . " - Error: " . $_FILES['snapshots']['error'][$idx]);
                }
            } else {
                // Log error upload
                $error_messages = [
                    UPLOAD_ERR_INI_SIZE => 'File melebihi upload_max_filesize',
                    UPLOAD_ERR_FORM_SIZE => 'File melebihi MAX_FILE_SIZE',
                    UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
                    UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
                    UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
                    UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
                    UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh extension'
                ];
                $error_msg = isset($error_messages[$_FILES['snapshots']['error'][$idx]]) 
                    ? $error_messages[$_FILES['snapshots']['error'][$idx]] 
                    : 'Unknown error';
                error_log("Upload error untuk snapshot: " . $original_name . " - " . $error_msg);
            }
        }
    }
    
    // 6. Handle file pendukung upload
    if (isset($_FILES['file_upload']) && !empty($_FILES['file_upload']['name'][0])) {
        $file_labels = isset($_POST['file_label']) ? $_POST['file_label'] : [];
        
        // Gunakan path absolut untuk kompatibilitas dengan hosting
        $upload_dir = __DIR__ . '/../../uploads/files/';
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                throw new Exception("Gagal membuat folder uploads/files. Pastikan folder uploads memiliki permission write.");
            }
        }
        
        // Pastikan folder writable
        if (!is_writable($upload_dir)) {
            throw new Exception("Folder uploads/files tidak memiliki permission write. Hubungi administrator.");
        }
        
        $stmt_file = $conn->prepare(
            "INSERT INTO tbl_project_files (id_project, label, nama_file, file_path, file_size, mime_type) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        foreach ($_FILES['file_upload']['name'] as $idx => $original_name) {
            if ($_FILES['file_upload']['error'][$idx] === UPLOAD_ERR_OK) {
                $file_size = $_FILES['file_upload']['size'][$idx];
                $file_type = $_FILES['file_upload']['type'][$idx];
                $file_tmp = $_FILES['file_upload']['tmp_name'][$idx];
                
                // Validasi ukuran (max 5MB)
                if ($file_size > 5 * 1024 * 1024) {
                    continue; // Skip file yang terlalu besar
                }
                
                // Generate nama file unik
                $file_ext = pathinfo($original_name, PATHINFO_EXTENSION);
                $file_name = 'file_' . $id_project . '_' . time() . '_' . $idx . '.' . $file_ext;
                $file_path = $upload_dir . $file_name;
                
                // Upload file
                if (move_uploaded_file($file_tmp, $file_path)) {
                    $label = !empty($file_labels[$idx]) ? $file_labels[$idx] : $original_name;
                    $db_path = 'uploads/files/' . $file_name;
                    
                    $stmt_file->bind_param("isssis", $id_project, $label, $original_name, $db_path, $file_size, $file_type);
                    $stmt_file->execute();
                } else {
                    error_log("Gagal upload file: " . $original_name . " - Error: " . $_FILES['file_upload']['error'][$idx]);
                }
            } else {
                $error_messages = [
                    UPLOAD_ERR_INI_SIZE => 'File melebihi upload_max_filesize',
                    UPLOAD_ERR_FORM_SIZE => 'File melebihi MAX_FILE_SIZE',
                    UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
                    UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
                    UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
                    UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
                    UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh extension'
                ];
                $error_msg = isset($error_messages[$_FILES['file_upload']['error'][$idx]]) 
                    ? $error_messages[$_FILES['file_upload']['error'][$idx]] 
                    : 'Unknown error';
                error_log("Upload error untuk file: " . $original_name . " - " . $error_msg);
            }
        }
        $stmt_file->close();
    }
    
    // 7. Pastikan snapshot_url terisi jika masih NULL
    // Cek apakah snapshot_url masih NULL atau kosong
    $stmt_check = $conn->prepare("SELECT snapshot_url FROM tbl_project WHERE id_project = ?");
    $stmt_check->bind_param("i", $id_project);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $project_data = $result_check->fetch_assoc();
    $stmt_check->close();
    
    // Jika snapshot_url NULL atau kosong, ambil snapshot pertama dari tbl_project_files
    if (empty($project_data['snapshot_url'])) {
        $stmt_snapshot = $conn->prepare(
            "SELECT file_path FROM tbl_project_files 
             WHERE id_project = ? AND file_path LIKE 'uploads/snapshots/%' 
             ORDER BY id_file ASC LIMIT 1"
        );
        $stmt_snapshot->bind_param("i", $id_project);
        $stmt_snapshot->execute();
        $result_snapshot = $stmt_snapshot->get_result();
        $snapshot_data = $result_snapshot->fetch_assoc();
        $stmt_snapshot->close();
        
        // Update snapshot_url jika ada snapshot
        if ($snapshot_data && !empty($snapshot_data['file_path'])) {
            $stmt_update_snapshot = $conn->prepare("UPDATE tbl_project SET snapshot_url = ? WHERE id_project = ?");
            $stmt_update_snapshot->bind_param("si", $snapshot_data['file_path'], $id_project);
            $stmt_update_snapshot->execute();
            $stmt_update_snapshot->close();
        }
    }
    
    // 8. Log aktivitas
    // Fix: Support both old and new session variable names
    $admin_id_log = $_SESSION['admin_id'] ?? $_SESSION['id_admin'] ?? null;
    $admin_username_log = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'Unknown';
    
    if ($admin_id_log) {
        logActivity(
            $conn, 
            $admin_id_log, 
            $admin_username_log, 
            "Menambahkan karya: $judul (Status: $status)", 
            $id_project
        );
    }

    
    // Commit transaction
    mysqli_commit($conn);
    
    header("Location: ../../views/admin/kelola_karya.php?success=tambah");
    exit();
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    
    // Hapus file yang sudah diupload jika ada error
    if (isset($upload_dir) && is_dir($upload_dir)) {
        $files = glob($upload_dir . '*_' . $id_project . '_*');
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        }
    }
    
    header("Location: ../../views/admin/form_tambah_karya.php?error=database_error&msg=" . urlencode($e->getMessage()));
    exit();
}

$conn->close();
?>