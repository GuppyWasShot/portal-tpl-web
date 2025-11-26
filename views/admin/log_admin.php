<?php
session_start();

// Cek apakah sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$page_title = "Log Admin";
$current_page = 'log_admin';
require_once __DIR__ . '/../../app/autoload.php';
$db = Database::getInstance()->getConnection();
$conn = $db;
include __DIR__ . '/../layouts/header_admin.php';

// Pagination settings
$per_page = 20;
$current_page_num = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Filter settings
$log_type = isset($_GET['type']) ? $_GET['type'] : 'all'; // all, activity, login
$status_filter = isset($_GET['status']) ? $_GET['status'] : ''; // Success, Failed
$date_filter = isset($_GET['date']) ? $_GET['date'] : ''; // YYYY-MM-DD

// Build query based on log type
if ($log_type === 'activity') {
    // Activity logs only
    $where_conditions = ["1=1"];
    if ($date_filter) {
        $where_conditions[] = "DATE(log_time) = '$date_filter'";
    }
    $where_clause = implode(' AND ', $where_conditions);
    
    $count_query = "SELECT COUNT(*) as total FROM tbl_activity_logs WHERE $where_clause";
    $total_logs = $conn->query($count_query)->fetch_assoc()['total'];
    $total_pages = ceil($total_logs / $per_page);
    
    $offset = ($current_page_num - 1) * $per_page;
    $query = "SELECT 'activity' as log_type, id_log, username, action, log_time, NULL as ip_address, NULL as status, id_project
              FROM tbl_activity_logs 
              WHERE $where_clause
              ORDER BY log_time DESC 
              LIMIT $per_page OFFSET $offset";
              
} elseif ($log_type === 'login') {
    // Login logs only
    $where_conditions = ["1=1"];
    if ($status_filter) {
        $where_conditions[] = "status = '$status_filter'";
    }
    if ($date_filter) {
        $where_conditions[] = "DATE(log_time) = '$date_filter'";
    }
    $where_clause = implode(' AND ', $where_conditions);
    
    $count_query = "SELECT COUNT(*) as total FROM tbl_admin_logs WHERE $where_clause";
    $total_logs = $conn->query($count_query)->fetch_assoc()['total'];
    $total_pages = ceil($total_logs / $per_page);
    
    $offset = ($current_page_num - 1) * $per_page;
    $query = "SELECT 'login' as log_type, id_log, username_attempt as username, NULL as action, log_time, ip_address, status, NULL as id_project
              FROM tbl_admin_logs 
              WHERE $where_clause
              ORDER BY log_time DESC 
              LIMIT $per_page OFFSET $offset";
              
} else {
    // All logs (UNION)
    $where_activity = ["1=1"];
    $where_login = ["1=1"];
    
    if ($date_filter) {
        $where_activity[] = "DATE(log_time) = '$date_filter'";
        $where_login[] = "DATE(log_time) = '$date_filter'";
    }
    if ($status_filter) {
        $where_login[] = "status = '$status_filter'";
    }
    
    $where_activity_clause = implode(' AND ', $where_activity);
    $where_login_clause = implode(' AND ', $where_login);
    
    $count_query = "(SELECT COUNT(*) as total FROM tbl_activity_logs WHERE $where_activity_clause) 
                    UNION ALL 
                    (SELECT COUNT(*) as total FROM tbl_admin_logs WHERE $where_login_clause)";
    $result = $conn->query($count_query);
    $total_logs = 0;
    while ($row = $result->fetch_assoc()) {
        $total_logs += $row['total'];
    }
    $total_pages = ceil($total_logs / $per_page);
    
    $offset = ($current_page_num - 1) * $per_page;
    $query = "(SELECT 'activity' as log_type, id_log, username, action, log_time, NULL as ip_address, NULL as status, id_project
               FROM tbl_activity_logs WHERE $where_activity_clause)
              UNION ALL
              (SELECT 'login' as log_type, id_log, username_attempt as username, NULL as action, log_time, ip_address, status, NULL as id_project
               FROM tbl_admin_logs WHERE $where_login_clause)
              ORDER BY log_time DESC
              LIMIT $per_page OFFSET $offset";
}

