<?php
// halaman/login/daftar.php — registrasi akun email + password via Supabase
if (!empty($_SESSION['sb_access_token'])) {
    redirect('dashboard');
}

$galat = '';
$suksesInfo = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $ulang    = $_POST['password2'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $galat = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $galat = 'Password minimal 6 karakter.';
    } elseif ($password !== $ulang) {
        $galat = 'Konfirmasi password tidak sama.';
    } else {
        [$sukses, $pesan] = auth_daftar($email, $password);
        if ($sukses) {
            redirect('dashboard');
        }
        // Pesan "cek email" bukan galat, tampilkan sebagai info
        if (str_contains($pesan, 'Registrasi berhasil')) {
            $suksesInfo = $pesan;
            $_SESSION['sb_info'] = $pesan . ' Silakan masuk setelah verifikasi.';
            redirect('login');
        }
        $galat = $pesan;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Daftar | <?= APP_NAME ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/adminlte.css" />
</head>
<body class="login-page bg-body-secondary">
  <div class="login-box">
    <div class="card">
      <div class="card-body login-card-body">
        <h4 class="text-center mb-3">Buat Akun</h4>
        <?php if ($galat): ?>
          <div class="alert alert-danger"><?= e($galat) ?></div>
        <?php endif; ?>
        <form method="post">
          <input class="form-control mb-3" type="email" name="email" placeholder="Email" value="<?= e($_POST['email'] ?? '') ?>" required>
          <input class="form-control mb-3" type="password" name="password" placeholder="Password (min. 6 karakter)" required>
          <input class="form-control mb-3" type="password" name="password2" placeholder="Ulangi password" required>
          <button class="btn btn-primary w-100">Daftar</button>
        </form>
        <p class="text-center mt-3 mb-0">
          Sudah punya akun? <a href="<?= BASE_URL ?>index.php?hal=login">Masuk</a>
        </p>
      </div>
    </div>
  </div>
</body>
</html>
