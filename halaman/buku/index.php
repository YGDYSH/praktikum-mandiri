<?php
$judulHalaman = 'Data Buku';

if (isset($_GET['hapus'])) {   // di praktik nyata, hapus lewat POST + token CSRF
    $stmt = $db->prepare('DELETE FROM buku WHERE id = ?');
    $idHapus = (int)$_GET['hapus'];
    $stmt->bind_param('i', $idHapus);
    $stmt->execute();
    redirect('buku');
}
$hasil = $db->query('SELECT * FROM buku ORDER BY id DESC');
?>
<main class="app-main">
  <div class="app-content-header"><div class="container-fluid">
    <h1 class="mb-0 fs-3">Data Buku</h1>
  </div></div>
  <div class="app-content"><div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <a href="<?= BASE_URL ?>index.php?hal=buku&aksi=create" class="btn btn-primary btn-sm">+ Tambah</a>
        <a href="<?= BASE_URL ?>index.php?hal=buku&aksi=print" class="btn btn-secondary btn-sm" target="_blank">Cetak</a>
      </div>
      <div class="card-body p-0">
        <table class="table table-striped mb-0">
          <thead><tr><th>No</th><th>Judul</th><th>Penulis</th><th>Tahun</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php $no = 1; while ($b = $hasil->fetch_assoc()): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= e($b['judul']) ?></td>
              <td><?= e($b['penulis']) ?></td>
              <td><?= e($b['tahun']) ?></td>
              <td>
                <a href="<?= BASE_URL ?>index.php?hal=buku&aksi=update&id=<?= (int)$b['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="<?= BASE_URL ?>index.php?hal=buku&hapus=<?= (int)$b['id'] ?>"
                   onclick="return confirm('Hapus buku ini?')" class="btn btn-danger btn-sm">Hapus</a>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div></div>
</main>