$result_logs = $conn->query($query);

// Get statistics
$total_activity = $conn->query("SELECT COUNT(*) as total FROM tbl_activity_logs")->fetch_assoc()['total'];
$total_login_success = $conn->query("SELECT COUNT(*) as total FROM tbl_admin_logs WHERE status = 'Success'")->fetch_assoc()['total'];
$total_login_failed = $conn->query("SELECT COUNT(*) as total FROM tbl_admin_logs WHERE status = 'Failed'")->fetch_assoc()['total'];
?>

<header class="bg-white shadow-sm">
    <div class="px-8 py-6">
        <h2 class="text-2xl font-bold text-gray-800">Log Admin</h2>
        <p class="text-gray-600 mt-1">Riwayat aktivitas dan login administrator</p>
    </div>
</header>

<div class="p-8">
    
   
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Log</label>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="all" <?php echo $log_type === 'all' ? 'selected' : ''; ?>>Semua Log</option>
                    <option value="activity" <?php echo $log_type === 'activity' ? 'selected' : ''; ?>>Aktivitas Saja</option>
                    <option value="login" <?php echo $log_type === 'login' ? 'selected' : ''; ?>>Login Saja</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Login</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="Success" <?php echo $status_filter === 'Success' ? 'selected' : ''; ?>>Berhasil</option>
                    <option value="Failed" <?php echo $status_filter === 'Failed' ? 'selected' : ''; ?>>Gagal</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    Filter
                </button>
                <a href="log_admin.php" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>
    
    <!-- Logs Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aktivitas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if ($result_logs && $result_logs->num_rows > 0): ?>
                        <?php while($log = $result_logs->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php 
                                // Data di database sudah dalam WIB (MySQL timezone sudah +07:00)
                                // Jadi hanya format saja, tidak perlu tambah 7 jam
                                echo date('d M Y, H:i:s', strtotime($log['log_time']));
                                ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php if ($log['log_type'] === 'activity'): ?>
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Aktivitas
                                </span>
                                <?php else: ?>
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Login
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($log['username']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php 
                                if ($log['log_type'] === 'activity') {
                                    echo htmlspecialchars($log['action']);
                                } else {
                                    echo $log['status'] === 'Success' ? 'Login berhasil' : 'Login gagal';
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 font-mono">
                                <?php echo $log['ip_address'] ? htmlspecialchars($log['ip_address']) : '-'; ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php if ($log['status']): ?>
                                    <?php if ($log['status'] === 'Success'): ?>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✓ Berhasil
                                    </span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        ✗ Gagal
                                    </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-lg font-medium">Tidak ada log ditemukan</p>
                                <p class="text-sm mt-1">Coba ubah filter atau reset pencarian</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Menampilkan <?php echo (($current_page_num - 1) * $per_page) + 1; ?> - 
                <?php echo min($current_page_num * $per_page, $total_logs); ?> dari 
                <?php echo $total_logs; ?> log
            </div>
            
            <div class="flex gap-2">
                <?php
                $query_params = $_GET;
                unset($query_params['page']);
                ?>
                
                <!-- Previous -->
                <?php if ($current_page_num > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $current_page_num - 1])); ?>" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    ← Prev
                </a>
                <?php endif; ?>
                
                <!-- Page Numbers -->
                <?php
                $start_page = max(1, $current_page_num - 2);
                $end_page = min($total_pages, $current_page_num + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <?php if ($i == $current_page_num): ?>
                    <span class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-indigo-600 rounded-lg">
                        <?php echo $i; ?>
                    </span>
                    <?php else: ?>
                    <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $i])); ?>" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        <?php echo $i; ?>
                    </a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <!-- Next -->
                <?php if ($current_page_num < $total_pages): ?>
                <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $current_page_num + 1])); ?>" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Next →
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
</div>

<?php include __DIR__ . '/../layouts/footer_admin.php'; ?>
