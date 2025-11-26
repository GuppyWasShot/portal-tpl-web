<?php
require_once __DIR__ . '/../../app/autoload.php';

$page_title = "Tentang TPL";
$body_class = 'page-tentang';
$additional_stylesheets = ['assets/css/page-tentang.css'];

$db = Database::getInstance()->getConnection();
$sections = [];
$result = $db->query("SELECT * FROM tbl_about_sections WHERE status = 'active' ORDER BY urutan ASC, created_at ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row;
    }
}

include __DIR__ . '/../layouts/header_public.php';
?>

    <!-- ===== Bagian: Hero Tentang ===== -->
    <section class="hero">
        <div class="hero-content">
            <h1>Tentang <span class="highlight">TPL</span></h1>
            <p>Mari mengenal program studi Teknologi Rekayasa Perangkat Lunak Sekolah Vokasi IPB University.</p>
            <div class="hero-buttons">
                <a href="matkul.php" class="btn-outline">Mata Kuliah</a>
                <a href="dosen.php" class="btn-filled">Daftar Dosen</a>
            </div>
        </div>
    </section>

    <!-- ===== Bagian: Konten Tentang ===== -->
    <section class="content">
        <?php if (empty($sections)): ?>
            <div class="empty-about">
                <p>Konten Tentang belum tersedia. Silakan hubungi admin untuk menambahkan informasi.</p>
            </div>
        <?php else: ?>
            <?php foreach ($sections as $section): ?>
            <article class="about-section">
                <h3><?php echo htmlspecialchars($section['judul']); ?></h3>
                <p class="about-body whitespace-pre-line"><?php echo nl2br(htmlspecialchars($section['konten'])); ?></p>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

<?php include __DIR__ . '/../layouts/footer_public.php'; ?>

