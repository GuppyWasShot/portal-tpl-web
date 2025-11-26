<?php
/**
 * Proses Ubah Password Admin
 */

session_start();
require_once __DIR__ . '/../../app/autoload.php';

// Cek session admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['error_message'] = "Session tidak valid. Silakan login kembali.";
    header("Location: ../../views/admin/login.php");
    exit();
}

// Validasi method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../views/admin/ubah_password.php");
    exit();
}

$db = Database::getInstance()->getConnection();

// Fix: Gunakan nama session yang benar
$id_admin = $_SESSION['admin_id'] ?? null;
$username = $_SESSION['admin_username'] ?? null;

// Fallback untuk backward compatibility
if (!$id_admin && isset($_SESSION['id_admin'])) {
    $id_admin = $_SESSION['id_admin'];
}
if (!$username && isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
}

// Ambil data dari form
$password_lama = $_POST['password_lama'] ?? '';
$password_baru = $_POST['password_baru'] ?? '';
$konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

// Validasi input
if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
    $_SESSION['error_message'] = "Semua field harus diisi!";
    header("Location: ../../views/admin/ubah_password.php");
    exit();
}

// Validasi panjang password baru
if (strlen($password_baru) < 6) {
    $_SESSION['error_message'] = "Password baru minimal 6 karakter!";
    header("Location: ../../views/admin/ubah_password.php");
    exit();
}

// Validasi konfirmasi password
if ($password_baru !== $konfirmasi_password) {
    $_SESSION['error_message'] = "Password baru dan konfirmasi password tidak cocok!";
    header("Location: ../../views/admin/ubah_password.php");
    exit();
}

// Ambil password lama dari database
$stmt = $db->prepare("SELECT password FROM tbl_admin WHERE id_admin = ?");
$stmt->bind_param("i", $id_admin);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin) {
    $_SESSION['error_message'] = "Admin tidak ditemukan!";
    header("Location: ../../views/admin/ubah_password.php");
    exit();
}

// Verifikasi password lama
if (!password_verify($password_lama, $admin['password'])) {
    $_SESSION['error_message'] = "Password lama tidak sesuai!";
    header("Location: ../../views/admin/ubah_password.php");
    exit();
}

// Hash password baru
$password_baru_hash = password_hash($password_baru, PASSWORD_DEFAULT);

// Update password di database
$stmt = $db->prepare("UPDATE tbl_admin SET password = ? WHERE id_admin = ?");
$stmt->bind_param("si", $password_baru_hash, $id_admin);

if ($stmt->execute()) {
    // Log aktivitas (gunakan username yang sudah diambil dari session)
    $action = "Mengubah password akun";
    $log_stmt = $db->prepare("INSERT INTO tbl_activity_logs (id_admin, username, action) VALUES (?, ?, ?)");
    $log_stmt->bind_param("iss", $id_admin, $username, $action);
    $log_stmt->execute();
    $log_stmt->close();
    
    $_SESSION['success_message'] = "Password berhasil diubah!";
} else {
    $_SESSION['error_message'] = "Gagal mengubah password. Silakan coba lagi.";
}

$stmt->close();
header("Location: ../../views/admin/ubah_password.php");
exit();
?>
