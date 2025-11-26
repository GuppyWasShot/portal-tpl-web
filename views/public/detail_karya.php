<?php
/**
 * Detail Karya - Refactored dengan OOP
 * 
 * File ini mengintegrasikan:
 * - HTML design dari front/detail-karya.html
 * - Logic dari Class Karya dan Rating
 * - Clean separation of concerns
 */

session_start();

// Error reporting untuk debugging (nonaktifkan di production)
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

// Autoload classes
require_once __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../config/db_connect.php';

// Generate atau ambil UUID user dari session
if (!isset($_SESSION['user_uuid'])) {
    $_SESSION['user_uuid'] = uniqid('user_', true);
}

// Ambil ID project dari URL
$id_project = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_project <= 0) {
    header("Location: galeri.php");
    exit();
}

try {
// Inisialisasi Class
$karyaModel = new Karya();
$ratingModel = new Rating();

// Ambil data karya
$karya = $karyaModel->getKaryaById($id_project);

if (!$karya) {
    header("Location: galeri.php");
        exit();
    }
} catch (Exception $e) {
    // Log error dan redirect
    error_log("Error di detail_karya.php: " . $e->getMessage());
    header("Location: galeri.php?error=not_found");
    exit();
}

// Ambil links dan files
try {
$links = $karyaModel->getLinks($id_project);
$files = $karyaModel->getFiles($id_project);
$fileGroups = $karyaModel->separateFiles($files);
} catch (Exception $e) {
    error_log("Error mengambil links/files: " . $e->getMessage());
    $links = [];
    $files = [];
    $fileGroups = ['snapshots' => [], 'documents' => []];
}

// Cek apakah user sudah pernah rating
try {
    $user_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
$user_uuid = $_SESSION['user_uuid'];
$user_rating = $ratingModel->getUserRating($id_project, $user_uuid, $user_ip);
} catch (Exception $e) {
    error_log("Error mengambil rating: " . $e->getMessage());
    $user_rating = null;
}

// Handle success/error messages
$success_msg = '';
$error_msg = '';
if (isset($_GET['success'])) {
    switch($_GET['success']) {
        case 'rating_submitted':
            $success_msg = 'Rating berhasil dikirim!';
            break;
        case 'rating_updated':
            $success_msg = 'Rating berhasil diperbarui!';
            break;
        case 'rating_cancelled':
            $success_msg = 'Rating berhasil dibatalkan!';
            break;
    }
}
if (isset($_GET['error'])) {
    switch($_GET['error']) {
        case 'rating_failed':
            $error_msg = 'Gagal mengirim rating. Silakan coba lagi.';
            break;
        case 'rating_cancel_failed':
            $error_msg = 'Gagal membatalkan rating. Silakan coba lagi.';
            break;
    }
}

// Parse kategori
$kategori_arr = [];
$warna_arr = [];
if ($karya['kategori']) {
    $kategori_arr = explode(', ', $karya['kategori']);
    $warna_arr = explode(',', $karya['warna']);
}

$pembuat_list = [];
if (!empty($karya['pembuat'])) {
    $pembuat_list = array_filter(array_map('trim', explode(';', $karya['pembuat'])));
}

