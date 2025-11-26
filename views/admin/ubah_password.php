<?php
/**
 * Halaman Ubah Password Admin
 */

// Start session first
session_start();

$page_title = "Ubah Password";
$current_page = 'ubah_password';

require_once __DIR__ . '/../../app/autoload.php';

// Session check akan dilakukan di header_admin.php
// Jadi tidak perlu check lagi di sini

$db = Database::getInstance()->getConnection();

// Fix: Gunakan nama session yang benar dari proses_login.php
$id_admin = $_SESSION['admin_id'] ?? null;
$username = $_SESSION['admin_username'] ?? null;

// Fallback untuk backward compatibility
if (!$id_admin && isset($_SESSION['id_admin'])) {
    $id_admin = $_SESSION['id_admin'];
}
if (!$username && isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
}

// Ambil data admin
$stmt = $db->prepare("SELECT username, email FROM tbl_admin WHERE id_admin = ?");
$stmt->bind_param("i", $id_admin);
$stmt->execute();
$result = $stmt->get_result();
$admin_data = $result->fetch_assoc();
$stmt->close();

include __DIR__ . '/../layouts/header_admin.php';
?>

<!-- Main Content -->
<div class="p-6 md:p-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Ubah Password</h1>
        <p class="text-gray-600 mt-2">Perbarui password akun admin Anda</p>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
        </div>
    </div>
    <?php unset($_SESSION['success_message']); endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <p><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
        </div>
    </div>
    <?php unset($_SESSION['error_message']); endif; ?>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow-md max-w-2xl">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Informasi Akun</h2>
        </div>
        
        <div class="p-6 space-y-4 bg-gray-50 border-b border-gray-200">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <p class="text-gray-900 font-semibold"><?php echo htmlspecialchars($admin_data['username']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <p class="text-gray-900">
                    <?php 
                    if (!empty($admin_data['email'])) {
                        echo htmlspecialchars($admin_data['email']); 
                    } else {
                        echo '<span class="text-gray-400 italic">Email belum diset</span>';
                    }
                    ?>
                </p>
            </div>
        </div>

        <form action="../../controllers/admin/proses_ubah_password.php" method="POST" class="p-6">
            <div class="space-y-6">
                <!-- Password Lama -->
                <div>
                    <label for="password_lama" class="block text-sm font-medium text-gray-700 mb-2">
                        Password Lama <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password_lama" 
                            name="password_lama" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            placeholder="Masukkan password lama"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword('password_lama')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Password Baru -->
                <div>
                    <label for="password_baru" class="block text-sm font-medium text-gray-700 mb-2">
                        Password Baru <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password_baru" 
                            name="password_baru" 
                            required
                            minlength="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            placeholder="Masukkan password baru (min. 6 karakter)"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword('password_baru')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Password minimal 6 karakter</p>
                </div>

                <!-- Konfirmasi Password Baru -->
                <div>
                    <label for="konfirmasi_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Konfirmasi Password Baru <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="konfirmasi_password" 
                            name="konfirmasi_password" 
                            required
                            minlength="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            placeholder="Ulangi password baru"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword('konfirmasi_password')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 pt-4">
                    <button 
                        type="submit" 
                        class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition font-semibold focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Ubah Password
                    </button>
                    <a 
                        href="index.php" 
                        class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-semibold text-center focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                    >
                        Batal
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Security Tips -->
    <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg max-w-2xl">
        <div class="flex">
            <svg class="w-5 h-5 text-blue-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-blue-800 mb-1">Tips Keamanan Password</h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol</li>
                    <li>• Jangan gunakan informasi pribadi yang mudah ditebak</li>
                    <li>• Ubah password secara berkala untuk keamanan lebih baik</li>
                    <li>• Jangan bagikan password Anda kepada siapapun</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    if (field.type === 'password') {
        field.type = 'text';
    } else {
        field.type = 'password';
    }
}

// Validasi form sebelum submit
document.querySelector('form').addEventListener('submit', function(e) {
    const passwordBaru = document.getElementById('password_baru').value;
    const konfirmasi = document.getElementById('konfirmasi_password').value;
    
    if (passwordBaru !== konfirmasi) {
        e.preventDefault();
        alert('Password baru dan konfirmasi password tidak cocok!');
        return false;
    }
    
    if (passwordBaru.length < 6) {
        e.preventDefault();
        alert('Password baru minimal 6 karakter!');
        return false;
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer_admin.php'; ?>
