<?php
$page_title = "Dosen TPL";
$body_class = 'page-dosen';
$additional_stylesheets = ['assets/css/page-dosen.css'];

require_once __DIR__ . '/../../app/autoload.php';
$db = Database::getInstance()->getConnection();
$dosen_list = [];
$result = $db->query("SELECT * FROM tbl_dosen WHERE status = 'active' ORDER BY COALESCE(NULLIF(urutan, 0), 9999) ASC, nama ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $dosen_list[] = $row;
    }
}
$default_photo = 'assets/img/fd.png';

include __DIR__ . '/../layouts/header_public.php';
?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Dosen<span class="highlight">TPL</span></h1>
            <p>Kepakaran Teknologi Rekayasa Perangkat Lunak</p>
        </div>
    </section>

    <!-- Dosen Cards Section -->
    <div class="container-dosen">
        <div class="unique-dosen-grid">
            <?php if (empty($dosen_list)): ?>
                <div class="dosen-empty-state">
                    <p>Data dosen belum tersedia.</p>
                </div>
            <?php else: ?>
                <?php foreach ($dosen_list as $dosen): ?>
                <div class="dosen-card-unique">
                    <div class="photo-base">
                        <img src="../../<?php echo htmlspecialchars(!empty($dosen['foto_url']) ? $dosen['foto_url'] : $default_photo); ?>" alt="Foto <?php echo htmlspecialchars($dosen['nama']); ?>" class="dosen-photo">
                    </div>
                    <div class="info-content-unique">
                        <div class="name-research-group">
                            <?php if (!empty($dosen['gelar'])): ?>
                                <h3 class="dosen-name"><?php echo htmlspecialchars($dosen['nama']) . (empty($dosen['gelar']) ? '' : ' ' . htmlspecialchars($dosen['gelar'])); ?></h3>
                            <?php endif; ?>
                            <?php if (!empty($dosen['jabatan'])): ?>
                                <p class="dosen-position"><?php echo htmlspecialchars($dosen['jabatan']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($dosen['deskripsi'])): ?>
                            <p class="research-interest">
                                <strong>Research Interest</strong><br>
                                <?php echo nl2br(htmlspecialchars($dosen['deskripsi'])); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($dosen['email'])): ?>
                        <div class="contact-footer">
                            <a href="mailto:<?php echo htmlspecialchars($dosen['email']); ?>" class="dosen-email">
                                <?php echo htmlspecialchars($dosen['email']); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

<?php include __DIR__ . '/../layouts/footer_public.php'; ?>

