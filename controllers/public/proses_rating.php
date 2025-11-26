<?php
/**
 * Proses Rating - Refactored dengan OOP
 * 
 * File ini menggunakan Class Rating untuk menangani logika rating
 * Clean separation: View hanya menerima input, Class menangani business logic
 */

session_start();

// Error reporting untuk debugging (nonaktifkan di production)
// UNCOMMENT 2 BARIS BERIKUT UNTUK DEBUGGING, COMMENT LAGI SETELAH SELESAI
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

// Autoload classes
require_once __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../config/db_connect.php';

// Helper function untuk get redirect URL (menggunakan path relatif yang lebih reliable)
function getRedirectUrl($id_project, $status, $message) {
    // Gunakan path relatif yang lebih sederhana
    $redirect = "../../views/public/detail_karya.php?id=" . intval($id_project);
    if ($status === 'success') {
        $redirect .= "&success=" . urlencode($message);
    } else {
        $redirect .= "&error=" . urlencode($message);
    }
    return $redirect;
}

// Validasi request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (ob_get_level()) {
        ob_end_clean();
    }
    header("Location: ../../views/public/galeri.php");
    exit();
}

// Generate atau ambil UUID user
if (!isset($_SESSION['user_uuid'])) {
    $_SESSION['user_uuid'] = uniqid('user_', true);
}

// Validasi input
$id_project = isset($_POST['id_project']) ? intval($_POST['id_project']) : 0;
$action = isset($_POST['action']) ? trim($_POST['action']) : 'submit';

if ($id_project <= 0) {
    if (ob_get_level()) {
        ob_end_clean();
    }
    header("Location: ../../views/public/galeri.php?error=invalid_id");
    exit();
}

$user_uuid = $_SESSION['user_uuid'];
$ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';

// Pastikan tidak ada output sebelum header
if (ob_get_level()) {
    ob_end_clean();
}

try {
    // Inisialisasi Class Rating
    $ratingModel = new Rating();

if ($action === 'cancel') {
    // Cancel rating (hapus rating)
    $success = $ratingModel->deleteRating($id_project, $user_uuid, $ip_address);
    if ($success) {
            header("Location: " . getRedirectUrl($id_project, 'success', 'rating_cancelled'));
    } else {
            error_log("Gagal cancel rating: id_project=$id_project, user_uuid=$user_uuid");
            header("Location: " . getRedirectUrl($id_project, 'error', 'rating_cancel_failed'));
    }
} else {
    // Submit atau update rating
    $skor = isset($_POST['skor']) ? intval($_POST['skor']) : 0;
        
        // Debug log
        error_log("Rating submission attempt: id_project=$id_project, skor=$skor, user_uuid=$user_uuid, ip=$ip_address");
        
        // Validasi skor
        if ($skor < 1 || $skor > 5) {
            error_log("Skor tidak valid: skor=$skor, id_project=$id_project");
            header("Location: " . getRedirectUrl($id_project, 'error', 'rating_failed'));
            exit();
        }
        
        // Validasi id_project
        if ($id_project <= 0) {
            error_log("ID project tidak valid: id_project=$id_project");
            header("Location: " . getRedirectUrl($id_project, 'error', 'rating_failed'));
            exit();
        }
        
        try {
            // Cek dulu apakah project ada dan published
            $db = Database::getInstance()->getConnection();
            $check_stmt = $db->prepare("SELECT id_project, status FROM tbl_project WHERE id_project = ?");
            if ($check_stmt) {
                $check_stmt->bind_param("i", $id_project);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $project_data = $check_result->fetch_assoc();
                $check_stmt->close();
                
                if (!$project_data) {
                    error_log("Project dengan ID $id_project tidak ditemukan di database");
                    header("Location: " . getRedirectUrl($id_project, 'error', 'rating_failed'));
                    exit();
                }
                
                if ($project_data['status'] !== 'Published') {
                    error_log("Project dengan ID $id_project statusnya bukan 'Published' (status: " . $project_data['status'] . ")");
                    header("Location: " . getRedirectUrl($id_project, 'error', 'rating_failed'));
                    exit();
                }
            }
            
    $success = $ratingModel->submitRating($id_project, $skor, $user_uuid, $ip_address);
            
            error_log("Rating submission result: success=" . ($success ? 'true' : 'false') . ", id_project=$id_project");
    
    // Redirect berdasarkan hasil
    if ($success) {
                // Cek apakah ini update atau insert baru
        $existingRating = $ratingModel->hasUserRated($id_project, $user_uuid, $ip_address);
        if ($existingRating) {
                    header("Location: " . getRedirectUrl($id_project, 'success', 'rating_updated'));
                } else {
                    header("Location: " . getRedirectUrl($id_project, 'success', 'rating_submitted'));
                }
        } else {
                error_log("Gagal submit rating: id_project=$id_project, skor=$skor, user_uuid=$user_uuid, ip=$ip_address");
                // Cek apakah tabel rating ada
                $table_check = $db->query("SHOW TABLES LIKE 'tbl_rating'");
                if ($table_check->num_rows === 0) {
                    error_log("Tabel tbl_rating tidak ditemukan di database!");
                }
                header("Location: " . getRedirectUrl($id_project, 'error', 'rating_failed'));
        }
        } catch (Exception $e) {
            error_log("Exception saat submit rating: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            header("Location: " . getRedirectUrl($id_project, 'error', 'rating_failed'));
        }
    }
} catch (Exception $e) {
    // Log error dan redirect dengan error message
    error_log("Error di proses_rating.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    header("Location: " . getRedirectUrl($id_project, 'error', 'rating_failed'));
}
exit();

