<?php
/**
 * Header Layout untuk Public Pages
 * 
 * Usage:
 * $page_title = "Judul Halaman";
 * $body_class = 'optional-css-class';
 * $additional_stylesheets = ['assets/css/page-example.css'];
 * include __DIR__ . '/../layouts/header_public.php';
 */

$page_title = isset($page_title) ? $page_title : 'Portal TPL';
$body_class = isset($body_class) ? trim($body_class) : '';
$additional_stylesheets = isset($additional_stylesheets) ? (array)$additional_stylesheets : [];

// Tentukan base path publik (hapus segmen /views/public dari request saat ini)
$script_dir = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
$asset_base_path = rtrim(str_replace('/views/public', '', $script_dir), '/');
if ($asset_base_path === '.' || $asset_base_path === '/') {
    $asset_base_path = '';
}

if (!function_exists('public_asset_url')) {
    function public_asset_url($path, $asset_base_path, $cache_bust = false) {
        if (preg_match('#^(https?:)?//#', $path)) {
            return $path;
        }

        // Hilangkan ../ agar selalu relatif dari root project
        $clean_path = preg_replace('#^(\.\./)+#', '', ltrim($path, '/'));
        $normalized = '/' . $clean_path;
        $url = ($asset_base_path !== '' ? $asset_base_path : '') . $normalized;

        if ($cache_bust) {
            $absolute_root = realpath(__DIR__ . '/../../' . $clean_path);
            if ($absolute_root && file_exists($absolute_root)) {
                $version = filemtime($absolute_root);
            } else {
                $version = time();
            }
            $separator = strpos($url, '?') === false ? '?' : '&';
            $url .= $separator . 'v=' . $version;
        }

        return $url;
    }
}

$stylesheets_to_load = array_merge(
    ['assets/css/styles.css', 'assets/css/layout-public.css'],
    $additional_stylesheets
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <?php foreach ($stylesheets_to_load as $stylesheet): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars(public_asset_url($stylesheet, $asset_base_path, true)); ?>">
    <?php endforeach; ?>
    <title><?php echo htmlspecialchars($page_title); ?> | PortalTPL</title>
</head>
<body<?php echo $body_class !== '' ? ' class="' . htmlspecialchars($body_class) . '"' : ''; ?>>
    <!-- Header -->
    <header class="public-header">
        <div class="logo">
            <img src="<?php echo htmlspecialchars(public_asset_url('assets/img/logo.svg', $asset_base_path)); ?>" alt="Logo TPL">
            <span>Portal<em>TPL</em></span>
        </div>
        <nav id="nav-menu" class="nav-menu" aria-label="Navigasi utama">
            <a href="index.php">Beranda</a>
            <a href="galeri.php">Daftar Karya</a>
            <a href="faq.php">FAQ</a>
            <a href="tentang.php">Tentang</a>
        </nav>
        <button 
            class="menu-toggle" 
            id="menu-toggle" 
            type="button" 
            aria-label="Buka navigasi utama"
            aria-expanded="false"
            aria-controls="nav-menu">
            <span class="sr-only">Navigasi</span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </button>
    </header>
    <div class="nav-overlay" id="nav-overlay"></div>

