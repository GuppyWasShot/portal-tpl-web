<?php
$page_title = "Mata Kuliah";
$body_class = 'page-matkul';
$additional_stylesheets = ['assets/css/page-matkul.css'];

require_once __DIR__ . '/../../app/autoload.php';
$db = Database::getInstance()->getConnection();
$matkul_list = [];
$result = $db->query("SELECT * FROM tbl_matkul WHERE status = 'active' ORDER BY semester ASC, COALESCE(NULLIF(urutan, 0), 9999) ASC, nama ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $matkul_list[] = $row;
    }
}

$grouped = [];
foreach ($matkul_list as $matkul) {
    $grouped[$matkul['semester']][] = $matkul;
}
ksort($grouped);

include __DIR__ . '/../layouts/header_public.php';
?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Mata <span class="highlight">Kuliah</span></h1>
            <p>Daftar Mata Kuliah Prodi Teknologi Rekayasa Perangkat Lunak</p>
        </div>
    </section>

    <!-- container content -->
    <div class="container-content">
        <?php if (empty($grouped)): ?>
            <div class="semester-section">
                <div class="table-wrapper">
                    <p>Data mata kuliah belum tersedia.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($grouped as $semester => $items): ?>
            <div class="semester-section">
                <h2>Semester <?php echo $semester; ?></h2>
                <div class="table-wrapper" role="region" aria-label="Tabel Semester <?php echo $semester; ?>" tabindex="0">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode MK</th>
                                <th>Nama Mata Kuliah</th>
                                <th>SKS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $matkul): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($matkul['kode']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($matkul['nama']); ?>
                                    <?php if (!empty($matkul['kategori'])): ?>
                                        <div class="matkul-tag"><?php echo htmlspecialchars($matkul['kategori']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $sks_text = (string)$matkul['sks'];
                                        if (!empty($matkul['deskripsi'])) {
                                            $sks_text .= ' (' . $matkul['deskripsi'] . ')';
                                        }
                                        echo htmlspecialchars($sks_text);
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<?php include __DIR__ . '/../layouts/footer_public.php'; ?>

