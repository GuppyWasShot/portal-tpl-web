<?php
session_start();

// Cek apakah sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$page_title = "Dashboard";
$current_page = 'index';
require_once __DIR__ . '/../../app/autoload.php';
$db = Database::getInstance()->getConnection();
$conn = $db; // Untuk backward compatibility
include __DIR__ . '/../layouts/header_admin.php';

// Helper function untuk format waktu WIB
function convertToWIB($datetime_string) {
    // Data di database sudah dalam WIB (karena MySQL timezone sudah di-set +07:00)
    // Jadi kita hanya perlu format saja, TIDAK perlu tambah 7 jam
    $timestamp = strtotime($datetime_string);
    return date('d M Y, H:i', $timestamp);
}

// Ambil statistik
$total_karya = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_project"))['total'];
$total_published = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_project WHERE status = 'Published'"))['total'];
$total_rating = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_rating"))['total'];

// Karya dengan rating tertinggi
$query_top_rated = "SELECT p.judul, AVG(r.skor) as avg_rating, COUNT(r.id_rating) as total_votes
                    FROM tbl_project p
                    LEFT JOIN tbl_rating r ON p.id_project = r.id_project
                    WHERE p.status = 'Published'
                    GROUP BY p.id_project
                    HAVING total_votes > 0
                    ORDER BY avg_rating DESC, total_votes DESC
                    LIMIT 1";
$top_rated = mysqli_fetch_assoc(mysqli_query($conn, $query_top_rated));

// Ambil karya terbaru
$query_karya = "SELECT p.*, 
                GROUP_CONCAT(c.nama_kategori SEPARATOR ', ') as kategori,
                AVG(r.skor) as avg_rating,
                COUNT(DISTINCT r.id_rating) as total_rating
                FROM tbl_project p
                LEFT JOIN tbl_project_category pc ON p.id_project = pc.id_project
                LEFT JOIN tbl_category c ON pc.id_kategori = c.id_kategori
                LEFT JOIN tbl_rating r ON p.id_project = r.id_project
                GROUP BY p.id_project
                ORDER BY p.id_project DESC
                LIMIT 10";
$result_karya = mysqli_query($conn, $query_karya);

// Ambil aktivitas terbaru
$query_activity = "SELECT * FROM tbl_activity_logs ORDER BY log_time DESC LIMIT 10";
$result_activity = mysqli_query($conn, $query_activity);
?>

<!-- Header -->
<header class="bg-white shadow-sm">
    <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Dashboard</h2>
        <p class="text-gray-600 mt-1 text-sm sm:text-base">Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
    </div>
</header>

<!-- Content -->
<div class="p-4 sm:p-6 lg:p-8">
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <!-- Total Karya -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Karya</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $total_karya; ?></p>
                    <p class="text-xs text-gray-400 mt-1">
                        <span class="text-green-600 font-semibold"><?php echo $total_published; ?></span> Published
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Karya Terpopuler (Rating Tertinggi) -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div class="flex-1 mr-2">
                    <p class="text-gray-500 text-sm font-medium">Karya Rating Tertinggi</p>
                    <?php if ($top_rated): ?>
                    <p class="text-lg font-bold text-gray-800 mt-2 line-clamp-2">
                        <?php echo htmlspecialchars($top_rated['judul']); ?>
                    </p>
                    <div class="flex items-center mt-1">
                        <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span class="text-sm font-semibold text-gray-700 ml-1">
                            <?php echo number_format($top_rated['avg_rating'], 1); ?>
                        </span>
                        <span class="text-xs text-gray-400 ml-1">
                            (<?php echo $top_rated['total_votes']; ?> votes)
                        </span>
                    </div>
                    <?php else: ?>
                    <p class="text-sm text-gray-500 mt-2">Belum ada rating</p>
                    <?php endif; ?>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Total Rating -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Penilaian</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $total_rating; ?></p>
                    <p class="text-xs text-gray-400 mt-1">Rating diterima</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Recent Projects (2/3 width) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Karya Terbaru</h3>
                    <a href="kelola_karya.php" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                        Lihat Semua →
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php while($row = mysqli_fetch_assoc($result_karya)): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($row['judul']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($row['pembuat']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($row['kategori'] ?? '-'); ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center">
                                        <span class="text-yellow-500 mr-1">⭐</span>
                                        <?php echo $row['avg_rating'] ? number_format($row['avg_rating'], 1) : '-'; ?>
                                        <span class="text-gray-400 ml-1">(<?php echo $row['total_rating']; ?>)</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php 
                                    $status_class = $row['status'] == 'Published' ? 'bg-green-100 text-green-800' : 
                                                   ($row['status'] == 'Draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800');
                                    ?>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Activity Log (1/3 width) -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Aktivitas Terbaru</h3>
                </div>
                
                <div class="p-4 max-h-96 overflow-y-auto">
                    <div class="space-y-4">
                        <?php while($activity = mysqli_fetch_assoc($result_activity)): ?>
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900">
                                    <span class="font-medium"><?php echo htmlspecialchars($activity['username']); ?></span>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <?php echo htmlspecialchars($activity['action']); ?>
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    <?php echo convertToWIB($activity['log_time']); ?>
                                </p>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    
</div>

<?php include __DIR__ . '/../layouts/footer_admin.php'; ?>