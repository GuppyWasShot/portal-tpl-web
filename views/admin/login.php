<?php 
session_start(); 
// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Portal TPL</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-block bg-white p-4 rounded-full shadow-lg mb-4">
                <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 28 28">
                    <image href="../../assets/img/logotpl.png" x="0" y="0" width="28" height="28"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Portal TPL</h1>
        </div>

        <!-- Card Login -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            
            <?php
            // Tampilkan pesan error
            if (isset($_GET['error'])) {
                $error_msg = '';
                $sisa = isset($_GET['sisa']) ? intval($_GET['sisa']) : 0;
                
                switch ($_GET['error']) {
                    case 'gagal':
                        $error_msg = 'Username atau password salah!';
                        if ($sisa > 0) {
                            $error_msg .= " Sisa percobaan: $sisa";
                        }
                        break;
                    case 'terkunci':
                        $error_msg = 'Anda salah login 5x. Coba lagi dalam 10 menit.';
                        break;
                    case 'input_kosong':
                        $error_msg = 'Username dan password tidak boleh kosong!';
                        break;
                    case 'belum_login':
                        $error_msg = 'Silakan login terlebih dahulu.';
                        break;
                    case 'logout':
                        $error_msg = 'Anda berhasil logout.';
                        $is_success = true;
                        break;
                }
                
                $bg_color = isset($is_success) ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
                $icon_color = isset($is_success) ? 'text-green-400' : 'text-red-400';
                
                echo "<div class='mb-6 p-4 rounded-lg border $bg_color'>
                        <div class='flex items-start'>
                            <svg class='w-5 h-5 $icon_color mt-0.5 mr-3' fill='currentColor' viewBox='0 0 20 20'>
                                <path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' clip-rule='evenodd' />
                            </svg>
                            <span class='text-sm font-medium'>$error_msg</span>
                        </div>
                      </div>";
            }
            ?>

            <form action="../../controllers/admin/proses_login.php" method="POST" class="space-y-6">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Username
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required
                            autocomplete="username"
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            placeholder="Masukkan username"
                        >
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            autocomplete="current-password"
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            placeholder="Masukkan password"
                        >
                    </div>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                    Login
                </button>
            </form>

            <!-- Footer Link -->
            <div class="mt-6 text-center">
                <a href="../../views/public/index.php" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    ← Kembali ke Beranda
                </a>
            </div>
        </div>

        <!-- Copyright -->
        <div class="text-center mt-6 text-sm text-gray-600">
            &copy; <?php echo date('Y'); ?> Portal TPL - SV IPB
        </div>
    </div>

</body>
</html>