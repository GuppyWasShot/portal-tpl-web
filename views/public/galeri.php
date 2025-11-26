<?php
/**
 * Galeri Karya - Refactored dengan OOP
 * Menggunakan Class Karya untuk mengambil data dengan pagination
 */

// ===== Bagian: Autoload Class =====
require_once __DIR__ . '/../../app/autoload.php';

$page_title = "Daftar Karya";

// ===== Bagian: Inisialisasi Kelas =====
$karyaModel = new Karya();
$db = Database::getInstance()->getConnection();

// ===== Bagian: Pengaturan Pagination =====
$per_page = 12;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// ===== Bagian: Parameter Pencarian dan Filter =====
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'terbaru';
$kategori_filter = isset($_GET['kategori']) ? (is_array($_GET['kategori']) ? $_GET['kategori'] : [$_GET['kategori']]) : [];

// ===== Bagian: Daftar Kategori =====
$query_kategori = "SELECT * FROM tbl_category ORDER BY nama_kategori ASC";
$result_kategori = $db->query($query_kategori);
$kategori_list = [];
while ($row = $result_kategori->fetch_assoc()) {
    $kategori_list[] = $row;
}

// ===== Bagian: Data Karya dengan Pagination =====
$filters = [
    'search' => $search,
    'sort' => $sort,
    'kategori' => $kategori_filter
];
$result = $karyaModel->getAllKaryaPaginated($filters, $current_page, $per_page);
$karya_list = isset($result['data']) ? $result['data'] : [];
$total_hasil = isset($result['total']) ? $result['total'] : 0;
$total_pages = isset($result['total_pages']) ? $result['total_pages'] : 1;

$body_class = 'page-galeri';
$additional_stylesheets = ['assets/css/page-galeri.css'];
include __DIR__ . '/../layouts/header_public.php';
?>

<!-- ===== Bagian: Hero Galeri ===== -->
<section class="gallery-hero">
    <div class="gallery-hero-content">
        <h1>Cari <span class="highlight">Karya</span></h1>
        <p>Jelajahi inovasi dan kreativitas mahasiswa Teknologi Rekayasa Perangkat Lunak</p>
    </div>
</section>

