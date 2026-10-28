<?php
// halaman/login/index.php — form login email + password dan tombol Google
if (!empty($_SESSION['sb_access_token'])) {
    redirect('dashboard');
}

$galat = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$sukses, $pesan] = auth_login_email(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
    if ($sukses) {
        redirect('dashboard');
    }
    $galat = $pesan;
}

// Info singkat (mis. hasil registrasi) lewat flash session
$info = $_SESSION['sb_info'] ?? '';
unset($_SESSION['sb_info']);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login | <?= APP_NAME ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/adminlte.css" />
</head>
<body class="login-page bg-body-secondary">
  <div class="login-box">
    <div class="card">
      <div class="card-body login-card-body">
        <h4 class="text-center mb-3"><?= APP_NAME ?></h4>
        <?php if ($galat): ?>
          <div class="alert alert-danger"><?= e($galat) ?></div>
        <?php elseif ($info): ?>
          <div class="alert alert-info"><?= e($info) ?></div>
        <?php endif; ?>
        <form method="post">
          <input class="form-control mb-3" type="email" name="email" placeholder="Email" required>
          <input class="form-control mb-3" type="password" name="password" placeholder="Password" required>
          <button class="btn btn-primary w-100">Masuk</button>
        </form>
        <div class="text-center my-3">
          <span class="text-secondary small">atau</span>
        </div>
        <a class="btn btn-outline-secondary w-100" href="<?= BASE_URL ?>index.php?hal=login&aksi=google">
          <i class="bi bi-google me-1"></i> Masuk dengan Google
        </a>
        <p class="text-center mt-3 mb-0">
          Belum punya akun? <a href="<?= BASE_URL ?>index.php?hal=login&aksi=daftar">Daftar</a>
        </p>
      </div>
    </div>
  </div>
</body>
</html>
