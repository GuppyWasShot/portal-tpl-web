<?php
/**
 * DEBUG VERSION - Proses Ubah Password dengan Logging
 * Gunakan file ini untuk debugging
 */

session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log function
function debug_log($message, $data = null) {
    $log_file = __DIR__ . '/../../debug_password_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message";
    if ($data !== null) {
        $log_message .= "\n" . print_r($data, true);
    }
    $log_message .= "\n" . str_repeat('-', 80) . "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

debug_log("=== START PROSES UBAH PASSWORD ===");

require_once __DIR__ . '/../../app/autoload.php';

// Cek session admin
debug_log("Checking session", $_SESSION);

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    debug_log("ERROR: Admin not logged in");
    $_SESSION['error_message'] = "Session tidak valid. Silakan login kembali.";
    header("Location: ../../views/admin/login.php");
    exit();
}

// Validasi method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    debug_log("ERROR: Not POST method", $_SERVER['REQUEST_METHOD']);
    header("Location: ../../views/admin/ubah_password.php");
    exit();
}

debug_log("POST Data received", $_POST);

$db = Database::getInstance()->getConnection();
$id_admin = $_SESSION['id_admin'];

// Ambil data dari form
$password_lama = $_POST['password_lama'] ?? '';
$password_baru = $_POST['password_baru'] ?? '';
$konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

debug_log("Form data", [
    'password_lama_length' => strlen($password_lama),
    'password_baru_length' => strlen($password_baru),
    'konfirmasi_password_length' => strlen($konfirmasi_password)
]);

// Validasi input
if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
    debug_log("ERROR: Empty fields");
    $_SESSION['error_message'] = "Semua field harus diisi!";
    header("Location: ../../views/admin/ubah_password.php");
    exit();
}

// Validasi panjang password baru
if (strlen($password_baru) < 6) {
    debug_log("ERROR: Password too short", strlen($password_baru));
    $_SESSION['error_message'] = "Password baru minimal 6 karakter!";
    header("Location: ../../views/admin/ubah_password.php");
    exit();
}

// Validasi konfirmasi password
if ($password_baru !== $konfirmasi_password) {
    debug_log("ERROR: Password mismatch");
    $_SESSION['error_message'] = "Password baru dan konfirmasi password tidak cocok!";
    header("Location: ../../views/admin/ubah_password.php");
    exit();
}

// Ambil password lama dari database
debug_log("Fetching admin data from database", ['id_admin' => $id_admin]);

$stmt = $db->prepare("SELECT password, username FROM tbl_admin WHERE id_admin = ?");
$stmt->bind_param("i", $id_admin);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin) {
    debug_log("ERROR: Admin not found in database");
    $_SESSION['error_message'] = "Admin tidak ditemukan!";
    header("Location: ../../views/admin/ubah_password.php");
    exit();
}

debug_log("Admin found", [
    'username' => $admin['username'],
    'password_hash_preview' => substr($admin['password'], 0, 30) . '...'
]);

// Verifikasi password lama
debug_log("Verifying old password");
$verify_result = password_verify($password_lama, $admin['password']);
debug_log("Password verify result", ['result' => $verify_result ? 'MATCH' : 'NO MATCH']);

if (!$verify_result) {
    debug_log("ERROR: Old password incorrect");
    $_SESSION['error_message'] = "Password lama tidak sesuai!";
    header("Location: ../../views/admin/ubah_password.php");
    exit();
}

// Hash password baru
debug_log("Hashing new password");
$password_baru_hash = password_hash($password_baru, PASSWORD_DEFAULT);
debug_log("New password hash", ['hash_preview' => substr($password_baru_hash, 0, 30) . '...']);

// Update password di database
debug_log("Updating password in database");
$stmt = $db->prepare("UPDATE tbl_admin SET password = ? WHERE id_admin = ?");
$stmt->bind_param("si", $password_baru_hash, $id_admin);

if ($stmt->execute()) {
    debug_log("SUCCESS: Password updated", ['affected_rows' => $stmt->affected_rows]);
    
    // Log aktivitas
    $username = $_SESSION['username'];
    $action = "Mengubah password akun";
    $log_stmt = $db->prepare("INSERT INTO tbl_activity_logs (id_admin, username, action) VALUES (?, ?, ?)");
    $log_stmt->bind_param("iss", $id_admin, $username, $action);
    $log_stmt->execute();
    debug_log("Activity logged");
    $log_stmt->close();
    
    $_SESSION['success_message'] = "Password berhasil diubah!";
} else {
    debug_log("ERROR: Failed to update password", ['error' => $stmt->error]);
    $_SESSION['error_message'] = "Gagal mengubah password. Error: " . $stmt->error;
}

$stmt->close();
debug_log("=== END PROSES UBAH PASSWORD ===");

header("Location: ../../views/admin/ubah_password.php");
exit();
?>
