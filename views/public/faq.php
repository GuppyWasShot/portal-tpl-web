<?php
require_once __DIR__ . '/../../app/autoload.php';

$page_title = "FAQ";
$body_class = 'page-faq';
$additional_stylesheets = ['assets/css/page-faq.css'];

$db = Database::getInstance()->getConnection();
$faq_result = $db->query("SELECT * FROM tbl_faq WHERE status = 'active' ORDER BY COALESCE(NULLIF(urutan, 0), 9999) ASC, created_at ASC");
$faq_groups = [];

if ($faq_result) {
    while ($row = $faq_result->fetch_assoc()) {
        $group = !empty($row['kategori']) ? $row['kategori'] : 'Umum';
        if (!isset($faq_groups[$group])) {
            $faq_groups[$group] = [];
        }
        $faq_groups[$group][] = $row;
    }
}

ksort($faq_groups);

include __DIR__ . '/../layouts/header_public.php';
?>

<!-- ===== Bagian: FAQ Hero ===== -->
<section class="faq-hero">
    <div class="faq-hero-content">
        <h1>Frequently Asked<br><span class="highlight">Questions</span></h1>
        <p>Temukan jawaban untuk pertanyaan yang sering diajukan tentang Portal TPL</p>
    </div>
</section>

<!-- ===== Bagian: Daftar FAQ ===== -->
<section class="faq-wrapper">
    <h2>Pertanyaan yang <span class="highlight">Sering Diajukan</span></h2>
    <?php if (empty($faq_groups)): ?>
        <div class="faq-empty-state">
            <p>Belum ada FAQ yang tersedia saat ini.</p>
        </div>
    <?php else: ?>
        <?php foreach ($faq_groups as $group => $items): ?>
        <div class="faq-group">
            <!-- <h3><?php echo htmlspecialchars($group); ?></h3> -->
            <div class="faq-container">
                <?php foreach ($items as $faq): ?>
                <div class="faq-card">
                    <div class="faq-question">
                        <?php echo htmlspecialchars($faq['pertanyaan']); ?>
                        <span>+</span>
                    </div>
                    <div class="faq-answer">
                        <p><?php echo nl2br(htmlspecialchars($faq['jawaban'])); ?></p>
                        <?php if (!empty($faq['kategori'])): ?>
                        <div class="faq-tags">
                            <span class="tag"><?php echo htmlspecialchars($faq['kategori']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<script>
    // ===== Bagian: FAQ Accordion =====
    const faqItems = document.querySelectorAll('.faq-card');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', () => {
            const isActive = item.classList.contains('active');
            
            // ===== Bagian: Tutup Semua FAQ =====
            faqItems.forEach(faq => faq.classList.remove('active'));
            
            // ===== Bagian: Buka FAQ Terpilih =====
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
</script>

<?php include __DIR__ . '/../layouts/footer_public.php'; ?>

