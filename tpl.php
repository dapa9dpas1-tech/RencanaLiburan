<?php
require_once __DIR__ . '/func.php';
must_login();

$slug = $_GET['slug'] ?? '';
$all  = require __DIR__ . '/template.php';

if (!isset($all[$slug])) {
    flash('e', 'Template tidak ditemukan.');
    header('Location: index.php'); exit;
}

$t = $all[$slug];
$totalBudget = $t['lama'] * $t['budget_per_hari'];
$totalItem   = 0;
foreach ($t['hari'] as $items) $totalItem += count($items);

$title = $t['nama'] . ' — Template';
include __DIR__ . '/head.php';
?>

<a href="index.php" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-sky-600 font-semibold mb-4">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
  Kembali
</a>

<section class="relative rounded-2xl sm:rounded-3xl overflow-hidden mb-5 shadow-lg">
  <img src="<?= e($t['foto']) ?>" class="w-full h-48 sm:h-72 object-cover" alt="">
  <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
  <div class="absolute bottom-0 p-4 sm:p-8 text-white">
    <span class="inline-block px-3 py-1 rounded-full bg-amber-400 text-amber-900 text-xs font-bold">
      ⭐ <?= $t['rating'] ?> · Template Siap Pakai
    </span>
    <h1 class="text-2xl sm:text-4xl font-extrabold mt-2"><?= e($t['nama']) ?></h1>
    <p class="text-white/80 text-xs sm:text-sm mt-1 flex items-center gap-1">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2"/></svg>
      <?= e($t['lokasi']) ?>
    </p>
  </div>
</section>

<div class="grid grid-cols-3 gap-3 mb-5">
  <div class="bg-white rounded-2xl border border-slate-100 p-3 sm:p-4 text-center">
    <div class="text-[11px] text-slate-400 font-semibold uppercase">Lama</div>
    <div class="text-lg sm:text-2xl font-extrabold text-sky-600 mt-1"><?= $t['lama'] ?> Hari</div>
  </div>
  <div class="bg-white rounded-2xl border border-slate-100 p-3 sm:p-4 text-center">
    <div class="text-[11px] text-slate-400 font-semibold uppercase">Per Hari</div>
    <div class="text-sm sm:text-lg font-extrabold text-emerald-600 mt-1.5"><?= rp($t['budget_per_hari']) ?></div>
  </div>
  <div class="bg-white rounded-2xl border border-slate-100 p-3 sm:p-4 text-center">
    <div class="text-[11px] text-slate-400 font-semibold uppercase">Total</div>
    <div class="text-sm sm:text-lg font-extrabold text-amber-600 mt-1.5"><?= rp($totalBudget) ?></div>
  </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 p-4 sm:p-6 mb-5">
  <h2 class="font-extrabold text-base sm:text-lg text-slate-800 mb-2">Tentang <?= e($t['nama']) ?></h2>
  <p class="text-sm text-slate-600 leading-relaxed"><?= e($t['deskripsi']) ?></p>
  <p class="text-xs text-slate-400 mt-3">
    Total <?= $totalItem ?> kegiatan siap ditambahkan otomatis ke akunmu.
  </p>
</div>

<div class="bg-white rounded-2xl border border-slate-100 p-4 sm:p-6 mb-5">
  <h2 class="font-extrabold text-base sm:text-lg text-slate-800 mb-4">Itinerary (<?= $t['lama'] ?> Hari)</h2>

  <div class="space-y-4">
    <?php foreach ($t['hari'] as $hari => $items): ?>
      <div>
        <div class="flex items-center gap-2.5 mb-2">
          <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-500 to-indigo-500 text-white grid place-items-center font-extrabold text-xs shrink-0">
            H<?= $hari ?>
          </div>
          <h3 class="font-extrabold text-sm text-slate-800">Hari ke-<?= $hari ?></h3>
        </div>
        <div class="relative border-l-2 border-sky-100 ml-4 pl-4 space-y-2.5">
          <?php foreach ($items as $r): ?>
            <div class="relative bg-slate-50 rounded-xl p-3">
              <span class="absolute -left-[22px] top-4 w-2.5 h-2.5 rounded-full bg-sky-500 ring-4 ring-white"></span>
              <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                <span class="px-2 py-0.5 rounded-md bg-white border border-sky-100 text-sky-600 text-[10px] font-bold">
                  🕐 <?= e($r['jam']) ?>
                </span>
                <span class="px-2 py-0.5 rounded-md bg-white border border-indigo-100 text-indigo-600 text-[10px] font-bold">
                  📍 <?= e($r['lokasi']) ?>
                </span>
              </div>
              <p class="text-xs sm:text-sm text-slate-700 leading-relaxed"><?= e($r['kegiatan']) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<form method="post" action="tpl_use.php" class="bg-gradient-to-br from-sky-500 to-indigo-500 rounded-2xl p-4 sm:p-6 text-white mb-6">
  <input type="hidden" name="slug" value="<?= e($slug) ?>">
  <h3 class="font-extrabold text-base sm:text-lg">Gunakan Template Ini?</h3>
  <p class="text-xs sm:text-sm text-white/85 mt-1">
    Sistem akan otomatis membuat destinasi <b><?= e($t['nama']) ?></b> lengkap dengan
    <?= $totalItem ?> kegiatan di akunmu. Kamu bisa edit/hapus setelahnya.
  </p>
  <button type="submit"
    class="mt-4 w-full sm:w-auto px-6 py-3 rounded-xl bg-white text-sky-600 font-extrabold text-sm hover:bg-sky-50 active:scale-95 transition shadow-lg">
    ✓ Ya, Pakai Template Ini
  </button>
</form>

<?php include __DIR__ . '/foot.php'; ?>