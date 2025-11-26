<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../config/db_connect.php';
$db = Database::getInstance()->getConnection();
$conn = $db; // Untuk backward compatibility dengan fungsi lama

// Error reporting untuk debugging (nonaktifkan di production)
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

// Ambil IP address pengguna
$ip_address = $_SERVER['REMOTE_ADDR'];

// Cek apakah IP sedang terkunci (database-based, bukan session)
if (isIPLocked($conn, $ip_address)) {
    header("Location: ../../views/admin/login.php?error=terkunci");
    exit();
}

// Validasi input POST
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header("Location: ../../views/admin/login.php?error=input_kosong");
    exit();
}

$username = trim($_POST['username']);
$password = $_POST['password'];

// Validasi input tidak boleh kosong
if (empty($username) || empty($password)) {
    header("Location: ../../views/admin/login.php?error=input_kosong");
    exit();
}

// Cek username di database (Gunakan Prepared Statement)
$stmt = $conn->prepare("SELECT id_admin, username, password FROM tbl_admin WHERE username = ?");
if (!$stmt) {
    header("Location: ../../views/admin/login.php?error=database_error");
    exit();
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();
    
    // Verifikasi password HASH
    if (password_verify($password, $admin['password'])) {
        // --- LOGIN BERHASIL ---
        
        // Catat log BERHASIL
        try {
            logLoginAttempt($conn, $username, $ip_address, 'Success');
        } catch (Exception $e) {
            // Log error tapi lanjutkan proses login
        }
        
        // Reset counter gagal dari database
        try {
            resetFailedAttempts($conn, $ip_address);
        } catch (Exception $e) {
            // Log error tapi lanjutkan proses login
        }
        
        // Buat session
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id_admin'];
        $_SESSION['admin_username'] = $admin['username'];
        
        // Log aktivitas login
        try {
            logActivity($conn, $admin['id_admin'], $admin['username'], 'Login ke sistem');
        } catch (Exception $e) {
            // Log error tapi lanjutkan proses login
        }
        
        // Pastikan tidak ada output sebelum header
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Arahkan ke dashboard
        header("Location: ../../views/admin/index.php");
        exit();
        
    } else {
        // Password salah
        gagalLogin($conn, $username, $ip_address);
    }
    
} else {
    // Username tidak ditemukan
    gagalLogin($conn, $username, $ip_address);
}

// Fungsi untuk menangani login gagal
function gagalLogin($conn, $username, $ip) {
    // Catat log GAGAL
    try {
        logLoginAttempt($conn, $username, $ip, 'Failed');
    } catch (Exception $e) {
        // Log error tapi lanjutkan
    }
    
    // Cek berapa kali sudah gagal
    $stmt = $conn->prepare(
        "SELECT COUNT(*) as attempts 
         FROM tbl_admin_logs 
         WHERE ip_address = ? 
         AND status = 'Failed' 
         AND log_time > DATE_SUB(NOW(), INTERVAL 10 MINUTE)"
    );
    
    if ($stmt) {
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        $remaining = 5 - (int)$row['attempts'];
        
        if ($remaining <= 0) {
            $redirect = "../../views/admin/login.php?error=terkunci";
        } else {
            $redirect = "../../views/admin/login.php?error=gagal&sisa=$remaining";
        }
    } else {
        $redirect = "../../views/admin/login.php?error=gagal";
    }
    
    // Pastikan tidak ada output sebelum header
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header("Location: " . $redirect);
    exit();
}

// Close statement jika masih terbuka
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}

// Jangan close connection karena menggunakan singleton
// $conn->close(); // HAPUS - singleton connection tidak boleh di-close
?>