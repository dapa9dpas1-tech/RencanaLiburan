<?php
require_once __DIR__ . '/func.php';
must_login();
$uid = (int)$_SESSION['uid'];
$id  = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$s = $conn->prepare("SELECT r.*, d.judul jd, d.user_id FROM rencana r JOIN destinasi d ON d.id=r.destinasi_id WHERE r.id=? AND d.user_id=?");
$s->bind_param('ii', $id, $uid); $s->execute();
$r = $s->get_result()->fetch_assoc(); $s->close();
if (!$r) { flash('e','Kegiatan tidak ditemukan.'); header('Location: dest.php'); exit; }

$errs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r['hari']     = (int)($_POST['hari'] ?? 1);
    $r['jam']      = $_POST['jam'] ?? '';
    $r['lokasi']   = trim($_POST['lokasi'] ?? '');
    $r['kegiatan'] = trim($_POST['kegiatan'] ?? '');
    if ($r['hari'] < 1)        $errs[] = 'Hari minimal 1.';
    if ($r['kegiatan'] === '') $errs[] = 'Kegiatan wajib diisi.';

    if (!$errs) {
        $jam = $r['jam'] !== '' ? $r['jam'] : null;
        $s = $conn->prepare("UPDATE rencana SET hari=?,jam=?,lokasi=?,kegiatan=? WHERE id=?");
        $s->bind_param('isssi', $r['hari'], $jam, $r['lokasi'], $r['kegiatan'], $id);
        if ($s->execute()) { flash('s','Kegiatan diperbarui!'); header("Location: d_view.php?id=".$r['destinasi_id']); exit; }
        $errs[] = 'Gagal: ' . $conn->error;
    }
}
$title = 'Edit Kegiatan'; include __DIR__ . '/head.php';
?>
<div class="max-w-2xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-extrabold mb-1">Edit Kegiatan</h1>
  <p class="text-slate-500 text-sm mb-6">Destinasi: <b><?= e($r['jd']) ?></b></p>
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
    <?php if ($errs): ?>
      <div class="mb-5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm px-4 py-3">
        <ul class="list-disc list-inside space-y-1"><?php foreach ($errs as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>
    <form method="post" class="space-y-5">
      <input type="hidden" name="id" value="<?= (int)$id ?>">
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-semibold mb-1">Hari ke-</label>
          <input type="number" name="hari" min="1" value="<?= (int)$r['hari'] ?>" required
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 outline-none"></div>
        <div><label class="block text-sm font-semibold mb-1">Jam</label>
          <input type="time" name="jam" value="<?= $r['jam'] ? date('H:i', strtotime($r['jam'])) : '' ?>"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 outline-none"></div>
      </div>
      <div><label class="block text-sm font-semibold mb-1">Lokasi</label>
        <input type="text" name="lokasi" value="<?= e($r['lokasi']) ?>"
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 outline-none"></div>
      <div><label class="block text-sm font-semibold mb-1">Kegiatan</label>
        <textarea name="kegiatan" rows="4" required
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 outline-none resize-none"><?= e($r['kegiatan']) ?></textarea></div>
      <div class="flex gap-3 pt-2">
        <button class="flex-1 py-3 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-500 text-white font-bold shadow-lg shadow-sky-200">Simpan</button>
        <a href="d_view.php?id=<?= (int)$r['destinasi_id'] ?>" class="px-6 py-3 rounded-xl bg-slate-100 font-bold text-slate-600">Batal</a>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/foot.php'; ?>