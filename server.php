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

    // Kirim salinan terkompresi (dibuat saat `npm run build`): Brotli bila didukung, lalu gzip.
    $acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
    if (! in_array($ext, ['gz', 'br'], true)) {
        if (str_contains($acceptEncoding, 'br') && is_file($file.'.br')) {
            $file .= '.br';
            header('Content-Encoding: br');
        } elseif (str_contains($acceptEncoding, 'gzip') && is_file($file.'.gz')) {
            $file .= '.gz';
            header('Content-Encoding: gzip');
        }
    }

    header('Content-Length: '.filesize($file));

    if ($_SERVER['REQUEST_METHOD'] !== 'HEAD') {
        readfile($file);
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
