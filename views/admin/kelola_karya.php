<?php
session_start();
$page_title = "Kelola Karya";
$current_page = 'kelola_karya';
require_once __DIR__ . '/../../app/autoload.php';
$db = Database::getInstance()->getConnection();
$conn = $db;
include __DIR__ . '/../layouts/header_admin.php';

// Ambil parameter sorting
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'id_project';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validasi sort_by untuk keamanan
$allowed_sort = ['id_project', 'judul', 'pembuat', 'tanggal_selesai', 'status', 'avg_rating', 'total_rating'];
if (!in_array($sort_by, $allowed_sort)) {
    $sort_by = 'id_project';
}

// Validasi sort_order
$sort_order = strtoupper($sort_order);
if ($sort_order !== 'ASC' && $sort_order !== 'DESC') {
    $sort_order = 'DESC';
}

// Pagination settings
$per_page = 15; // Items per page
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Build WHERE conditions
$where_conditions = [];
$params = [];
$types = "";

// No additional filters for now, but can be added later
$where_clause = "1=1"; // Always true, placeholder

// Count total untuk pagination
$count_query = "SELECT COUNT(DISTINCT p.id_project) as total
                FROM tbl_project p
                LEFT JOIN tbl_project_category pc ON p.id_project = pc.id_project
                LEFT JOIN tbl_category c ON pc.id_kategori = c.id_kategori
                LEFT JOIN tbl_rating r ON p.id_project = r.id_project
                WHERE $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_karya = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_karya / $per_page);

// Ambil karya dengan pagination
$offset = ($current_page - 1) * $per_page;
$query_all_karya = "SELECT p.*, 
                    GROUP_CONCAT(DISTINCT c.nama_kategori ORDER BY c.nama_kategori SEPARATOR ', ') as kategori,
                    GROUP_CONCAT(DISTINCT c.warna_hex ORDER BY c.nama_kategori SEPARATOR ',') as warna,
                    AVG(r.skor) as avg_rating,
                    COUNT(DISTINCT r.id_rating) as total_rating
                    FROM tbl_project p
                    LEFT JOIN tbl_project_category pc ON p.id_project = pc.id_project
                    LEFT JOIN tbl_category c ON pc.id_kategori = c.id_kategori
                    LEFT JOIN tbl_rating r ON p.id_project = r.id_project
                    WHERE $where_clause
                    GROUP BY p.id_project
                    ORDER BY $sort_by $sort_order
                    LIMIT $per_page OFFSET $offset";
$result_all_karya = mysqli_query($conn, $query_all_karya);
?>

<header class="bg-white shadow-sm">
    <div class="px-8 py-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Kelola Karya</h2>
            <p class="text-gray-600 mt-1">Manajemen karya mahasiswa TPL</p>
        </div>
        <a href="form_tambah_karya.php" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Karya
        </a>
    </div>
</header>

