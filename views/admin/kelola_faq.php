<?php
session_start();
$page_title = "Kelola FAQ";
$current_page = 'kelola_faq';

require_once __DIR__ . '/../../app/autoload.php';
$db = Database::getInstance()->getConnection();
$conn = $db;

$alert_type = '';
$alert_message = '';

if (isset($_GET['success'])) {
    $alert_type = 'success';
    $alert_message = match ($_GET['success']) {
        'created' => 'FAQ berhasil ditambahkan.',
        'updated' => 'FAQ berhasil diperbarui.',
        'deleted' => 'FAQ berhasil dihapus.',
        default => ''
    };
} elseif (isset($_GET['error'])) {
    $alert_type = 'error';
    $alert_message = match ($_GET['error']) {
        'empty_field' => 'Pertanyaan dan jawaban wajib diisi.',
        'invalid_id' => 'ID FAQ tidak valid.',
        'not_found' => 'Data FAQ tidak ditemukan atau sudah dihapus.',
        'database_error' => 'Terjadi kesalahan pada database. Coba lagi nanti.',
        'invalid_request' => 'Permintaan tidak valid.',
        default => 'Terjadi kesalahan. Coba lagi nanti.'
    };
}

$faq_list = [];
$result = $conn->query("SELECT * FROM tbl_faq ORDER BY status DESC, urutan ASC, created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $faq_list[] = $row;
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
            <span class="text-gray-900 font-medium">Kelola FAQ</span>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Kelola FAQ</h2>
        <p class="text-gray-600 mt-1">Tambahkan dan kelola pertanyaan yang sering diajukan.</p>
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
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah FAQ Baru</h3>
            <form action="../../controllers/admin/proses_tambah_faq.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pertanyaan <span class="text-red-500">*</span></label>
                    <input type="text" name="pertanyaan" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jawaban <span class="text-red-500">*</span></label>
                    <textarea name="jawaban" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <input type="text" name="kategori" placeholder="Contoh: Umum" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                        <input type="number" name="urutan" value="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    Simpan FAQ
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Daftar FAQ</h3>
                    <p class="text-sm text-gray-500 mt-1">Total FAQ: <?php echo count($faq_list); ?></p>
                </div>
            </div>

            <?php if (count($faq_list) === 0): ?>
                <div class="text-center py-12 text-gray-500 border-2 border-dashed border-gray-200 rounded-lg">
                    Belum ada FAQ. Tambahkan pertanyaan pertama Anda.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Urutan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pertanyaan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-sm">
                            <?php foreach ($faq_list as $faq): ?>
                            <tr>
                                <td class="px-4 py-3 font-mono text-gray-600"><?php echo $faq['urutan']; ?></td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($faq['pertanyaan']); ?></p>
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2"><?php echo htmlspecialchars(mb_substr(strip_tags($faq['jawaban']), 0, 120)); ?>...</p>
                                </td>
                                <td class="px-4 py-3 text-gray-700"><?php echo !empty($faq['kategori']) ? htmlspecialchars($faq['kategori']) : '-'; ?></td>
                                <td class="px-4 py-3">
                                    <?php if ($faq['status'] === 'active'): ?>
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">Aktif</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                                class="px-3 py-1.5 text-sm text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                                onclick='openFaqModal(<?php echo htmlspecialchars(json_encode($faq), ENT_QUOTES, "UTF-8"); ?>)'>
                                            Edit
                                        </button>
                                        <button type="button"
                                                class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition"
                                                onclick="deleteFaq(<?php echo $faq['id_faq']; ?>)">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Edit FAQ -->
<div id="faqModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeFaqModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-3xl mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Edit FAQ</h3>
            <button type="button" onclick="closeFaqModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form action="../../controllers/admin/proses_edit_faq.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="id_faq" id="faq_id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pertanyaan <span class="text-red-500">*</span></label>
                <input type="text" name="pertanyaan" id="faq_pertanyaan" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jawaban <span class="text-red-500">*</span></label>
                <textarea name="jawaban" id="faq_jawaban" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <input type="text" name="kategori" id="faq_kategori" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                    <input type="number" name="urutan" id="faq_urutan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="faq_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeFaqModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
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
const faqModal = document.getElementById('faqModal');
const faqIdInput = document.getElementById('faq_id');
const faqPertanyaanInput = document.getElementById('faq_pertanyaan');
const faqJawabanInput = document.getElementById('faq_jawaban');
const faqKategoriInput = document.getElementById('faq_kategori');
const faqUrutanInput = document.getElementById('faq_urutan');
const faqStatusInput = document.getElementById('faq_status');

function openFaqModal(data) {
    faqIdInput.value = data.id_faq;
    faqPertanyaanInput.value = data.pertanyaan;
    faqJawabanInput.value = data.jawaban;
    faqKategoriInput.value = data.kategori ?? '';
    faqUrutanInput.value = data.urutan ?? 0;
    faqStatusInput.value = data.status;
    faqModal.classList.remove('hidden');
    faqModal.classList.add('flex');
}

function closeFaqModal() {
    faqModal.classList.add('hidden');
    faqModal.classList.remove('flex');
}

function deleteFaq(id) {
    if (confirm('Hapus FAQ ini?')) {
        window.location.href = '../../controllers/admin/hapus_faq.php?id=' + id;
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !faqModal.classList.contains('hidden')) {
        closeFaqModal();
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer_admin.php'; ?>

