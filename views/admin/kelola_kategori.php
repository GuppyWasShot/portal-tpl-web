<?php
session_start();
$page_title = "Kelola Kategori";
$current_page = 'kelola_kategori';

require_once __DIR__ . '/../../app/autoload.php';
$db = Database::getInstance()->getConnection();
$conn = $db;

$alert_type = '';
$alert_message = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'created':
            $alert_type = 'success';
            $alert_message = 'Kategori berhasil ditambahkan.';
            break;
        case 'updated':
            $alert_type = 'success';
            $alert_message = 'Kategori berhasil diperbarui.';
            break;
        case 'deleted':
            $alert_type = 'success';
            $alert_message = 'Kategori berhasil dihapus.';
            break;
    }
} elseif (isset($_GET['error'])) {
    $alert_type = 'error';
    switch ($_GET['error']) {
        case 'empty_field':
            $alert_message = 'Nama kategori wajib diisi.';
            break;
        case 'invalid_color':
            $alert_message = 'Format warna tidak valid. Gunakan format HEX, misal #6366F1.';
            break;
        case 'not_found':
            $alert_message = 'Kategori tidak ditemukan atau sudah dihapus.';
            break;
        case 'database_error':
            $alert_message = 'Terjadi kesalahan pada database. Coba lagi nanti.';
            break;
        default:
            $alert_message = 'Terjadi kesalahan. Coba lagi nanti.';
            break;
    }
}

$query_kategori = "SELECT * FROM tbl_category ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($conn, $query_kategori);
$kategori_list = [];
while ($row = mysqli_fetch_assoc($result_kategori)) {
    $kategori_list[] = $row;
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
            <span class="text-gray-900 font-medium">Kelola Kategori</span>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Kelola Kategori</h2>
        <p class="text-gray-600 mt-1">Tambahkan, ubah, atau hapus kategori yang digunakan pada karya.</p>
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
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Kategori Baru</h3>
            <form action="../../controllers/admin/proses_tambah_kategori.php" method="POST" class="space-y-4">
                <div>
                    <label for="nama_kategori" class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_kategori" name="nama_kategori" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Contoh: Web App">
                </div>
                <div>
                    <label for="warna_hex" class="block text-sm font-medium text-gray-700 mb-1">Warna Badge</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="warna_hex" name="warna_hex" value="#6366F1"
                               class="h-11 w-16 border border-gray-300 rounded-lg cursor-pointer">
                        <span class="text-sm text-gray-500">Gunakan warna yang konsisten untuk badge kategori.</span>
                    </div>
                </div>
                <button type="submit"
                        class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    Simpan Kategori
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Daftar Kategori</h3>
                    <p class="text-sm text-gray-500 mt-1">Total kategori: <?php echo count($kategori_list); ?></p>
                </div>
            </div>

            <?php if (count($kategori_list) === 0): ?>
                <div class="text-center py-12 text-gray-500 border-2 border-dashed border-gray-200 rounded-lg">
                    Belum ada kategori. Tambahkan kategori pertama Anda.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kategori</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Warna</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($kategori_list as $kategori): 
                                $warna = !empty($kategori['warna_hex']) ? $kategori['warna_hex'] : '#6B7280';
                            ?>
                            <tr>
                                <td class="px-4 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($kategori['nama_kategori']); ?></div>
                                    <div class="text-sm text-gray-500">ID: <?php echo $kategori['id_kategori']; ?></div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="w-8 h-8 rounded-full border border-gray-200" style="background-color: <?php echo htmlspecialchars($warna); ?>"></span>
                                        <span class="text-sm text-gray-700"><?php echo htmlspecialchars(strtoupper($warna)); ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button"
                                                onclick="openEditModal(<?php echo $kategori['id_kategori']; ?>, '<?php echo htmlspecialchars($kategori['nama_kategori'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($warna, ENT_QUOTES); ?>')"
                                                class="px-3 py-1.5 text-sm text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                            Edit
                                        </button>
                                        <button type="button"
                                                onclick="confirmDeleteKategori(<?php echo $kategori['id_kategori']; ?>)"
                                                class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition">
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

<!-- Modal Edit Kategori -->
<div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeEditModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Edit Kategori</h3>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form action="../../controllers/admin/proses_edit_kategori.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="id_kategori" id="edit_id_kategori">
            <div>
                <label for="edit_nama_kategori" class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                <input type="text" id="edit_nama_kategori" name="nama_kategori" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="edit_warna_hex" class="block text-sm font-medium text-gray-700 mb-1">Warna Badge</label>
                <input type="color" id="edit_warna_hex" name="warna_hex" class="h-11 w-16 border border-gray-300 rounded-lg cursor-pointer">
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
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
const editModal = document.getElementById('editModal');
const editIdInput = document.getElementById('edit_id_kategori');
const editNameInput = document.getElementById('edit_nama_kategori');
const editColorInput = document.getElementById('edit_warna_hex');

function openEditModal(id, name, color) {
    editIdInput.value = id;
    editNameInput.value = name;
    editColorInput.value = color || '#6366F1';
    editModal.classList.remove('hidden');
    editModal.classList.add('flex');
}

function closeEditModal() {
    editModal.classList.add('hidden');
    editModal.classList.remove('flex');
}

function confirmDeleteKategori(id) {
    if (confirm('Hapus kategori ini? Kategori akan dilepas dari semua karya yang terkait.')) {
        window.location.href = '../../controllers/admin/hapus_kategori.php?id=' + id;
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !editModal.classList.contains('hidden')) {
        closeEditModal();
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer_admin.php'; ?>

