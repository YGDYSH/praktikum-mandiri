<?php
// halaman/login/callback.php — menerima kode OAuth dari Supabase lalu menukar dengan token
$code  = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

$verifier     = $_SESSION['sb_oauth_verifier'] ?? '';
$state_tersim = $_SESSION['sb_oauth_state'] ?? '';
unset($_SESSION['sb_oauth_verifier'], $_SESSION['sb_oauth_state']);

if ($code === '' || $state_tersim === '' || !hash_equals($state_tersim, $state)) {
    $_SESSION['sb_info'] = 'Callback OAuth tidak valid. Silakan coba masuk lagi.';
    redirect('login');
}

[$sukses, $pesan] = auth_tukar_code($code, $verifier);
if (!$sukses) {
    $_SESSION['sb_info'] = $pesan;
    redirect('login');
}

redirect('dashboard');
