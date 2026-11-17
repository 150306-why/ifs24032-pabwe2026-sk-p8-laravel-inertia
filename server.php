<?php

/**
 * Router kustom untuk `php artisan serve` (otomatis dipakai Laravel bila file ini ada di root proyek).
 *
 * Sama seperti router bawaan Laravel, ditambah: aset hasil build Vite (`/build/assets/*`) berversi hash,
 * jadi aman di-cache browser selama 1 tahun. PHP built-in server tidak mengirim header cache untuk file
 * statis, sehingga file tersebut dikirim langsung di sini beserta header Cache-Control.
 */
$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

$file = $publicPath.$uri;

if ($uri !== '/' && is_file($file) && str_starts_with($uri, '/build/assets/') && ! str_contains($uri, '..')) {
    $types = [
        'js' => 'application/javascript; charset=utf-8',
        'mjs' => 'application/javascript; charset=utf-8',
        'css' => 'text/css; charset=utf-8',
        'map' => 'application/json; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
    ];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    header('Content-Type: '.($types[$ext] ?? (mime_content_type($file) ?: 'application/octet-stream')));
    header('Cache-Control: public, max-age=31536000, immutable');
    header('Vary: Accept-Encoding');

    // Kompresi aset (php artisan serve tidak mengompres sendiri):
    // 1. Salinan .br/.gz hasil `npm run build` bila ada.
    // 2. Bila tidak ada, gzip langsung di sini (ekstensi zlib).
    // Bila header Accept-Encoding tidak sampai (dihapus reverse proxy), gzip tetap dipakai karena
    // semua browser modern mendukungnya; hanya klien yang tegas menolak gzip yang menerima file mentah.
    $acceptEncoding = strtolower($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '');
    $acceptsBrotli = str_contains($acceptEncoding, 'br');
    $acceptsGzip = $acceptEncoding === '' || str_contains($acceptEncoding, 'gzip') || str_contains($acceptEncoding, '*');
    $compressible = in_array($ext, ['js', 'mjs', 'css', 'map', 'json', 'svg'], true);

    $body = null;
    if ($compressible && $acceptsBrotli && is_file($file.'.br')) {
        $file .= '.br';
        header('Content-Encoding: br');
    } elseif ($compressible && $acceptsGzip && is_file($file.'.gz')) {
        $file .= '.gz';
        header('Content-Encoding: gzip');
    } elseif ($compressible && $acceptsGzip && function_exists('gzencode')) {
        $body = gzencode((string) file_get_contents($file), 6);
        header('Content-Encoding: gzip');
    }

    header('Content-Length: '.($body !== null ? strlen($body) : filesize($file)));

    if ($_SERVER['REQUEST_METHOD'] !== 'HEAD') {
        $body !== null ? print($body) : readfile($file);
    }

    return true;
}

if ($uri !== '/' && file_exists($file)) {
    return false;
}

$formattedDateTime = date('D M j H:i:s Y');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.'/index.php';
