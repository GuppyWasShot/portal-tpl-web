<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Pastikan tidak ada output sebelum header
    if (ob_get_level()) {
        ob_end_clean();
    }
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script_path = dirname($_SERVER['PHP_SELF']); // /portal_tpl/controllers/admin
    $base_path = dirname(dirname($script_path)); // /portal_tpl
    header("Location: " . $protocol . '://' . $host . $base_path . '/views/admin/login.php?error=belum_login');
    exit();
}

require_once __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../config/db_connect.php';
$db = Database::getInstance()->getConnection();
$conn = $db;

$id_project = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_project <= 0) {
    // Pastikan tidak ada output sebelum header
    if (ob_get_level()) {
        ob_end_clean();
    }
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script_path = dirname($_SERVER['PHP_SELF']); // /portal_tpl/controllers/admin
    $base_path = dirname(dirname($script_path)); // /portal_tpl
    header("Location: " . $protocol . '://' . $host . $base_path . '/views/admin/kelola_karya.php?error=invalid_id');
    exit();
}

// Ambil data karya
$stmt = $conn->prepare("SELECT * FROM tbl_project WHERE id_project = ?");
$stmt->bind_param("i", $id_project);
$stmt->execute();
$result = $stmt->get_result();
$karya = $result->fetch_assoc();
$stmt->close();

if (!$karya) {
    // Pastikan tidak ada output sebelum header
    if (ob_get_level()) {
        ob_end_clean();
    }
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script_path = dirname($_SERVER['PHP_SELF']); // /portal_tpl/controllers/admin
    $base_path = dirname(dirname($script_path)); // /portal_tpl
    header("Location: " . $protocol . '://' . $host . $base_path . '/views/admin/kelola_karya.php?error=not_found');
    exit();
}

// Proses perubahan status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE tbl_project SET status = ? WHERE id_project = ?");
    $stmt->bind_param("si", $new_status, $id_project);
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
            "Mengubah status karya '" . $karya['judul'] . "' menjadi $new_status", 
            $id_project
        );
    }

    
    // Pastikan tidak ada output sebelum header
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Gunakan URL absolut untuk redirect
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script_path = dirname($_SERVER['PHP_SELF']); // /portal_tpl/controllers/admin
    $base_path = dirname(dirname($script_path)); // /portal_tpl
    $redirect_url = $protocol . '://' . $host . $base_path . '/views/admin/kelola_karya.php?success=status';
    
    header("Location: " . $redirect_url);
    exit();
}

// Helper function untuk get base URL
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script_path = dirname($_SERVER['PHP_SELF']);
    $base_path = dirname(dirname($script_path));
    return $protocol . '://' . $host . $base_path;
}

$base_url = getBaseUrl();
$page_title = "Ubah Status Karya";
$current_page = 'kelola_karya';
include __DIR__ . '/../../views/layouts/header_admin.php';
?>

<header class="bg-white shadow-sm">
    <div class="px-8 py-6">
        <div class="flex items-center text-sm text-gray-500 mb-2">
            <a href="<?php echo $base_url; ?>/views/admin/kelola_karya.php" class="hover:text-indigo-600">Kelola Karya</a>
            <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Ubah Status</span>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Ubah Status Karya</h2>
        <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($karya['judul']); ?></p>
    </div>
</header>

<div class="p-8">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-md p-8">
        
        <form method="POST" action="" class="space-y-6">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Status Saat Ini
                </label>
                <div class="px-4 py-3 bg-gray-50 rounded-lg">
                    <?php 
                    $status_class = $karya['status'] == 'Published' ? 'bg-green-100 text-green-800' : 
                                   ($karya['status'] == 'Draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800');
                    ?>
                    <span class="inline-block px-3 py-1 rounded-full text-sm font-medium <?php echo $status_class; ?>">
                        <?php echo htmlspecialchars($karya['status']); ?>
                    </span>
                </div>
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                    Ubah Ke Status <span class="text-red-500">*</span>
                </label>
                <select id="status" name="status" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="Draft" <?php echo $karya['status'] == 'Draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="Published" <?php echo $karya['status'] == 'Published' ? 'selected' : ''; ?>>Published</option>
                    <option value="Hidden" <?php echo $karya['status'] == 'Hidden' ? 'selected' : ''; ?>>Hidden</option>
                </select>
                <p class="text-xs text-gray-500 mt-2">
                    <strong>Draft:</strong> Tidak tampil di publik<br>
                    <strong>Published:</strong> Tampil di website publik<br>
                    <strong>Hidden:</strong> Disembunyikan sementara dari publik
                </p>
            </div>
            
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="<?php echo $base_url; ?>/views/admin/kelola_karya.php" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    Simpan Perubahan
                </button>
            </div>
            
        </form>
        
    </div>
</div>

<?php include __DIR__ . '/../../views/layouts/footer_admin.php'; ?>