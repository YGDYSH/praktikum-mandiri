<!doctype html>
<html lang="id">
<!--begin::Head-->
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="color-scheme" content="light dark" />
  <title><?= e($judulHalaman ?? APP_NAME) ?> | <?= APP_NAME ?></title>

  <!-- Font, Bootstrap Icons, OverlayScrollbars (CDN) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" crossorigin="anonymous" />

  <!-- CSS AdminLTE lokal -->
  <link rel="preload" href="<?= BASE_URL ?>assets/css/adminlte.css" as="style" />
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/adminlte.css" />

  <!-- Skrip anti-kedip tema gelap -->
  <script>
    (function () {
      var t = localStorage.getItem('theme');
      if (!t || t === 'auto') {
        t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      }
      document.documentElement.setAttribute('data-bs-theme', t);
    })();
  </script>
</head>
<!--end::Head-->
<!-- PASANGAN: <body> dan <div class="app-wrapper"> dibuka di sini, DITUTUP di footer.php -->
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

  <!--begin::Header-->
  <nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
      <!--begin::Start Navbar Links-->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
            <i class="bi bi-list"></i>
          </a>
        </li>
        <li class="nav-item d-none d-md-block">
          <a href="<?= BASE_URL ?>index.php?hal=dashboard" class="nav-link">Beranda</a>
        </li>
      </ul>
      <!--end::Start Navbar Links-->

      <!--begin::End Navbar Links-->
      <ul class="navbar-nav ms-auto">

        <!--begin::Fullscreen Toggle-->
        <li class="nav-item">
          <a class="nav-link" href="#" data-lte-toggle="fullscreen">
            <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
            <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
          </a>
        </li>
        <!--end::Fullscreen Toggle-->

        <!--begin::Color Mode Toggle-->
        <li class="nav-item dropdown">
          <button class="btn btn-link nav-link py-2 px-0 px-lg-2 dropdown-toggle d-flex align-items-center"
                  id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown">
            <span class="theme-icon-active"><i class="my-1 bi bi-circle-half"></i></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" style="--bs-dropdown-min-width: 8rem;">
            <li><button type="button" class="dropdown-item" data-bs-theme-value="light">Terang</button></li>
            <li><button type="button" class="dropdown-item" data-bs-theme-value="dark">Gelap</button></li>
            <li><button type="button" class="dropdown-item" data-bs-theme-value="auto">Otomatis</button></li>
          </ul>
        </li>
        <!--end::Color Mode Toggle-->

        <!--begin::User Menu Dropdown-->
        <li class="nav-item dropdown user-menu">
          <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle fs-5"></i>
            <span class="d-none d-md-inline"><?= e(auth_sesi()['email'] ?? 'Pengguna') ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a href="<?= BASE_URL ?>index.php?hal=login&aksi=logout" class="dropdown-item">Sign out</a></li>
          </ul>
        </li>
        <!--end::User Menu Dropdown-->
      </ul>
      <!--end::End Navbar Links-->
    </div>
  </nav>
  <!--end::Header-->
