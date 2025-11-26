<?php
/**
 * Header Layout untuk Admin
 * 
 * Usage:
 * $current_page = 'index'; // atau 'kelola_karya', dll
 * include __DIR__ . '/../layouts/header_admin.php';
 */

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$current_page = isset($current_page) ? $current_page : 'index';
$page_title = isset($page_title) ? $page_title : 'Dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Portal TPL Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        @media (max-width: 768px) {
            .sidebar-overlay {
                transition: opacity 0.3s ease;
            }
            .sidebar {
                transition: transform 0.3s ease;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    
    <div class="flex h-screen overflow-hidden">
        
        <!-- Mobile Sidebar Overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden sidebar-overlay" onclick="toggleSidebar()"></div>
        
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed md:static inset-y-0 left-0 transform -translate-x-full md:translate-x-0 w-64 bg-indigo-900 text-white flex flex-col z-50 sidebar">
            
            <!-- Logo & Header -->
            <div class="p-6 border-b border-indigo-800 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Portal TPL</h1>
                    <p class="text-indigo-300 text-sm mt-1">Admin Panel</p>
                </div>
                <button onclick="toggleSidebar()" class="md:hidden text-white hover:text-indigo-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="index.php" class="flex items-center px-4 py-3 <?php echo $current_page == 'index' ? 'bg-indigo-800' : 'hover:bg-indigo-800'; ?> rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>
                
                <a href="kelola_karya.php" class="flex items-center px-4 py-3 <?php echo $current_page == 'kelola_karya' || $current_page == 'form_tambah_karya' || $current_page == 'form_edit_karya' ? 'bg-indigo-800' : 'hover:bg-indigo-800'; ?> rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Kelola Karya</span>
                </a>

                <a href="kelola_kategori.php" class="flex items-center px-4 py-3 <?php echo $current_page == 'kelola_kategori' ? 'bg-indigo-800' : 'hover:bg-indigo-800'; ?> rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span>Kelola Kategori</span>
                </a>

                <div class="pt-4 mt-4 border-t border-indigo-800">
                    <p class="px-4 text-xs font-semibold text-indigo-300 uppercase tracking-wide mb-2">Konten Publik</p>
                    <a href="kelola_faq.php" class="flex items-center px-4 py-3 <?php echo $current_page == 'kelola_faq' ? 'bg-indigo-800' : 'hover:bg-indigo-800'; ?> rounded-lg transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h4l2 2h4a2 2 0 012 2v12a2 2 0 01-2 2z" />
                        </svg>
                        <span>Kelola FAQ</span>
                    </a>
                    <a href="kelola_tentang.php" class="flex items-center px-4 py-3 <?php echo $current_page == 'kelola_tentang' ? 'bg-indigo-800' : 'hover:bg-indigo-800'; ?> rounded-lg transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2-1.343-2-3-2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16v12H4z" />
                        </svg>
                        <span>Konten Tentang</span>
                    </a>
                    <a href="kelola_dosen.php" class="flex items-center px-4 py-3 <?php echo $current_page == 'kelola_dosen' ? 'bg-indigo-800' : 'hover:bg-indigo-800'; ?> rounded-lg transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A4 4 0 017 17h10a4 4 0 011.879.804L21 19.5V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14.5l2.121-1.696z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11a3 3 0 100-6 3 3 0 000 6z" />
                        </svg>
                        <span>Kelola Dosen</span>
                    </a>
                    <a href="kelola_matkul.php" class="flex items-center px-4 py-3 <?php echo $current_page == 'kelola_matkul' ? 'bg-indigo-800' : 'hover:bg-indigo-800'; ?> rounded-lg transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 11-4 0 2 2 0 014 0zm-6 8v6m0-6a2 2 0 100 4 2 2 0 000-4zm12-2v8m0-8a2 2 0 100 4 2 2 0 000-4z" />
                        </svg>
                        <span>Kelola Mata Kuliah</span>
                    </a>
                </div>
                
                <div class="pt-4 mt-4 border-t border-indigo-800">
                    <p class="px-4 text-xs font-semibold text-indigo-300 uppercase tracking-wide mb-2">Sistem</p>
                    <a href="log_admin.php" class="flex items-center px-4 py-3 <?php echo $current_page == 'log_admin' ? 'bg-indigo-800' : 'hover:bg-indigo-800'; ?> rounded-lg transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Log Admin</span>
                    </a>
                </div>
                
                <div class="pt-4 mt-4 border-t border-indigo-800">
                    <a href="../../views/public/index.php" target="_blank" rel="noopener noreferrer" class="flex items-center px-4 py-3 hover:bg-indigo-800 rounded-lg transition text-indigo-300">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span>Lihat Situs Publik</span>
                    </a>
                </div>

                <div class="pt-4 mt-4 border-t border-indigo-800">
                    <p class="px-4 text-xs font-semibold text-indigo-300 uppercase tracking-wide mb-2">Pengaturan</p>
                    <a href="ubah_password.php" class="flex items-center px-4 py-3 <?php echo $current_page == 'ubah_password' ? 'bg-indigo-800' : 'hover:bg-indigo-800'; ?> rounded-lg transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <span>Ubah Password</span>
                    </a>
                </div>
            </nav>
            
            <!-- User Profile & Logout -->
            <div class="p-4 border-t border-indigo-800">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-indigo-700 rounded-full flex items-center justify-center">
                        <span class="text-lg font-bold"><?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?></span>
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <p class="font-medium truncate"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
                        <p class="text-xs text-indigo-300">Administrator</p>
                    </div>
                </div>
                <a href="../../controllers/admin/logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?')" 
                   class="flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg transition w-full">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Logout</span>
                </a>
            </div>
        </aside>
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Mobile Header -->
            <header class="md:hidden bg-white shadow-sm p-4 flex items-center justify-between">
                <div class="flex items-center">
                    <button onclick="toggleSidebar()" class="text-gray-600 hover:text-gray-900 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-lg font-bold text-gray-800">Portal TPL</h1>
                </div>
                <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                    <span class="text-sm font-bold text-white"><?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?></span>
                </div>
            </header>
            
            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto">