<div class="p-8">
    
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        
        <?php if(isset($_GET['success'])): ?>
        <div class="p-4 m-6 mb-0 rounded-lg border bg-green-50 border-green-200 text-green-800 alert-auto-hide">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-green-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium">
                    <?php 
                    if($_GET['success'] == 'tambah') echo 'Karya berhasil ditambahkan!';
                    elseif($_GET['success'] == 'edit') echo 'Karya berhasil diperbarui!';
                    elseif($_GET['success'] == 'hapus') echo 'Karya berhasil dihapus!';
                    elseif($_GET['success'] == 'status') echo 'Status karya berhasil diubah!';
                    ?>
                </span>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="p-6">
            
            <div class="mb-6 flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" id="searchInput" placeholder="Cari judul atau pembuat..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           onkeyup="filterTable()">
                </div>
                <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        onchange="filterTable()">
                    <option value="">Semua Status</option>
                    <option value="Published">Published</option>
                    <option value="Draft">Draft</option>
                    <option value="Hidden">Hidden</option>
                </select>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full" id="karyaTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <a href="?sort=judul&order=<?php echo ($sort_by == 'judul' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>&page=<?php echo $current_page; ?>" 
                                   class="flex items-center hover:text-indigo-600 transition">
                                    Judul
                                    <?php if($sort_by == 'judul'): ?>
                                        <span class="ml-1 text-indigo-600"><?php echo $sort_order == 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <a href="?sort=pembuat&order=<?php echo ($sort_by == 'pembuat' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>&page=<?php echo $current_page; ?>" 
                                   class="flex items-center hover:text-indigo-600 transition">
                                    Pembuat
                                    <?php if($sort_by == 'pembuat'): ?>
                                        <span class="ml-1 text-indigo-600"><?php echo $sort_order == 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <a href="?sort=avg_rating&order=<?php echo ($sort_by == 'avg_rating' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>&page=<?php echo $current_page; ?>" 
                                   class="flex items-center hover:text-indigo-600 transition">
                                    Rating
                                    <?php if($sort_by == 'avg_rating'): ?>
                                        <span class="ml-1 text-indigo-600"><?php echo $sort_order == 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <a href="?sort=status&order=<?php echo ($sort_by == 'status' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>&page=<?php echo $current_page; ?>" 
                                   class="flex items-center hover:text-indigo-600 transition">
                                    Status
                                    <?php if($sort_by == 'status'): ?>
                                        <span class="ml-1 text-indigo-600"><?php echo $sort_order == 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <?php while($karya = mysqli_fetch_assoc($result_all_karya)): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($karya['judul']); ?>
                                </div>
                                <?php if($karya['tanggal_selesai']): ?>
                                <div class="text-xs text-gray-500">
                                    <?php echo date('Y', strtotime($karya['tanggal_selesai'])); ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php echo htmlspecialchars($karya['pembuat']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php 
                                if($karya['kategori']):
                                    $kategori_arr = explode(', ', $karya['kategori']);
                                    $warna_arr = explode(',', $karya['warna']);
                                    foreach($kategori_arr as $idx => $kat):
                                        $warna = $warna_arr[$idx] ?? '#6B7280';
                                ?>
                                <span class="inline-block px-2 py-1 text-xs font-medium rounded-full mr-1 mb-1" 
                                      style="background-color: <?php echo $warna; ?>20; color: <?php echo $warna; ?>">
                                    <?php echo htmlspecialchars($kat); ?>
                                </span>
                                <?php 
                                    endforeach;
                                else: 
                                ?>
                                <span class="text-gray-400 text-sm">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex items-center">
                                    <span class="text-yellow-500 mr-1">⭐</span>
                                    <?php echo $karya['avg_rating'] ? number_format($karya['avg_rating'], 1) : '-'; ?>
                                    <span class="text-gray-400 text-xs ml-1">(<?php echo $karya['total_rating']; ?>)</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php 
                                $status_class = $karya['status'] == 'Published' ? 'bg-green-100 text-green-800' : 
                                               ($karya['status'] == 'Draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800');
                                ?>
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($karya['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex items-center justify-center">
                                    <div class="relative inline-block text-left">
                                        <button type="button" onclick="toggleMenu(event, <?php echo $karya['id_project']; ?>)" 
                                                class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                            </svg>
                                        </button>
                                        
                                        <div id="menu-<?php echo $karya['id_project']; ?>" 
                                             class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-lg shadow-2xl bg-white ring-1 ring-black ring-opacity-5 z-50">
                                            <div class="py-1">
                                                <a href="form_edit_karya.php?id=<?php echo $karya['id_project']; ?>" 
                                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit Karya
                                                </a>
                                                <a href="../../controllers/admin/change_status.php?id=<?php echo $karya['id_project']; ?>" 
                                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                    </svg>
                                                    Ubah Status
                                                </a>
                                                <hr class="my-1">
                                                <a href="../../controllers/admin/hapus_karya.php?id=<?php echo $karya['id_project']; ?>" 
                                                   onclick="return confirmDelete('<?php echo htmlspecialchars($karya['judul']); ?>')"
                                                   class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Hapus Karya
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Info -->
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Menampilkan <strong><?php echo (($current_page - 1) * $per_page) + 1; ?></strong> - 
                    <strong><?php echo min($current_page * $per_page, $total_karya); ?></strong> dari 
                    <strong><?php echo $total_karya; ?></strong> karya
                </div>
                
                <?php if ($total_pages > 1): ?>
                <div class="flex gap-2">
                    <?php
                    // Build query params
                    $query_params = $_GET;
                    unset($query_params['page']);
                    ?>
                    
                    <!-- Previous Button -->
                    <?php if ($current_page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $current_page - 1])); ?>" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        ← Prev
                    </a>
                    <?php else: ?>
                    <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                        ← Prev
                    </span>
                    <?php endif; ?>
                    
                    <!-- Page Numbers -->
                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    if ($start_page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => 1])); ?>" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            1
                        </a>
                        <?php if ($start_page > 2): ?>
                        <span class="px-4 py-2 text-sm font-medium text-gray-400">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <?php if ($i == $current_page): ?>
                        <span class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-indigo-600 rounded-lg">
                            <?php echo $i; ?>
                        </span>
                        <?php else: ?>
                        <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $i])); ?>" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            <?php echo $i; ?>
                        </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                        <span class="px-4 py-2 text-sm font-medium text-gray-400">...</span>
                        <?php endif; ?>
                        <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $total_pages])); ?>" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            <?php echo $total_pages; ?>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Next Button -->
                    <?php if ($current_page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $current_page + 1])); ?>" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Next →
                    </a>
                    <?php else: ?>
                    <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                        Next →
                    </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</div>

