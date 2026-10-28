<?php
// template/sidebar.php
$hal = $_GET['hal'] ?? 'dashboard';
function aktif(string $nama, string $hal): string {
    return $nama === $hal ? 'active' : '';
}
?>
<!--begin::Sidebar-->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <div class="sidebar-brand">
    <a href="<?= BASE_URL ?>index.php" class="brand-link">
      <span class="brand-text fw-light"><?= APP_NAME ?></span>
    </a>
  </div>
  <div class="sidebar-wrapper">
    <nav class="mt-2">
      <ul class="nav sidebar-menu flex-column" role="navigation"
          data-lte-toggle="treeview" data-accordion="false">

        <li class="nav-item">
          <a href="<?= BASE_URL ?>index.php?hal=dashboard"
             class="nav-link <?= aktif('dashboard', $hal) ?>">
            <i class="nav-icon bi bi-speedometer"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <li class="nav-header">DATA</li>
        <li class="nav-item">
          <a href="<?= BASE_URL ?>index.php?hal=buku"
             class="nav-link <?= aktif('buku', $hal) ?>">
            <i class="nav-icon bi bi-book"></i>
            <p>Data Buku</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="<?= BASE_URL ?>index.php?hal=anggota"
             class="nav-link <?= aktif('anggota', $hal) ?>">
            <i class="nav-icon bi bi-people"></i>
            <p>Anggota</p>
          </a>
        </li>

        <li class="nav-header">LAPORAN</li>
        <li class="nav-item">
          <a href="<?= BASE_URL ?>index.php?hal=buku&aksi=print" target="_blank" class="nav-link">
            <i class="nav-icon bi bi-printer"></i>
            <p>Laporan</p>
          </a>
        </li>
      </ul>
    </nav>
  </div>
</aside>
<!--end::Sidebar-->
