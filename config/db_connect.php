<?php
// Setel informasi database Anda
$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'db_portal_tpl';

// $host = 'sql109.infinityfree.com';
// $user = 'if0_40385611';
// $pass = 'portaltpl123';
// $db_name = 'if0_40385611_portal_tpl';

// 1. Buat koneksi
$conn = mysqli_connect($host, $user, $pass, $db_name);

// 2. Cek koneksi
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Set timezone PHP dan MySQL ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');
mysqli_query($conn, "SET time_zone = '+07:00'");

// 3. Setel charset
mysqli_set_charset($conn, "utf8mb4");

// Fungsi global untuk log aktivitas
function logActivity($conn, $id_admin, $username, $action, $id_project = null) {
    $stmt = $conn->prepare(
        "INSERT INTO tbl_activity_logs (id_admin, username, action, id_project) 
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("issi", $id_admin, $username, $action, $id_project);
    $stmt->execute();
    $stmt->close();
}

// Fungsi global untuk log login
function logLoginAttempt($conn, $username, $ip, $status) {
    $status = ($status === 'Success') ? 'Success' : 'Failed';
    $stmt = $conn->prepare(
        "INSERT INTO tbl_admin_logs (username_attempt, ip_address, status) 
         VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $username, $ip, $status);
    $stmt->execute();
    $stmt->close();
}

// Fungsi untuk cek apakah IP sedang terkunci (database-based) - DIPERBAIKI
function isIPLocked($conn, $ip_address, $max_attempts = 5, $lockout_minutes = 10) {
    $stmt = $conn->prepare(
        "SELECT COUNT(*) as attempts 
         FROM tbl_admin_logs 
         WHERE ip_address = ? 
         AND status = 'Failed' 
         AND log_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)"
    );
    $stmt->bind_param("si", $ip_address, $lockout_minutes);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return ($row['attempts'] >= $max_attempts);
}

// Fungsi untuk reset failed attempts setelah berhasil login
function resetFailedAttempts($conn, $ip_address) {
    $stmt = $conn->prepare(
        "DELETE FROM tbl_admin_logs 
         WHERE ip_address = ? 
         AND status = 'Failed'"
    );
    $stmt->bind_param("s", $ip_address);
    $stmt->execute();
    $stmt->close();
}
?>