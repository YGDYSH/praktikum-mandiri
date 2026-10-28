<?php
$judulHalaman = 'Edit Anggota';
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM anggota WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$anggota = $stmt->get_result()->fetch_assoc();
if (!$anggota) redirect('anggota');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare('UPDATE anggota SET nama=?, email=? WHERE id=?');
    $stmt->bind_param('ssi', $_POST['nama'], $_POST['email'], $id);
    $stmt->execute();
    redirect('anggota');
}
?>
<main class="app-main">
  <div class="app-content-header"><div class="container-fluid">
    <h1 class="mb-0 fs-3">Edit Anggota</h1>
  </div></div>
  <div class="app-content"><div class="container-fluid">
    <div class="card"><form method="post">
      <div class="card-body">
        <div class="mb-3"><label class="form-label">Nama</label>
          <input name="nama" class="form-control" value="<?= e($anggota['nama']) ?>" required></div>
        <div class="mb-3"><label class="form-label">Email</label>
          <input name="email" type="email" class="form-control" value="<?= e($anggota['email']) ?>" required></div>
      </div>
      <div class="card-footer">
        <button class="btn btn-primary">Simpan Perubahan</button>
        <a href="<?= BASE_URL ?>index.php?hal=anggota" class="btn btn-link">Batal</a>
      </div>
    </form></div>
  </div></div>
</main>