// Set page title
$page_title = $karya['judul'];
$body_class = 'page-detail';
$additional_stylesheets = ['assets/css/page-detail.css'];
include __DIR__ . '/../layouts/header_public.php';
?>
    <!-- ===== Bagian: Konten Utama ===== -->
    <main class="main-content">
        <div class="content-grid">
            <!-- ===== Bagian: Kolom Kiri ===== -->
            <div class="left-column">
                <!-- ===== Bagian: Galeri Utama ===== -->
                <?php 
                $all_snapshots = $fileGroups['snapshots'];
                
                // Jika ada snapshot_url utama, tambahkan ke awal array jika belum ada
                if (!empty($karya['snapshot_url'])) {
                    $main_snapshot = $karya['snapshot_url'];
                    // Cek apakah snapshot_url sudah ada di array
                    $found = false;
                    foreach ($all_snapshots as $snap) {
                        if ($snap['file_path'] === $main_snapshot) {
                            $found = true;
                            break;
                        }
                    }
                    // Jika belum ada, tambahkan di awal
                    if (!$found) {
                        array_unshift($all_snapshots, ['file_path' => $main_snapshot, 'label' => 'Main Image']);
                    }
                } else {
                    $main_snapshot = !empty($all_snapshots) ? $all_snapshots[0]['file_path'] : '';
                }
                ?>
                
                <?php if (!empty($main_snapshot)): ?>
                <div class="main-image-container">
                    <img id="mainGalleryImage" 
                         src="../../<?php echo htmlspecialchars($main_snapshot); ?>" 
                         alt="<?php echo htmlspecialchars($karya['judul']); ?>">
                </div>
                
                <!-- ===== Bagian: Thumbnail Galeri ===== -->
                <?php if (count($all_snapshots) > 1): ?>
                <div class="gallery-grid">
                    <?php foreach ($all_snapshots as $idx => $snapshot): ?>
                    <div class="gallery-item <?php echo $idx === 0 ? 'active' : ''; ?>" 
                         onclick="changeMainImage('../../<?php echo htmlspecialchars($snapshot['file_path']); ?>', this)">
                        <img src="../../<?php echo htmlspecialchars($snapshot['file_path']); ?>" 
                             alt="Thumbnail <?php echo $idx + 1; ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <h1 class="project-title"><?php echo htmlspecialchars($karya['judul']); ?></h1>

                <!-- ===== Bagian: Tag Kategori ===== -->
                <?php if (!empty($kategori_arr)): ?>
                <div class="project-tags">
                    <?php foreach($kategori_arr as $idx => $kat): 
                        $warna = isset($warna_arr[$idx]) ? trim($warna_arr[$idx]) : '#6B7280';
                    ?>
                    <span class="tag" style="background-color: <?php echo $warna; ?>20; color: <?php echo $warna; ?>;">
                        <?php echo htmlspecialchars($kat); ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <h2 class="section-title">Deskripsi Karya</h2>
                <p class="description-text">
                    <?php echo nl2br(htmlspecialchars($karya['deskripsi'])); ?>
                </p>

                <!-- ===== Bagian: Tautan Karya ===== -->
                <?php if (!empty($links)): ?>
                <div class="action-buttons">
                    <?php foreach ($links as $link): ?>
                    <a href="<?php echo htmlspecialchars($link['url']); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="btn btn-outline">
                        <?php echo htmlspecialchars($link['label']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>


                <!-- ===== Bagian: Dokumen Pendukung ===== -->
                <?php if (!empty($fileGroups['documents'])): ?>
                <h2 class="section-title">File Pendukung</h2>
                <div class="action-buttons">
                    <?php foreach ($fileGroups['documents'] as $doc): ?>
                    <a href="../../<?php echo htmlspecialchars($doc['file_path']); ?>" 
                       target="_blank" 
                       download
                       class="btn btn-outline">
                        📄 <?php echo htmlspecialchars($doc['label']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- ===== Bagian: Kolom Kanan ===== -->
            <div class="right-column">
                <!-- ===== Bagian: Informasi Karya ===== -->
                <div class="info-card">
                    <h3>Detail Karya</h3>
                    
                    <div class="info-item">
                        <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="#2d1b69" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <div class="info-content">
                            <h4>Tanggal</h4>
                            <p><?php echo date('d F Y', strtotime($karya['tanggal_selesai'])); ?></p>
                        </div>
                    </div>

                    <div class="info-item">
                        <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="#2d1b69" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                        <div class="info-content">
                            <h4>Pembuat</h4>
                            <?php if (!empty($pembuat_list)): ?>
                            <ul class="creator-list">
                                <?php foreach ($pembuat_list as $pembuat): ?>
                                <li><?php echo htmlspecialchars($pembuat); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php else: ?>
                            <p><?php echo htmlspecialchars($karya['pembuat']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ===== Bagian: Penilaian Karya ===== -->
                <div class="info-card">
                    <h3>Penilaian Rata-Rata</h3>
                    
                    <?php if ($karya['avg_rating']): ?>
                    <div class="rating-summary">
                        <span class="rating-summary-value">
                            Rating <?php echo number_format($karya['avg_rating'], 1); ?>/5
                        </span>
                        <svg viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span class="rating-summary-count">
                            (<?php echo $karya['total_rating']; ?> penilaian)
                        </span>
                    </div>
                    <?php else: ?>
                    <div class="rating-empty">
                        <p>
                            Belum ada penilaian
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- ===== Bagian: Pesan Status ===== -->
                    <?php if ($success_msg): ?>
                    <div class="alert-success">
                        <p>
                            ✓ <?php echo htmlspecialchars($success_msg); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error_msg): ?>
                    <div class="alert-error">
                        <p>
                            ✗ <?php echo htmlspecialchars($error_msg); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- ===== Bagian: Form Penilaian Pengguna ===== -->
                    <?php if ($user_rating): ?>
                    <div class="rating-form">
                        <h4>Rating Anda: <?php echo $user_rating['skor']; ?> bintang</h4>
                        <p class="rating-note">
                            Anda dapat mengubah atau membatalkan rating
                        </p>
                        <form id="ratingForm" method="POST" action="../../controllers/public/proses_rating.php" class="rating-update-form">
                            <input type="hidden" name="id_project" value="<?php echo $id_project; ?>">
                            <input type="hidden" name="action" value="submit">
                            <div class="star-input" id="starRating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span data-value="<?php echo $i; ?>" class="<?php echo $i <= $user_rating['skor'] ? 'active' : ''; ?>">
                                    <?php echo $i <= $user_rating['skor'] ? '★' : '☆'; ?>
                                </span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="skor" id="skorInput" value="<?php echo $user_rating['skor']; ?>" required>
                            <button type="submit" id="submitBtn" class="btn-submit btn-full">
                                Ubah Rating
                            </button>
                        </form>
                        <form method="POST" action="../../controllers/public/proses_rating.php" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan rating?');">
                            <input type="hidden" name="id_project" value="<?php echo $id_project; ?>">
                            <input type="hidden" name="action" value="cancel">
                            <button type="submit" class="btn-cancel-rating">
                                Batalkan Rating
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="rating-form">
                        <h4>Beri Penilaian</h4>
                        <p class="rating-note">
                            Pilih penilaian Anda di bawah ini
                        </p>
                        <form id="ratingForm" method="POST" action="../../controllers/public/proses_rating.php">
                            <input type="hidden" name="id_project" value="<?php echo $id_project; ?>">
                            <input type="hidden" name="action" value="submit">
                            <div class="star-input" id="starRating">
                                <span data-value="1">☆</span>
                                <span data-value="2">☆</span>
                                <span data-value="3">☆</span>
                                <span data-value="4">☆</span>
                                <span data-value="5">☆</span>
                            </div>
                            <input type="hidden" name="skor" id="skorInput" required>
                            <button type="submit" id="submitBtn" disabled class="btn-submit">
                                Kirim Penilaian
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        // ===== Bagian: Logika Penilaian =====
        let selectedRating = 0;
        const starRating = document.getElementById('starRating');
        const submitBtn = document.getElementById('submitBtn');
        const skorInput = document.getElementById('skorInput');
        const ratingForm = document.getElementById('ratingForm');
        
        if (starRating) {
            const stars = starRating.querySelectorAll('span');
            
            // ===== Bagian: Nilai Awal Rating =====
            if (skorInput && skorInput.value) {
                selectedRating = parseInt(skorInput.value);
            }

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    selectedRating = parseInt(this.getAttribute('data-value'));
                    if (skorInput) {
                        skorInput.value = selectedRating;
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                    updateStars(selectedRating);
                });

                star.addEventListener('mouseenter', function() {
                    const value = parseInt(this.getAttribute('data-value'));
                    updateStars(value, true);
                });
            });

            starRating.addEventListener('mouseleave', function() {
                updateStars(selectedRating);
            });

            function updateStars(rating, isHover = false) {
                stars.forEach(star => {
                    const value = parseInt(star.getAttribute('data-value'));
                    if (value <= rating) {
                        star.classList.add('active');
                        star.textContent = '★';
                    } else {
                        star.classList.remove('active');
                        star.textContent = '☆';
                    }
                });
            }
            
            // ===== Bagian: Inisialisasi Rating =====
            if (selectedRating > 0) {
                updateStars(selectedRating);
            }
            
            // ===== Bagian: Validasi Form Sebelum Submit =====
            if (ratingForm) {
                ratingForm.addEventListener('submit', function(e) {
                    if (!skorInput || !skorInput.value || skorInput.value < 1 || skorInput.value > 5) {
                        e.preventDefault();
                        alert('Silakan pilih rating terlebih dahulu (1-5 bintang)');
                        return false;
                    }
                    
                    // Disable button untuk mencegah double submit
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Mengirim...';
                    }
                });
            }
        }

        // ===== Bagian: Galeri Dinamis =====
        function changeMainImage(imageSrc, clickedElement) {
            const mainImage = document.getElementById('mainGalleryImage');
            if (mainImage) {
                mainImage.style.opacity = '0';
                setTimeout(() => {
                    mainImage.src = imageSrc;
                    mainImage.style.opacity = '1';
                }, 150);
            }
            
            // ===== Bagian: Status Thumbnail =====
            document.querySelectorAll('.gallery-item').forEach(item => {
                item.classList.remove('active');
            });
            if (clickedElement) {
                clickedElement.classList.add('active');
            }
        }

        // ===== Bagian: Navigasi Halus =====
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>

<?php include __DIR__ . '/../layouts/footer_public.php'; ?>

