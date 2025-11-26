<?php
session_start();
$page_title = "Kelola Karya";
require_once __DIR__ . '/../../app/autoload.php';
$db = Database::getInstance()->getConnection();
$conn = $db;
include __DIR__ . '/../layouts/header_admin.php';

// Ambil data kategori
$query_kategori = "SELECT * FROM tbl_category ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($conn, $query_kategori);
?>

<style>
.category-card {
    background-color: #f3f4f6;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1rem;
    transition: all .2s ease;
    color: #4b5563;
    text-align: center;
}
.category-card .category-name {
    color: #4b5563;
    font-weight: 600;
    font-size: 0.9rem;
    transition: color .2s ease;
}
.peer:checked + .category-card {
    background-color: var(--cat-bg, #eef2ff);
    border-color: var(--cat-border, #6366f1);
    color: var(--cat-text, #4338ca);
    box-shadow: 0 8px 20px rgba(79, 70, 229, 0.25);
}
.peer:checked + .category-card .category-name {
    color: var(--cat-text, #4338ca);
}
</style>

<header class="bg-white shadow-sm">
    <div class="px-8 py-6">
        <div class="flex items-center text-sm text-gray-500 mb-2">
            <a href="kelola_karya.php" class="hover:text-indigo-600">Kelola Karya</a>
            <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Tambah Karya</span>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Tambah Karya Baru</h2>
        <p class="text-gray-600 mt-1">Isi form di bawah untuk menambahkan karya mahasiswa</p>
    </div>
</header>

<div class="p-8">
    
    <?php if(isset($_GET['error'])): ?>
    <div class="mb-6 p-4 rounded-lg border bg-red-50 border-red-200 text-red-800">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-red-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm font-medium">
                <?php 
                if($_GET['error'] == 'empty_field') echo 'Mohon lengkapi semua field yang wajib diisi!';
                elseif($_GET['error'] == 'no_category') echo 'Pilih minimal satu kategori!';
                elseif($_GET['error'] == 'database_error') echo 'Terjadi kesalahan database: ' . (isset($_GET['msg']) ? $_GET['msg'] : '');
                ?>
            </span>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-xl shadow-md p-6">
        
        <form action="../../controllers/admin/proses_tambah_karya.php" method="POST" enctype="multipart/form-data" class="space-y-6">
            
            <!-- Judul Karya -->
            <div>
                <label for="judul" class="block text-sm font-medium text-gray-700 mb-2">
                    Judul Karya <span class="text-red-500">*</span>
                </label>
                <input type="text" id="judul" name="judul" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Masukkan judul karya">
            </div>
            
            <!-- Pembuat -->
            <div>
                <label for="pembuat" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Pembuat <span class="text-red-500">*</span>
                </label>
                <input type="text" id="pembuat" name="pembuat" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder='Nama atau tim pembuat (pisahkan dengan ";")'>
                <p class="text-xs text-gray-500 mt-1">Jika lebih dari satu nama, pisahkan menggunakan tanda titik koma (;). Contoh: "Ani; Budi; Charlie".</p>
            </div>
            
            <!-- Deskripsi -->
            <div>
                <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">
                    Deskripsi Karya <span class="text-red-500">*</span>
                </label>
                <textarea id="deskripsi" name="deskripsi" rows="5" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                          placeholder="Jelaskan tentang karya ini..."></textarea>
            </div>
            
            <!-- Kategori Toggle -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    <?php while($kategori = mysqli_fetch_assoc($result_kategori)): 
                        $color = $kategori['warna_hex'];
                        $bgColor = $color . '1a';
                    ?>
                    <label class="relative cursor-pointer">
                        <input type="checkbox" name="kategori[]" value="<?php echo $kategori['id_kategori']; ?>"
                               class="peer sr-only" >
                        <div class="category-card"
                             style="--cat-bg: <?php echo $bgColor; ?>;
                                    --cat-border: <?php echo $color; ?>;
                                    --cat-text: <?php echo $color; ?>;">
                            <span class="category-name">
                                    <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
                                </span>
                        </div>
                    </label>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <!-- Tanggal Selesai -->
            <div>
                <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal/Tahun Karya Selesai <span class="text-red-500">*</span>
                </label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <!-- Upload Multiple Snapshots dengan Konsep Tambah Bertahap -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Upload Snapshot/Preview
                </label>
                
                <!-- Hidden input untuk menyimpan file yang dipilih -->
                <input type="file" id="snapshotInput" accept="image/*" multiple class="hidden">
                
                <!-- Tombol untuk memicu input file -->
                <button type="button" onclick="document.getElementById('snapshotInput').click()" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Pilih Gambar
                </button>
                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, WEBP. Max 2MB per file. Bisa pilih multiple atau satu per satu.</p>
                
                <!-- Preview Container dengan tombol hapus per gambar -->
                <div id="previewContainer" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
            </div>
            
            <!-- Link Karya Utama -->
            <div>
                <label for="link_utama" class="block text-sm font-medium text-gray-700 mb-2">
                    Link Karya <span class="text-red-500">*</span>
                </label>
                <input type="url" id="link_utama" name="link_utama" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="https://example.com atau link download">
                <p class="text-xs text-gray-500 mt-1">Link website, aplikasi, atau download aplikasi</p>
            </div>
            
            <!-- Link Tambahan (Dynamic) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Link Tambahan (Opsional)</label>
                <div id="linksContainer" class="space-y-3"></div>
                <button type="button" onclick="addLinkField()" 
                        class="mt-3 px-4 py-2 text-sm text-indigo-600 border border-indigo-300 rounded-lg hover:bg-indigo-50 transition">
                    + Tambah Link
                </button>
            </div>
            
            <!-- File Pendukung (Dynamic) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">File Pendukung (Opsional)</label>
                <div id="filesContainer" class="space-y-3"></div>
                <button type="button" onclick="addFileField()" 
                        class="mt-3 px-4 py-2 text-sm text-indigo-600 border border-indigo-300 rounded-lg hover:bg-indigo-50 transition">
                    + Tambah File
                </button>
                <p class="text-xs text-gray-500 mt-1">Contoh: Dokumen HKI, User Manual, dll. (Max 5MB per file)</p>
            </div>
            
            <!-- Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="kelola_karya.php" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" name="action" value="draft"
                        class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition">
                    Save as Draft
                </button>
                <button type="submit" name="action" value="publish"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    Publish
                </button>
            </div>
            
        </form>
        
    </div>
</div>

<script>
// Array untuk menyimpan file yang dipilih
let selectedFiles = [];

// Event listener untuk input file
document.getElementById('snapshotInput').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    
    files.forEach(file => {
        // Validasi tipe dan ukuran
        if (!file.type.startsWith('image/')) {
            alert(file.name + ' bukan file gambar!');
            return;
        }
        
        if (file.size > 2 * 1024 * 1024) {
            alert(file.name + ' terlalu besar (max 2MB)!');
            return;
        }
        
        // Tambahkan ke array
        selectedFiles.push(file);
    });
    
    // Render preview
    renderSnapshotPreviews();
    
    // Reset input agar bisa pilih file yang sama lagi
    e.target.value = '';
});

function renderSnapshotPreviews() {
    const container = document.getElementById('previewContainer');
    container.innerHTML = '';
    
    selectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative group';
            div.innerHTML = `
                <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg border-2 border-gray-300">
                <div class="absolute top-2 right-2 flex gap-1">
                    <span class="bg-indigo-600 text-white text-xs px-2 py-1 rounded">${index + 1}</span>
                    <button type="button" onclick="removeSnapshot(${index})" 
                            class="bg-red-600 text-white p-1 rounded hover:bg-red-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            `;
            container.appendChild(div);
        }
        reader.readAsDataURL(file);
    });
    
    // Update FormData untuk form submission
    updateFormData();
}

