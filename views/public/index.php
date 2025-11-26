<?php
/**
 * Beranda Portal TPL - Refactored dengan OOP
 * Mengintegrasikan design dari front/index.html dengan data dinamis
 */

// Autoload classes
require_once __DIR__ . '/../../app/autoload.php';

$page_title = "Beranda";
$body_class = 'page-home';
$additional_stylesheets = ['assets/css/page-index.css'];

// Inisialisasi Class
$karyaModel = new Karya();
$db = Database::getInstance()->getConnection();

// Ambil karya dengan rating tertinggi (Featured) - 6 karya
$featured_karya = $karyaModel->getAllKarya(['sort' => 'rating']);
$featured_karya = array_slice($featured_karya, 0, 6);

// Ambil semua kategori
$query_kategori = "SELECT * FROM tbl_category ORDER BY nama_kategori ASC";
$result_kategori = $db->query($query_kategori);
$kategori_list = [];
while ($row = $result_kategori->fetch_assoc()) {
    $kategori_list[] = $row;
}

// Statistik
$total_karya = $db->query("SELECT COUNT(*) as total FROM tbl_project WHERE status = 'Published'")->fetch_assoc()['total'];

include __DIR__ . '/../layouts/header_public.php';
?>

    <!-- Hero Section -->
    <section class="hero" id="beranda">
        <div class="hero-content">
            <h1>
                Galeri Mahasiswa <em>TPL</em><br>
                <span class="highlight">Sekolah Vokasi<br>
                IPB University</span>
            </h1>
            <p>Ruang kreatif yang menghadirkan inovasi digital<br>untuk masyarakat.</p>
            <a href="galeri.php" class="btn-view-all" style="text-decoration: none; display: inline-block;">Jelajah Karya</a>
        </div>
        <div class="hero-images">
            <?php 
            // Tampilkan 6 gambar dari karya terbaru
            $count = 0;
            foreach ($featured_karya as $karya_item): 
                if (!empty($karya_item['snapshot_url']) && $count < 6):
                    $count++;
            ?>
            <div class="hero-image" style="background-image: url('../../<?php echo htmlspecialchars($karya_item['snapshot_url']); ?>');"></div>
            <?php 
                endif;
            endforeach; 
            // Jika kurang dari 6, tambahkan placeholder
            while ($count < 6):
                $count++;
            ?>
            <div class="hero-image"></div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <p>Temukan <strong><?php echo $total_karya; ?>+ proyek mahasiswa</strong> dan karya akademis yang luar biasa.</p>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </div>
                <p>Jelajahi berbagai proyek berbasis web, sistem informasi, dan portal yang menarik interaktif.</p>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="5" y="2" width="14" height="20" rx="2"/>
                        <line x1="12" y1="18" x2="12" y2="18"/>
                    </svg>
                </div>
                <p>Temukan inovasi berupa aplikasi seluler, bot untuk platform Android maupun iOS.</p>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                </div>
                <p>Akses jurnal, laporan penelitian, dan karya akademis dalam bentuk dokumen ilmiah.</p>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section class="projects" id="showcase">
        <h2>Sorotan</h2>
        <p class="projects-subtitle">Jelajahi karya-karya terbaik mahasiswa TPL</p>
        <div class="projects-grid">
            <?php if (!empty($featured_karya)): ?>
                <?php foreach ($featured_karya as $karya_item): 
                    $kategori_arr = $karya_item['kategori'] ? explode(', ', $karya_item['kategori']) : [];
                    $warna_arr = $karya_item['warna'] ? explode(',', $karya_item['warna']) : [];
                ?>
                <div class="project-card">
                    <a href="detail_karya.php?id=<?php echo $karya_item['id_project']; ?>" style="text-decoration: none; display: block;">
                        <div class="project-image" style="<?php echo !empty($karya_item['snapshot_url']) ? 'background-image: url(../../' . htmlspecialchars($karya_item['snapshot_url']) . '); cursor: pointer;' : ''; ?>">
                            <?php if ($karya_item['avg_rating']): ?>
                            <div style="position: absolute; top: 10px; right: 10px; background: rgba(255, 255, 255, 0.95); padding: 5px 12px; border-radius: 20px; display: flex; align-items: center; gap: 4px; font-size: 13px; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                <svg viewBox="0 0 20 20" style="width: 16px; height: 16px; fill: #ffd700;">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span style="color: #2d1b69;"><?php echo number_format($karya_item['avg_rating'], 1); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="project-content">
                        <?php if (!empty($kategori_arr)): ?>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 10px;">
                            <?php foreach($kategori_arr as $idx => $kat): 
                                $warna = isset($warna_arr[$idx]) ? trim($warna_arr[$idx]) : '#6B7280';
                            ?>
                            <span class="project-badge" style="background-color: <?php echo $warna; ?>20; color: <?php echo $warna; ?>; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                <?php echo htmlspecialchars($kat); ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <h3 class="project-title"><?php echo htmlspecialchars($karya_item['judul']); ?></h3>
                        <p class="project-description"><?php echo htmlspecialchars(substr($karya_item['deskripsi'], 0, 100)); ?>...</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                            <span style="font-size: 13px; color: #666;">
                                <?php echo date('Y', strtotime($karya_item['tanggal_selesai'])); ?>
                            </span>
                            <a href="detail_karya.php?id=<?php echo $karya_item['id_project']; ?>" class="btn-secondary" style="text-decoration: none; display: inline-block; padding: 8px 16px; font-size: 13px;">Lihat Detail</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Tidak ada karya yang dipublikasikan.</p>
            <?php endif; ?>
        </div>
        <div class="view-all">
            <a href="galeri.php" class="btn-view-all" style="text-decoration: none; display: inline-block;">Lihat Semua Proyek</a>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq" id="faq">
        <h2>Frequently Asked<br>Questions</h2>
        <div class="faq-container">
            <div class="faq-item">
                <div class="faq-question">
                    Bagaimana cara mencari atau memfilter karya?
                    <span>+</span>
                </div>
                <div class="faq-answer">
                    Gunakan fitur pencarian atau filter berdasarkan jenis, kategori, atau tahun. Klik tombol "Jelajahi" untuk memulai.
                    <div class="faq-tags">
                        <span class="tag">Pencarian</span>
                        <span class="tag">Beranda</span>
                    </div>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    Bagaimana cara memberi rating pada karya?
                    <span>+</span>
                </div>
                <div class="faq-answer">
                    Buka halaman detail karya, lalu pilih berbintang untuk memberikan penilaian.
                    <div class="faq-tags">
                        <span class="tag">Interaksi</span>
                        <span class="tag">Komentar</span>
                    </div>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    Bagaimana cara mengunggah karya ke portal?
                    <span>+</span>
                </div>
                <div class="faq-answer">
                    Mahasiswa menghubungi dosen serta mengisi formulir publikasi. Karya akan diupload oleh admin.
                    <div class="faq-tags">
                        <span class="tag">Unggah</span>
                        <span class="tag">Komentar</span>
                    </div>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    Apakah saya perlu login untuk melihat karya?
                    <span>+</span>
                </div>
                <div class="faq-answer">
                    Tidak. Semua karya dapat di-publish dapat diakses oleh publik tanpa login.
                    <div class="faq-tags">
                        <span class="tag">Akses</span>
                        <span class="tag">Visibilitas</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <h2>Syntax Error, <span class="highlight">Compile Lagi.</span></h2>
    </section>

    <script>
        // FAQ Accordion
        const faqItems = document.querySelectorAll('.faq-item');
        
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            
            question.addEventListener('click', () => {
                const isActive = item.classList.contains('active');
                
                // Close all items
                faqItems.forEach(faq => faq.classList.remove('active'));
                
                // Open clicked item if it wasn't active
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        });
    </script>

<?php include __DIR__ . '/../layouts/footer_public.php'; ?>