<script>
function toggleMenu(event, id) {
    // 1. Hentikan event agar tidak langsung ditangkap oleh document 'click'
    event.stopPropagation();
    
    const menu = document.getElementById('menu-' + id);
    const button = event.currentTarget;
    const isHidden = menu.classList.contains('hidden');

    // 2. Tutup semua menu lain terlebih dahulu
    document.querySelectorAll('[id^="menu-"]').forEach(m => {
        if (m.id !== 'menu-' + id) {
            m.classList.add('hidden');
        }
    });

    // 3. Jika menu ini akan dibuka
    if (isHidden) {
        // 4. Pindahkan menu ke <body> agar lolos dari 'overflow'
        document.body.appendChild(menu);
        
        // 5. Dapatkan posisi tombol yang diklik
        const rect = button.getBoundingClientRect();

        // 6. Atur style menu untuk diposisikan secara 'fixed'
        // 'fixed' berarti posisinya relatif terhadap layar (viewport)
        menu.style.position = 'fixed';
        menu.style.top = `${rect.bottom + 4}px`; // 4px di bawah tombol
        menu.style.left = 'auto'; // Hapus 'left' jika ada
        menu.style.right = `${window.innerWidth - rect.right}px`; // Sejajarkan ke kanan tombol
        
        menu.classList.remove('hidden');
    } else {
        // Jika sudah terbuka, tutup saja
        menu.classList.add('hidden');
    }
}

// Listener ini berfungsi untuk menutup menu jika user klik di luar area menu
document.addEventListener('click', function(e) {
    // Cek apakah klik *tidak* di dalam menu DAN *tidak* pada tombol
    if (!e.target.closest('[id^="menu-"]') && !e.target.closest('button[onclick^="toggleMenu"]')) {
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            if (!menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });
    }
});

function filterTable() {
    // ... (Fungsi filter Anda tetap sama, tidak perlu diubah) ...
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
    const table = document.getElementById('karyaTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let row of rows) {
        const judul = row.cells[0].textContent.toLowerCase();
        const pembuat = row.cells[1].textContent.toLowerCase();
        const status = row.cells[4].textContent.trim().toLowerCase();
        
        const matchSearch = judul.includes(searchInput) || pembuat.includes(searchInput);
        const matchStatus = statusFilter === '' || status.includes(statusFilter);
        
        row.style.display = (matchSearch && matchStatus) ? '' : 'none';
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer_admin.php'; ?>