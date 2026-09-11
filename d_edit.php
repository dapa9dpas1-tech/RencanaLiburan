<?php
require_once __DIR__ . '/func.php';
must_login();
$uid = (int)$_SESSION['uid'];
$id  = (int)($_GET['id'] ?? 0);
$d   = get_dest($conn, $id, $uid);
if (!$d) { flash('e','Destinasi tidak ditemukan.'); header('Location: dest.php'); exit; }
$errs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d['judul']   = trim($_POST['judul'] ?? '');
    $d['tanggal'] = $_POST['tanggal'] ?? '';
    $d['budget']  = $_POST['budget'] ?? '0';
    $d['lama']    = (int)($_POST['lama'] ?? 1);
    $d['status']  = (int)($_POST['status'] ?? 0) === 1 ? 1 : 0;

    if ($d['judul']==='') $errs[] = 'Judul wajib.';
    if (!$d['tanggal'])   $errs[] = 'Tanggal wajib.';
    if ($d['lama'] < 1)   $errs[] = 'Lama min 1 hari.';

    $nf = null; $eu = null;
    if (!$errs) { $nf = up_foto($_FILES['foto'] ?? [], $eu); if ($eu) $errs[] = $eu; }

    if (!$errs) {
        $foto = $d['foto'];
        if ($nf) { del_foto($d['foto']); $foto = $nf; }
        $b = (float)$d['budget'];
        $s = $conn->prepare("UPDATE destinasi SET foto=?,judul=?,tanggal=?,budget=?,lama=?,status=? WHERE id=? AND user_id=?");
        $s->bind_param('sssdiiii', $foto, $d['judul'], $d['tanggal'], $b, $d['lama'], $d['status'], $id, $uid);
        if ($s->execute()) { flash('s','Destinasi diperbarui!'); header('Location: dest.php'); exit; }
        $errs[] = 'Gagal: ' . $conn->error;
    }
}
$title = 'Edit Destinasi'; include __DIR__ . '/head.php';
?>
<div class="max-w-2xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-extrabold mb-1">Edit Destinasi</h1>
  <p class="text-slate-500 text-sm mb-6">Perbarui detail destinasi.</p>
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
    <?php if ($errs): ?>
      <div class="mb-5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm px-4 py-3">
        <ul class="list-disc list-inside space-y-1"><?php foreach ($errs as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="space-y-5">
      <?php if ($d['foto']): ?>
        <div><label class="block text-sm font-semibold mb-2">Foto Saat Ini</label>
          <img src="uploads/<?= e($d['foto']) ?>" class="w-40 h-28 object-cover rounded-xl border"></div>
      <?php endif; ?>
      <div><label class="block text-sm font-semibold mb-1">Ganti Foto (opsional)</label>
        <input type="file" name="foto" accept="image/*"
          class="w-full text-sm file:mr-4 file:px-4 file:py-2 file:rounded-xl file:border-0 file:bg-sky-50 file:text-sky-600 file:font-bold"></div>
      <div><label class="block text-sm font-semibold mb-1">Judul</label>
        <input type="text" name="judul" value="<?= e($d['judul']) ?>" required
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 outline-none"></div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-semibold mb-1">Tanggal</label>
          <input type="date" name="tanggal" value="<?= e($d['tanggal']) ?>" required
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 outline-none"></div>
        <div><label class="block text-sm font-semibold mb-1">Lama (hari)</label>
          <input type="number" name="lama" min="1" value="<?= (int)$d['lama'] ?>" required
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 outline-none"></div>
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-semibold mb-1">Budget</label>
          <input type="number" name="budget" min="0" step="1000" value="<?= e((string)$d['budget']) ?>" required
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 outline-none"></div>
        <div><label class="block text-sm font-semibold mb-1">Status</label>
          <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 outline-none">
            <option value="0" <?= (int)$d['status']===0?'selected':'' ?>>Belum</option>
            <option value="1" <?= (int)$d['status']===1?'selected':'' ?>>Tercapai</option>
          </select></div>
      </div>
      <div class="flex gap-3 pt-2">
        <button class="flex-1 py-3 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-500 text-white font-bold shadow-lg shadow-sky-200">Simpan</button>
        <a href="dest.php" class="px-6 py-3 rounded-xl bg-slate-100 font-bold text-slate-600">Batal</a>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/foot.php'; ?>