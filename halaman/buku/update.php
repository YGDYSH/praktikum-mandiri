<?php
$judulHalaman = 'Edit Buku';
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM buku WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$buku = $stmt->get_result()->fetch_assoc();
if (!$buku) redirect('buku');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare('UPDATE buku SET judul=?, penulis=?, tahun=? WHERE id=?');
    $stmt->bind_param('ssii', $_POST['judul'], $_POST['penulis'], $_POST['tahun'], $id);
    $stmt->execute();
    redirect('buku');
}
?>
<main class="app-main">
  <div class="app-content-header"><div class="container-fluid">
    <h1 class="mb-0 fs-3">Edit Buku</h1>
  </div></div>
  <div class="app-content"><div class="container-fluid">
    <div class="card"><form method="post">
      <div class="card-body">
        <div class="mb-3"><label class="form-label">Judul</label>
          <input name="judul" class="form-control" value="<?= e($buku['judul']) ?>" required></div>
        <div class="mb-3"><label class="form-label">Penulis</label>
          <input name="penulis" class="form-control" value="<?= e($buku['penulis']) ?>" required></div>
        <div class="mb-3"><label class="form-label">Tahun</label>
          <input name="tahun" type="number" class="form-control" value="<?= e((string)$buku['tahun']) ?>" required></div>
      </div>
      <div class="card-footer">
        <button class="btn btn-primary">Simpan Perubahan</button>
        <a href="<?= BASE_URL ?>index.php?hal=buku" class="btn btn-link">Batal</a>
      </div>
    </form></div>
  </div></div>
</main>
