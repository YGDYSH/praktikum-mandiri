<?php
// halaman/dashboard/index.php
$judulHalaman = 'Dashboard';
$totalBuku = $db->query('SELECT COUNT(*) FROM buku')->fetch_row()[0];
$terbaru   = $db->query('SELECT judul, penulis, tahun FROM buku ORDER BY id DESC LIMIT 5');
?>
<main class="app-main">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6"><h1 class="mb-0 fs-3">Dashboard</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
  <div class="app-content">
    <div class="container-fluid">
      <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Ringkasan</h3></div>
        <div class="card-body">Total buku: <strong><?= (int)$totalBuku ?></strong></div>
      </div>

      <!-- Latihan 7: kartu buku terbaru -->
      <div class="card">
        <div class="card-header"><h3 class="card-title">Buku Terbaru</h3></div>
        <ul class="list-group list-group-flush">
          <?php while ($b = $terbaru->fetch_assoc()): ?>
            <li class="list-group-item">
              <?= e($b['judul']) ?> <small class="text-secondary">— <?= e($b['penulis']) ?> (<?= e($b['tahun']) ?>)</small>
            </li>
          <?php endwhile; ?>
        </ul>
      </div>
    </div>
  </div>
</main>