function removeSnapshot(index) {
    selectedFiles.splice(index, 1);
    renderSnapshotPreviews();
}

function updateFormData() {
    // Hapus input file lama jika ada
    const oldInputs = document.querySelectorAll('input[name="snapshots[]"]');
    oldInputs.forEach(input => input.remove());
    
    // Buat DataTransfer object untuk menyimpan file
    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    
    // Buat input file baru dengan files dari DataTransfer
    const newInput = document.createElement('input');
    newInput.type = 'file';
    newInput.name = 'snapshots[]';
    newInput.multiple = true;
    newInput.className = 'hidden';
    newInput.files = dt.files;
    
    document.querySelector('form').appendChild(newInput);
}

function addLinkField() {
    const container = document.getElementById('linksContainer');
    const div = document.createElement('div');
    div.className = 'flex gap-2 items-start';
    div.innerHTML = `
        <input type="text" name="link_label[]" placeholder="Label (misal: GitHub)" 
               class="w-1/3 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
        <input type="url" name="link_url[]" placeholder="https://..." 
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
        <button type="button" onclick="this.parentElement.remove()" 
                class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    `;
    container.appendChild(div);
}

function addFileField() {
    const container = document.getElementById('filesContainer');
    const div = document.createElement('div');
    div.className = 'flex gap-2 items-start';
    div.innerHTML = `
        <input type="text" name="file_label[]" placeholder="Label (misal: Dokumen HKI)" 
               class="w-1/3 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
        <input type="file" name="file_upload[]" 
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
        <button type="button" onclick="this.parentElement.remove()" 
                class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    `;
    container.appendChild(div);
}
</script>

<?php include __DIR__ . '/../layouts/footer_admin.php'; ?>