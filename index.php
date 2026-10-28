<?php
// index.php — router utama
// Cookie session: hanya diakses via HTTP, policy SameSite=Lax
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/auth/supabase.php';
require_once __DIR__ . '/koneksi.php';

$hal  = $_GET['hal']  ?? 'dashboard';
$aksi = $_GET['aksi'] ?? 'index';

// route => mode tampilan
$peta = [
    'dashboard' => ['index' => 'layout'],
    'login'     => [
        'index'    => 'mandiri',
        'daftar'   => 'mandiri',
        'google'   => 'mandiri',
        'callback' => 'mandiri',
        'logout'   => 'mandiri',
    ],
    'buku'      => [
        'index'  => 'layout',
        'create' => 'layout',
        'update' => 'layout',
        'print'  => 'mandiri',
    ],
    'anggota'   => [
        'index'  => 'layout',
        'create' => 'layout',
        'update' => 'layout',
    ],
];

if (!isset($peta[$hal][$aksi])) {
    http_response_code(404);
    exit('Halaman tidak ditemukan.');
}

// Penjaga login: verifikasi JWT Supabase di setiap request halaman terlindung
if ($hal !== 'login') {
    $claims = auth_sesi_valid();
    if ($claims === null) {
        redirect('login');
    }
}

$file = ROOT_PATH . "/halaman/$hal/$aksi.php";
$mode = $peta[$hal][$aksi];

if ($mode === 'mandiri') {
    require $file;   // halaman membawa HTML-nya sendiri
    exit;
}

// Mode layout: jalankan halaman DULU, simpan hasilnya di buffer
ob_start();
require $file;
$konten = ob_get_clean();

require ROOT_PATH . '/template/header.php';
require ROOT_PATH . '/template/sidebar.php';
echo $konten;
require ROOT_PATH . '/template/footer.php';