<!-- ===== Bagian: Seksi Galeri ===== -->
<section class="gallery-section">
    <div class="gallery-container">

    <!-- ===== Bagian: Filter Galeri ===== -->
    <div class="filter-section">
        <form method="GET" action="galeri.php" id="filterForm">
            <div class="search-container">
                <div class="search-wrapper">
                    <input type="text" 
                           id="searchInput" 
                           name="search" 
                           class="search-input"
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Cari proyek...">
                    <svg class="search-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                </div>
                <div class="sort-wrapper">
                    <label for="sortSelect" class="sort-label">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M7 12h10m-7 6h4"/>
                        </svg>
                        Sort:
                    </label>
                    <select name="sort" id="sortSelect" class="sort-select" onchange="document.getElementById('filterForm').submit();">
                        <option value="terbaru" <?php echo $sort === 'terbaru' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="terlama" <?php echo $sort === 'terlama' ? 'selected' : ''; ?>>Terlama</option>
                        <option value="alfabet" <?php echo $sort === 'alfabet' ? 'selected' : ''; ?>>Alfabet (A-Z)</option>
                        <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Rating Tertinggi</option>
                    </select>
                </div>
            </div>

            <!-- ===== Bagian: Filter Kategori ===== -->
            <div class="category-filter" id="categoryFilter">
                <span class="sr-only">Filter kategori</span>
                <div class="category-bubbles">
                    <button type="button" 
                            class="category-chip category-all <?php echo empty($kategori_filter) ? 'is-active' : ''; ?>" 
                            onclick="resetCategories()">
                        Semua
                    </button>
                    <?php foreach($kategori_list as $kat): 
                        $checked = in_array($kat['id_kategori'], $kategori_filter) ? 'checked' : '';
                        $warna = !empty($kat['warna_hex']) ? $kat['warna_hex'] : '#6B7280';
                    ?>
                    <div class="category-bubble">
                        <input type="checkbox" 
                               id="kategori_<?php echo $kat['id_kategori']; ?>" 
                               name="kategori[]" 
                               value="<?php echo $kat['id_kategori']; ?>" 
                               <?php echo $checked; ?>
                               onchange="document.getElementById('filterForm').submit();">
                        <label for="kategori_<?php echo $kat['id_kategori']; ?>" 
                               class="category-chip"
                               style="--bubble-color: <?php echo $warna; ?>;">
                            <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ===== Bagian: Pengaturan Pagination ===== -->
            <input type="hidden" name="page" value="1" id="pageInput">
        </form>
    </div>

    <!-- ===== Bagian: Informasi Hasil ===== -->
    <div class="results-info">
        Menampilkan <strong><?php echo count($karya_list); ?></strong> dari <strong><?php echo $total_hasil; ?></strong> karya
        <?php if (!empty($search)): ?>
        untuk pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>"
        <?php endif; ?>
    </div>

    <!-- ===== Bagian: Kartu Galeri ===== -->
    <?php if (count($karya_list) > 0): ?>
    <div class="gallery-grid">
        <?php foreach($karya_list as $karya): 
            $kategori_arr = $karya['kategori'] ? explode(', ', $karya['kategori']) : [];
            $warna_arr = $karya['warna'] ? explode(',', $karya['warna']) : [];
        ?>
        <a href="detail_karya.php?id=<?php echo $karya['id_project']; ?>" class="gallery-card">
            <div class="gallery-card-image" style="<?php echo !empty($karya['snapshot_url']) ? 'background-image: url(../../' . htmlspecialchars($karya['snapshot_url']) . ');' : ''; ?>">
                <?php if ($karya['avg_rating']): ?>
                <div class="gallery-rating-badge">
                    <svg viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <?php echo number_format($karya['avg_rating'], 1); ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="gallery-card-content">
                <?php if (!empty($kategori_arr)): ?>
                <div class="gallery-card-badges" style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 10px;">
                    <?php foreach($kategori_arr as $idx => $kat): 
                        $warna = isset($warna_arr[$idx]) ? trim($warna_arr[$idx]) : '#6B7280';
                    ?>
                    <span class="gallery-card-badge" style="background-color: <?php echo $warna; ?>20; color: <?php echo $warna; ?>; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                        <?php echo htmlspecialchars($kat); ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <h3 class="gallery-card-title"><?php echo htmlspecialchars($karya['judul']); ?></h3>
                <p class="gallery-card-description"><?php echo htmlspecialchars(substr($karya['deskripsi'], 0, 100)); ?>...</p>
                <div class="gallery-card-footer">
                    <span class="gallery-card-year"><?php echo date('Y', strtotime($karya['tanggal_selesai'])); ?></span>
                    <span class="gallery-card-link">Lihat Detail →</span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- ===== Bagian: Navigasi Halaman ===== -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php
        // ===== Bagian: Parameter Pagination =====
        $query_params = $_GET;
        unset($query_params['page']); // Remove page dari query params
        ?>
        
        <?php if ($current_page > 1): ?>
        <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $current_page - 1])); ?>">« Prev</a>
        <?php else: ?>
        <span class="disabled">« Prev</span>
        <?php endif; ?>

        <?php
        $start_page = max(1, $current_page - 2);
        $end_page = min($total_pages, $current_page + 2);
        
        if ($start_page > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => 1])); ?>">1</a>
            <?php if ($start_page > 2): ?>
            <span>...</span>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
            <?php if ($i == $current_page): ?>
            <span class="current"><?php echo $i; ?></span>
            <?php else: ?>
            <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $i])); ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($end_page < $total_pages): ?>
            <?php if ($end_page < $total_pages - 1): ?>
            <span>...</span>
            <?php endif; ?>
            <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $total_pages])); ?>"><?php echo $total_pages; ?></a>
        <?php endif; ?>

        <?php if ($current_page < $total_pages): ?>
        <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $current_page + 1])); ?>">Next »</a>
        <?php else: ?>
        <span class="disabled">Next »</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- ===== Bagian: Galeri Kosong ===== -->
    <div class="empty-state">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3>Tidak ada karya ditemukan</h3>
        <p>
            <?php if (!empty($search) || !empty($kategori_filter)): ?>
            Coba ubah filter atau kata kunci pencarian Anda
            <?php else: ?>
            Belum ada karya yang dipublikasikan
            <?php endif; ?>
        </p>
        <?php if (!empty($search) || !empty($kategori_filter)): ?>
        <a href="galeri.php" class="btn-secondary">Lihat Semua Karya</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    </div>
</section>

<script>
    function resetCategories() {
        document.querySelectorAll('input[name="kategori[]"]').forEach(cb => {
                cb.checked = false;
        });
        document.getElementById('pageInput').value = '1';
        saveScrollPosition();
        document.getElementById('filterForm').submit();
    }

    function scrollToCategories() {
        const section = document.getElementById('categoryFilter');
        if (section) {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // Save scroll position before form submit
    function saveScrollPosition() {
        sessionStorage.setItem('galeri_scroll', window.scrollY);
    }

    // Restore scroll position after page load
    window.addEventListener('load', function() {
        const savedScroll = sessionStorage.getItem('galeri_scroll');
        if (savedScroll !== null) {
            window.scrollTo(0, parseInt(savedScroll));
            sessionStorage.removeItem('galeri_scroll');
        }
    });

    // Save scroll position when form is submitted
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function() {
            saveScrollPosition();
        });
    }

    // Save scroll position when category/sort is changed
    document.querySelectorAll('input[name="kategori[]"], select[name="sort"]').forEach(function(elem) {
        elem.addEventListener('change', function() {
            saveScrollPosition();
        });
    });

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('pageInput').value = '1';
            saveScrollPosition();
        }
    });
    }

</script>

<?php include __DIR__ . '/../layouts/footer_public.php'; ?>
