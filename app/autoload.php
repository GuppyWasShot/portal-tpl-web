<?php
/**
 * Autoload file untuk memuat semua class yang dibutuhkan
 * File ini di-include di setiap halaman yang membutuhkan akses ke class
 */

// Set timezone ke Jakarta (WIB) untuk semua operasi tanggal/waktu
date_default_timezone_set('Asia/Jakarta');

spl_autoload_register(function ($class) {
    // Base directory untuk models
    $base_dir = __DIR__ . '/models/';
    
    // File path
    $file = $base_dir . $class . '.php';
    
    // Jika file exists, require it
    if (file_exists($file)) {
        require_once $file;
    }
});

