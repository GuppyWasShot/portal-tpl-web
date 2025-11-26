<?php
session_start();
$page_title = "Kelola Mata Kuliah";
$current_page = 'kelola_matkul';

require_once __DIR__ . '/../../app/autoload.php';
$db = Database::getInstance()->getConnection();
$conn = $db;

$alert_type = '';
$alert_message = '';

if (isset($_GET['success'])) {
    $alert_type = 'success';
    $alert_message = match ($_GET['success']) {
        'created' => 'Mata kuliah berhasil ditambahkan.',
        'updated' => 'Mata kuliah berhasil diperbarui.',
        'deleted' => 'Mata kuliah berhasil dihapus.',
        default => ''
    };
} elseif (isset($_GET['error'])) {
    $alert_type = 'error';
    $alert_message = match ($_GET['error']) {
        'empty_field' => 'Kode, nama, dan semester wajib diisi.',
        'invalid_id' => 'ID mata kuliah tidak valid.',
        'not_found' => 'Data mata kuliah tidak ditemukan.',
        'database_error' => 'Terjadi kesalahan pada database.',
        default => 'Terjadi kesalahan. Coba lagi nanti.'
    };
}

$matkul_list = [];
$result = $conn->query("SELECT * FROM tbl_matkul ORDER BY semester ASC, urutan ASC, nama ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $matkul_list[] = $row;
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
            <span class="text-gray-900 font-medium">Kelola Mata Kuliah</span>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Kelola Kurikulum Mata Kuliah</h2>
        <p class="text-gray-600 mt-1">Tambahkan dan atur data mata kuliah per semester.</p>
    </div>
</header>

<div class="p-8 space-y-6">

    <?php if (!empty($alert_message)): ?>
    <div class="px-4 py-3 rounded-lg <?php echo $alert_type === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'; ?>">
        <?php echo htmlspecialchars($alert_message); ?>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Mata Kuliah Baru</h3>
        <form action="../../controllers/admin/proses_tambah_matkul.php" method="POST" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode MK <span class="text-red-500">*</span></label>
                    <input type="text" name="kode" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Mata Kuliah <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Semester <span class="text-red-500">*</span></label>
                    <input type="number" name="semester" min="1" max="8" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SKS</label>
                    <input type="number" name="sks" min="0" max="30" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <input type="text" name="kategori" placeholder="Wajib/Pilihan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                    <input type="number" name="urutan" value="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan/Deskripsi</label>
                <textarea name="deskripsi" rows="3" placeholder="Contoh: 3(1-2) atau deskripsi singkat mata kuliah" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                Simpan Mata Kuliah
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Daftar Mata Kuliah</h3>
                <p class="text-sm text-gray-500 mt-1">Klik semester untuk melihat detail.</p>
            </div>
        </div>

        <?php if (count($matkul_list) === 0): ?>
            <div class="text-center py-12 text-gray-500 border-2 border-dashed border-gray-200 rounded-lg">
                Belum ada data mata kuliah.
            </div>
        <?php else: ?>
            <?php
            $grouped = [];
            foreach ($matkul_list as $matkul) {
                $grouped[$matkul['semester']][] = $matkul;
            }
            ksort($grouped);
            ?>
            <div class="space-y-6">
                <?php foreach ($grouped as $semester => $items): ?>
                <div class="border border-gray-200 rounded-xl">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                        <h4 class="text-lg font-semibold text-gray-800">Semester <?php echo $semester; ?></h4>
                        <span class="text-sm text-gray-500"><?php echo count($items); ?> mata kuliah</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKS</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                <?php foreach ($items as $matkul): ?>
                                <tr>
                                    <td class="px-4 py-3 font-mono text-gray-700"><?php echo htmlspecialchars($matkul['kode']); ?></td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($matkul['nama']); ?></p>
                                        <?php if (!empty($matkul['deskripsi'])): ?>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($matkul['deskripsi']); ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700"><?php echo $matkul['sks']; ?></td>
                                    <td class="px-4 py-3 text-gray-700"><?php echo !empty($matkul['kategori']) ? htmlspecialchars($matkul['kategori']) : '-'; ?></td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button"
                                                    class="px-3 py-1.5 text-sm text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                                    onclick='openMatkulModal(<?php echo htmlspecialchars(json_encode($matkul), ENT_QUOTES, "UTF-8"); ?>)'>
                                                Edit
                                            </button>
                                            <button type="button"
                                                    class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition"
                                                    onclick="deleteMatkul(<?php echo $matkul['id_matkul']; ?>)">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Edit Matkul -->
<div id="matkulModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeMatkulModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-4xl mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Edit Mata Kuliah</h3>
            <button type="button" onclick="closeMatkulModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form action="../../controllers/admin/proses_edit_matkul.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="id_matkul" id="matkul_id">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode MK <span class="text-red-500">*</span></label>
                    <input type="text" name="kode" id="matkul_kode" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Mata Kuliah <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="matkul_nama" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Semester <span class="text-red-500">*</span></label>
                    <input type="number" name="semester" id="matkul_semester" min="1" max="8" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SKS</label>
                    <input type="number" name="sks" id="matkul_sks" min="0" max="30" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <input type="text" name="kategori" id="matkul_kategori" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                    <input type="number" name="urutan" id="matkul_urutan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan/Deskripsi</label>
                <textarea name="deskripsi" id="matkul_deskripsi" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="matkul_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeMatkulModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
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
const matkulModal = document.getElementById('matkulModal');
const matkulIdInput = document.getElementById('matkul_id');
const matkulKodeInput = document.getElementById('matkul_kode');
const matkulNamaInput = document.getElementById('matkul_nama');
const matkulSemesterInput = document.getElementById('matkul_semester');
const matkulSksInput = document.getElementById('matkul_sks');
const matkulKategoriInput = document.getElementById('matkul_kategori');
const matkulDeskripsiInput = document.getElementById('matkul_deskripsi');
const matkulUrutanInput = document.getElementById('matkul_urutan');
const matkulStatusInput = document.getElementById('matkul_status');

function openMatkulModal(data) {
    matkulIdInput.value = data.id_matkul;
    matkulKodeInput.value = data.kode ?? '';
    matkulNamaInput.value = data.nama ?? '';
    matkulSemesterInput.value = data.semester ?? 1;
    matkulSksInput.value = data.sks ?? 0;
    matkulKategoriInput.value = data.kategori ?? '';
    matkulDeskripsiInput.value = data.deskripsi ?? '';
    matkulUrutanInput.value = data.urutan ?? 0;
    matkulStatusInput.value = data.status ?? 'active';
    matkulModal.classList.remove('hidden');
    matkulModal.classList.add('flex');
}

function closeMatkulModal() {
    matkulModal.classList.add('hidden');
    matkulModal.classList.remove('flex');
}

function deleteMatkul(id) {
    if (confirm('Hapus mata kuliah ini?')) {
        window.location.href = '../../controllers/admin/hapus_matkul.php?id=' + id;
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !matkulModal.classList.contains('hidden')) {
        closeMatkulModal();
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer_admin.php'; ?>

