<?php
session_start();
$page_title = "Kelola Halaman Tentang";
$current_page = 'kelola_tentang';

require_once __DIR__ . '/../../app/autoload.php';
$db = Database::getInstance()->getConnection();
$conn = $db;

$alert_type = '';
$alert_message = '';

if (isset($_GET['success'])) {
    $alert_type = 'success';
    $alert_message = match ($_GET['success']) {
        'created' => 'Section berhasil ditambahkan.',
        'updated' => 'Section berhasil diperbarui.',
        'deleted' => 'Section berhasil dihapus.',
        default => ''
    };
} elseif (isset($_GET['error'])) {
    $alert_type = 'error';
    $alert_message = match ($_GET['error']) {
        'empty_field' => 'Judul dan konten wajib diisi.',
        'invalid_id' => 'ID section tidak valid.',
        'not_found' => 'Section tidak ditemukan atau sudah dihapus.',
        'database_error' => 'Terjadi kesalahan pada database. Coba lagi nanti.',
        'invalid_request' => 'Permintaan tidak valid.',
        default => 'Terjadi kesalahan. Coba lagi nanti.'
    };
}

$sections = [];
$result = $conn->query("SELECT * FROM tbl_about_sections ORDER BY urutan ASC, created_at ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row;
    }
}

include __DIR__ . '/../layouts/header_admin.php';
?>

<header class="bg-white shadow-sm">
    <div class="px-8 py-6">
        <div class="flex items-center text-sm text-gray-500 mb-2">
            <a href="index.php" class="hover:text-indigo-600">Dashboard</a>
            <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Kelola Tentang</span>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Kelola Konten Tentang TPL</h2>
        <p class="text-gray-600 mt-1">Atur setiap section pada halaman Tentang.</p>
    </div>
</header>

<div class="p-8 space-y-6">

    <?php if (!empty($alert_message)): ?>
    <div class="px-4 py-3 rounded-lg <?php echo $alert_type === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'; ?>">
        <?php echo htmlspecialchars($alert_message); ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Section Baru</h3>
            <form action="../../controllers/admin/proses_tambah_about.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul Section <span class="text-red-500">*</span></label>
                    <input type="text" name="judul" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug (Opsional)</label>
                    <input type="text" name="slug" placeholder="contoh: visi" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Jika dikosongkan akan di-generate otomatis.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konten <span class="text-red-500">*</span></label>
                    <textarea name="konten" rows="6" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Gunakan Enter untuk membuat paragraf baru.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                        <input type="number" name="urutan" value="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    Simpan Section
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Daftar Section</h3>
                    <p class="text-sm text-gray-500 mt-1">Total section: <?php echo count($sections); ?></p>
                </div>
            </div>

            <?php if (count($sections) === 0): ?>
                <div class="text-center py-12 text-gray-500 border-2 border-dashed border-gray-200 rounded-lg">
                    Belum ada konten Tentang. Tambahkan section pertama Anda.
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($sections as $section): ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div>
                                <p class="text-xs text-gray-500">Urutan: <?php echo $section['urutan']; ?></p>
                                <h4 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($section['judul']); ?></h4>
                                <p class="text-xs text-gray-500">Slug: <?php echo htmlspecialchars($section['slug']); ?></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <?php if ($section['status'] === 'active'): ?>
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">Aktif</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">Nonaktif</span>
                                <?php endif; ?>
                                <button type="button"
                                        class="px-3 py-1.5 text-sm text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                        onclick='openSectionModal(<?php echo htmlspecialchars(json_encode($section), ENT_QUOTES, "UTF-8"); ?>)'>
                                    Edit
                                </button>
                                <button type="button"
                                        class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition"
                                        onclick="deleteSection(<?php echo $section['id_section']; ?>)">
                                    Hapus
                                </button>
                            </div>
                        </div>
                        <p class="text-sm text-gray-700 mt-3 whitespace-pre-line"><?php echo htmlspecialchars($section['konten']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Edit Section -->
<div id="sectionModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeSectionModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-3xl mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Edit Section Tentang</h3>
            <button type="button" onclick="closeSectionModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form action="../../controllers/admin/proses_edit_about.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="id_section" id="section_id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Section <span class="text-red-500">*</span></label>
                <input type="text" name="judul" id="section_judul" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" id="section_slug" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konten <span class="text-red-500">*</span></label>
                <textarea name="konten" id="section_konten" rows="6" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                    <input type="number" name="urutan" id="section_urutan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="section_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeSectionModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const sectionModal = document.getElementById('sectionModal');
const sectionIdInput = document.getElementById('section_id');
const sectionJudulInput = document.getElementById('section_judul');
const sectionSlugInput = document.getElementById('section_slug');
const sectionKontenInput = document.getElementById('section_konten');
const sectionUrutanInput = document.getElementById('section_urutan');
const sectionStatusInput = document.getElementById('section_status');

function openSectionModal(data) {
    sectionIdInput.value = data.id_section;
    sectionJudulInput.value = data.judul;
    sectionSlugInput.value = data.slug;
    sectionKontenInput.value = data.konten;
    sectionUrutanInput.value = data.urutan ?? 0;
    sectionStatusInput.value = data.status;
    sectionModal.classList.remove('hidden');
    sectionModal.classList.add('flex');
}

function closeSectionModal() {
    sectionModal.classList.add('hidden');
    sectionModal.classList.remove('flex');
}

function deleteSection(id) {
    if (confirm('Hapus section ini?')) {
        window.location.href = '../../controllers/admin/hapus_about.php?id=' + id;
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !sectionModal.classList.contains('hidden')) {
        closeSectionModal();
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer_admin.php'; ?>

