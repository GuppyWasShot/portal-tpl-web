<?php
session_start();
$page_title = "Kelola Dosen";
$current_page = 'kelola_dosen';

require_once __DIR__ . '/../../app/autoload.php';
$db = Database::getInstance()->getConnection();
$conn = $db;

$alert_type = '';
$alert_message = '';

if (isset($_GET['success'])) {
    $alert_type = 'success';
    $alert_message = match ($_GET['success']) {
        'created' => 'Data dosen berhasil ditambahkan.',
        'updated' => 'Data dosen berhasil diperbarui.',
        'deleted' => 'Data dosen berhasil dihapus.',
        default => ''
    };
} elseif (isset($_GET['error'])) {
    $alert_type = 'error';
    $alert_message = match ($_GET['error']) {
        'empty_field' => 'Nama dosen wajib diisi.',
        'invalid_file' => 'Format atau ukuran foto tidak valid (JPG/PNG/WEBP maks 2MB).',
        'upload_dir' => 'Folder upload dosen tidak dapat diakses.',
        'upload_failed' => 'Gagal mengunggah foto dosen.',
        'invalid_id' => 'ID dosen tidak valid.',
        'not_found' => 'Data dosen tidak ditemukan.',
        'database_error' => 'Terjadi kesalahan pada database.',
        default => 'Terjadi kesalahan. Coba lagi nanti.'
    };
}

$dosen_list = [];
$result = $conn->query("SELECT * FROM tbl_dosen ORDER BY status DESC, urutan ASC, nama ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $dosen_list[] = $row;
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
            <span class="text-gray-900 font-medium">Kelola Dosen</span>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Kelola Data Dosen</h2>
        <p class="text-gray-600 mt-1">Atur profil dosen beserta foto dan informasi kontak.</p>
    </div>
</header>

<div class="p-8 space-y-6">

    <?php if (!empty($alert_message)): ?>
    <div class="px-4 py-3 rounded-lg <?php echo $alert_type === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'; ?>">
        <?php echo htmlspecialchars($alert_message); ?>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Dosen Baru</h3>
        <form action="../../controllers/admin/proses_tambah_dosen.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gelar Akademik</label>
                    <input type="text" name="gelar" placeholder="S.Kom., M.Kom." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                    <input type="text" name="jabatan" placeholder="Contoh: Koordinator Program" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" placeholder="nama@apps.ipb.ac.id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Research Interest / Deskripsi</label>
                <textarea name="deskripsi" rows="4" placeholder="Tuliskan minat riset atau deskripsi singkat dosen." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Urutan Tampil</label>
                    <input type="number" name="urutan" value="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto</label>
                    <input type="file" name="foto" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">JPG/PNG/WEBP, maks 2MB</p>
                </div>
            </div>
            <button type="submit" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                Simpan Dosen
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Daftar Dosen</h3>
                <p class="text-sm text-gray-500 mt-1">Total dosen: <?php echo count($dosen_list); ?></p>
            </div>
        </div>

        <?php if (count($dosen_list) === 0): ?>
            <div class="text-center py-12 text-gray-500 border-2 border-dashed border-gray-200 rounded-lg">
                Belum ada data dosen.
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <?php foreach ($dosen_list as $dosen): ?>
                <div class="border border-gray-200 rounded-xl p-4 flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <img src="../../<?php echo !empty($dosen['foto_url']) ? htmlspecialchars($dosen['foto_url']) : 'assets/img/fd.png'; ?>"
                             alt="Foto <?php echo htmlspecialchars($dosen['nama']); ?>"
                             class="w-14 h-14 rounded-full object-cover border">
                        <div>
                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($dosen['nama']); ?></p>
                            <?php if (!empty($dosen['gelar'])): ?>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($dosen['gelar']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if ($dosen['status'] === 'inactive'): ?>
                            <span class="ml-auto px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full">Nonaktif</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($dosen['jabatan'])): ?>
                        <p class="text-sm text-gray-700 font-medium"><?php echo htmlspecialchars($dosen['jabatan']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($dosen['email'])): ?>
                        <p class="text-sm text-indigo-600"><?php echo htmlspecialchars($dosen['email']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($dosen['deskripsi'])): ?>
                        <p class="text-sm text-gray-600 line-clamp-3"><?php echo htmlspecialchars($dosen['deskripsi']); ?></p>
                    <?php endif; ?>
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                        <button type="button"
                                class="px-3 py-1.5 text-sm text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                onclick='openDosenModal(<?php echo htmlspecialchars(json_encode($dosen), ENT_QUOTES, "UTF-8"); ?>)'>
                            Edit
                        </button>
                        <button type="button"
                                class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition"
                                onclick="deleteDosen(<?php echo $dosen['id_dosen']; ?>)">
                            Hapus
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Edit Dosen -->
<div id="dosenModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeDosenModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-4xl mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Edit Data Dosen</h3>
            <button type="button" onclick="closeDosenModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form action="../../controllers/admin/proses_edit_dosen.php" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <input type="hidden" name="id_dosen" id="dosen_id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="dosen_nama" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gelar Akademik</label>
                    <input type="text" name="gelar" id="dosen_gelar" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                    <input type="text" name="jabatan" id="dosen_jabatan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="dosen_email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Research Interest / Deskripsi</label>
                <textarea name="deskripsi" id="dosen_deskripsi" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Urutan Tampil</label>
                    <input type="number" name="urutan" id="dosen_urutan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="dosen_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto (Opsional)</label>
                    <input type="file" name="foto" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Unggah foto baru untuk mengganti yang lama.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeDosenModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
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
const dosenModal = document.getElementById('dosenModal');
const dosenIdInput = document.getElementById('dosen_id');
const dosenNamaInput = document.getElementById('dosen_nama');
const dosenGelarInput = document.getElementById('dosen_gelar');
const dosenJabatanInput = document.getElementById('dosen_jabatan');
const dosenEmailInput = document.getElementById('dosen_email');
const dosenDeskripsiInput = document.getElementById('dosen_deskripsi');
const dosenUrutanInput = document.getElementById('dosen_urutan');
const dosenStatusInput = document.getElementById('dosen_status');

function openDosenModal(data) {
    dosenIdInput.value = data.id_dosen;
    dosenNamaInput.value = data.nama ?? '';
    dosenGelarInput.value = data.gelar ?? '';
    dosenJabatanInput.value = data.jabatan ?? '';
    dosenEmailInput.value = data.email ?? '';
    dosenDeskripsiInput.value = data.deskripsi ?? '';
    dosenUrutanInput.value = data.urutan ?? 0;
    dosenStatusInput.value = data.status ?? 'active';
    dosenModal.classList.remove('hidden');
    dosenModal.classList.add('flex');
}

function closeDosenModal() {
    dosenModal.classList.add('hidden');
    dosenModal.classList.remove('flex');
}

function deleteDosen(id) {
    if (confirm('Hapus data dosen ini?')) {
        window.location.href = '../../controllers/admin/hapus_dosen.php?id=' + id;
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !dosenModal.classList.contains('hidden')) {
        closeDosenModal();
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer_admin.php'; ?>

