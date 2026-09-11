<?php
require_once __DIR__ . '/func.php';
must_login();
$uid = (int)$_SESSION['uid'];

$q = trim($_GET['q'] ?? '');
$kategori = $_GET['kategori'] ?? '';
$lokasi = $_GET['lokasi'] ?? '';

$all = require __DIR__ . '/template.php';

// ============================================
// DATA PRESET DESTINASI
// Index: 0=slug, 1=nama, 2=kota, 3=rating, 4=kategori, 5=lokasi,
//        6=deskripsi, 7=lama, 8=budget, 9=badge, 10=foto
// ============================================
$preset = [
  ['bali',        'Bali',         'Indonesia',     4.8, 'Pantai', 'Bali & Nusa Tenggara', 'Pulau Dewata dengan pantai eksotis, pura ikonik, dan budaya yang kental.', 4, 3500000, 'Populer',     'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=600&q=80'],
  ['raja-ampat',  'Raja Ampat',   'Indonesia',     4.7, 'Pantai', 'Papua',                'Surga bawah laut dengan keindahan alam yang tiada duanya.',               5, 7500000, 'Rekomendasi', 'https://images.unsplash.com/photo-1516690561799-46d8f74f9abf?auto=format&fit=crop&w=600&q=80'],
  ['bromo',       'Bromo',        'Jawa Timur',    4.9, 'Gunung', 'Jawa',                 'Nikmati sunrise yang memukau di salah satu gunung paling ikonik Indonesia.', 3, 2800000, 'Favorit',     'https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&w=600&q=80'],
  ['jakarta',     'Jakarta',      'DKI Jakarta',   4.6, 'Kota',   'Jawa',                 'Kota metropolitan dengan berbagai destinasi modern dan bersejarah.',       2, 1500000, 'Populer',     'https://images.unsplash.com/photo-1555899434-94d1368aa7af?auto=format&fit=crop&w=600&q=80'],
  ['yogyakarta',  'Yogyakarta',   'DI Yogyakarta', 4.8, 'Budaya', 'Jawa',                 'Seni, budaya, dan keindahan alam dalam satu perjalanan.',                  3, 2100000, 'Rekomendasi', 'https://images.unsplash.com/photo-1596422846543-75c6fc197f07?auto=format&fit=crop&w=600&q=80'],
  ['danau-toba',  'Danau Toba',   'Sumatera Utara',4.5, 'Alam',   'Sumatera',             'Danau vulkanik terbesar di dunia dengan pemandangan yang menakjubkan.',    4, 3800000, 'Hidden Gem',  'https://images.unsplash.com/photo-1598895921514-1e0d5d8bf8ef?auto=format&fit=crop&w=600&q=80'],
];

// Kategori statis
$kategoriList = [
  ['Pantai',    '🏖️', 432],
  ['Gunung',    '⛰️', 210],
  ['Kota',      '🏙️', 198],
  ['Budaya',    '🏛️', 156],
  ['Alam',      '🌿', 143],
  ['Kuliner',   '🍜', 98],
  ['Adventure', '⛺', 87],
];

$lokasiList = [
  ['Jawa',                312],
  ['Bali & Nusa Tenggara',198],
  ['Sumatera',            176],
  ['Kalimantan',          94],
  ['Sulawesi',            87],
  ['Papua',               64],
];

// Filter preset
if ($kategori !== '' || $lokasi !== '') {
    $preset = array_filter($preset, function($p) use ($kategori, $lokasi) {
        if ($kategori !== '' && $p[4] !== $kategori) return false;
        if ($lokasi !== '' && $p[5] !== $lokasi) return false;
        return true;
    });
    $preset = array_values($preset);
}

// Populer
$populer = [
  ['bali',       'Bali',       '4.8', 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=200&q=80'],
  ['raja-ampat', 'Raja Ampat', '4.7', 'https://images.unsplash.com/photo-1516690561799-46d8f74f9abf?auto=format&fit=crop&w=200&q=80'],
  ['bromo',      'Bromo',      '4.9', 'https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&w=200&q=80'],
  ['yogyakarta', 'Yogyakarta', '4.6', 'https://images.unsplash.com/photo-1596422846543-75c6fc197f07?auto=format&fit=crop&w=200&q=80'],
  ['labuan-bajo','Labuan Bajo','4.7', 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=200&q=80'],
];

$title = 'Destinasi';
include __DIR__ . '/head.php';
?>

<!-- HERO + FEATURED -->
<div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-5 mb-5">

  <!-- HERO KIRI -->
  <section class="relative rounded-3xl overflow-hidden shadow-lg shadow-sky-100">
    <img src="https://images.unsplash.com/photo-1518509562904-e7ef99cdcc86?auto=format&fit=crop&w=1600&q=80"
         class="absolute inset-0 w-full h-full object-cover" alt="">
    <div class="absolute inset-0 bg-gradient-to-r from-sky-700/85 via-sky-600/60 to-sky-400/30"></div>

    <div class="relative p-6 sm:p-10 text-white min-h-[240px] sm:min-h-[280px] flex flex-col justify-center">
      <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur rounded-full px-3 py-1.5 text-xs font-bold w-fit border border-white/30">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2"/></svg>
        Jelajahi Destinasi Impianmu
      </div>
      <h1 class="mt-3 text-2xl sm:text-4xl font-extrabold leading-tight max-w-lg drop-shadow">Temukan Destinasi Terbaik<br>di Seluruh Indonesia</h1>
      <p class="mt-2 text-xs sm:text-sm text-white/90 max-w-md">Dari pantai eksotis hingga kota bersejarah, semua ada di sini. Yuk, mulai petualanganmu!</p>
    </div>

    <div class="hidden lg:block absolute right-10 top-1/2 -translate-y-1/2 text-right text-white pointer-events-none">
      <p class="text-xl font-extrabold italic leading-tight drop-shadow">Dunia luas,<br>cerita menanti!</p>
      <div class="text-3xl mt-2">✈️</div>
    </div>
  </section>

  <!-- FEATURED KANAN -->
  <aside class="relative rounded-3xl overflow-hidden shadow-lg shadow-sky-100 min-h-[280px]">
    <img src="https://images.unsplash.com/photo-1596402184320-417e7178b2cd?auto=format&fit=crop&w=600&q=80"
         class="absolute inset-0 w-full h-full object-cover" alt="">
    <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent"></div>

    <span class="absolute top-4 left-4 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-400 text-amber-900 text-[10px] font-bold shadow-lg">
      <svg viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3"><path d="M12 2 15 9l7 .5-5.5 4.5L18 21l-6-4-6 4 1.5-7L2 9.5 9 9z"/></svg>
      Paling Direkomendasikan
    </span>

    <div class="absolute bottom-0 p-5 w-full text-white">
      <h3 class="font-extrabold text-xl">Borobudur</h3>
      <p class="text-xs text-white/85 flex items-center gap-1 mt-1">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2"/></svg>
        Magelang, Jawa Tengah
      </p>

      <div class="mt-3 flex items-center gap-2 flex-wrap">
        <span class="px-2 py-1 rounded-full bg-white/20 backdrop-blur text-[10px] font-bold">⭐ 4.9</span>
        <span class="text-[10px] text-white/80">(12.4k ulasan)</span>
      </div>

      <div class="mt-3 flex items-center gap-3 text-[11px] text-white/85">
        <span class="flex items-center gap-1">📅 3 Hari</span>
        <span class="flex items-center gap-1">👥 2-5 Orang</span>
        <span class="flex items-center gap-1 font-bold text-amber-300">Rp 2.400.000</span>
      </div>

      <p class="text-[11px] text-white/80 mt-2">Situs warisan dunia dengan arsitektur megah dan nilai sejarah yang luar biasa.</p>

      <a href="tpl.php?slug=borobudur" class="mt-3 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold shadow-lg transition">
        Lihat Detail
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-3.5 h-3.5"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
      </a>
    </div>
  </aside>
</div>

<!-- SEARCH BAR -->
<form method="get" class="mb-5">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
    <div class="flex-1 flex items-center gap-2 px-3">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5 text-slate-400 shrink-0"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari destinasi, kota, atau tempat menarik..."
             class="flex-1 min-w-0 py-2.5 text-sm text-slate-700 outline-none placeholder:text-slate-400 bg-transparent">
    </div>
    <div class="flex items-center gap-2">
      <button type="submit" class="px-6 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold flex items-center gap-2 transition">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        Cari
      </button>
      <button type="button" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-bold flex items-center gap-2 transition">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
        Filter
      </button>
    </div>
  </div>
</form>

<!-- MAIN 3-KOLOM -->
<div class="grid grid-cols-1 lg:grid-cols-[220px_1fr] xl:grid-cols-[240px_1fr_300px] gap-5">

  <!-- ============ KIRI: KATEGORI & LOKASI ============ -->
  <aside class="space-y-4">
    <div class="bg-white rounded-3xl border border-slate-100 p-5">
      <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2 mb-4">
        <span class="w-7 h-7 rounded-lg bg-sky-100 text-sky-600 grid place-items-center">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </span>
        Kategori
      </h3>
      <ul class="space-y-1">
        <?php foreach ($kategoriList as $k): ?>
          <li>
            <a href="?kategori=<?= e($k[0]) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-sky-50 hover:text-sky-600 transition <?= $kategori === $k[0] ? 'bg-sky-50 text-sky-600 font-bold' : '' ?>">
              <span class="w-5 h-5 grid place-items-center"><?= $k[1] ?></span>
              <span class="flex-1"><?= e($k[0]) ?></span>
              <span class="text-[10px] text-slate-400 font-bold"><?= $k[2] ?></span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 p-5">
      <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2 mb-4">
        <span class="w-7 h-7 rounded-lg bg-sky-100 text-sky-600 grid place-items-center">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2"/></svg>
        </span>
        Lokasi
      </h3>
      <ul class="space-y-1">
        <?php foreach ($lokasiList as $l): ?>
          <li>
            <a href="?lokasi=<?= urlencode($l[0]) ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-sky-50 hover:text-sky-600 transition <?= $lokasi === $l[0] ? 'bg-sky-50 text-sky-600 font-bold' : '' ?>">
              <span class="w-3.5 h-3.5 rounded border-2 <?= $lokasi === $l[0] ? 'bg-sky-500 border-sky-500' : 'border-slate-300' ?> grid place-items-center">
                <?php if ($lokasi === $l[0]): ?>
                  <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="4" class="w-2.5 h-2.5"><path d="M20 6 9 17l-5-5"/></svg>
                <?php endif; ?>
              </span>
              <span class="flex-1"><?= e($l[0]) ?></span>
              <span class="text-[10px] text-slate-400 font-bold"><?= $l[1] ?></span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </aside>

  <!-- ============ TENGAH: REKOMENDASI ============ -->
  <div class="min-w-0">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="font-extrabold text-lg text-slate-800">Rekomendasi Destinasi</h2>
        <p class="text-xs text-slate-500 mt-0.5">Destinasi populer yang wajib kamu kunjungi.</p>
      </div>
      <a href="dest.php" class="text-xs font-bold text-sky-600 hover:underline whitespace-nowrap">Lihat Semua →</a>
    </div>

    <?php if (!$preset): ?>
      <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-10 text-center">
        <div class="text-4xl mb-2">🔍</div>
        <p class="font-bold text-slate-700">Tidak ada destinasi</p>
        <p class="text-xs text-slate-500 mt-1">Coba filter lain atau reset pencarian.</p>
        <a href="dest.php" class="inline-block mt-3 px-4 py-2 rounded-lg bg-sky-500 text-white text-xs font-bold">Reset</a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($preset as $p):
          $badgeColor = 'bg-white/95 text-sky-700';
          if ($p[9] === 'Favorit')    $badgeColor = 'bg-white/95 text-amber-600';
          if ($p[9] === 'Hidden Gem') $badgeColor = 'bg-white/95 text-emerald-600';
        ?>
          <a href="tpl.php?slug=<?= urlencode($p[0]) ?>" class="group bg-white rounded-2xl border border-slate-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition flex flex-col">
            <div class="relative h-40 overflow-hidden bg-slate-100">
              <img src="<?= $p[10] ?>"
                   onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=600&q=80';"
                   class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
                   alt="<?= e($p[1]) ?>">
              <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
              <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full <?= $badgeColor ?> backdrop-blur text-[10px] font-bold shadow">
                <?= e($p[9]) ?>
              </span>
              <span class="absolute top-3 right-3 px-2 py-1 rounded-full bg-white/95 text-[10px] font-bold text-amber-500 shadow">⭐ <?= $p[3] ?></span>
              <div class="absolute bottom-3 left-3 right-3 text-white">
                <h3 class="font-extrabold text-lg leading-tight"><?= e($p[1]) ?></h3>
                <p class="text-[11px] text-white/85 flex items-center gap-1 mt-0.5">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2"/></svg>
                  <?= e($p[2]) ?>
                </p>
              </div>
            </div>
            <div class="p-3.5 flex-1 flex flex-col">
              <div class="flex items-center gap-2 text-[10px] text-slate-500 mb-2 flex-wrap">
                <span class="flex items-center gap-1">⏱ <?= (int)$p[7] ?> Hari</span>
                <span class="flex items-center gap-1">👥 2-4 Orang</span>
                <span class="ml-auto font-bold text-sky-600">Rp <?= number_format($p[8], 0, ',', '.') ?></span>
              </div>
              <p class="text-[11px] text-slate-500 leading-relaxed line-clamp-2 flex-1"><?= e($p[6]) ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- ============ KANAN: POPULER ============ -->
  <aside class="space-y-4 min-w-0">
    <div class="bg-white rounded-3xl border border-slate-100 p-5">
      <h3 class="font-extrabold text-sm text-slate-800 mb-4">Destinasi Populer</h3>
      <div class="space-y-3">
        <?php foreach ($populer as $i => $p): ?>
          <a href="tpl.php?slug=<?= urlencode($p[0]) ?>" class="flex gap-3 items-center group">
            <span class="w-6 h-6 rounded-full bg-sky-50 text-sky-600 text-[10px] font-bold grid place-items-center shrink-0"><?= $i + 1 ?></span>
            <div class="w-12 h-12 rounded-xl overflow-hidden shrink-0 bg-slate-100">
              <img src="<?= $p[3] ?>"
                   onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=200&q=80';"
                   class="w-full h-full object-cover group-hover:scale-110 transition" alt="<?= e($p[1]) ?>">
            </div>
            <div class="flex-1 min-w-0">
              <h4 class="font-bold text-xs text-slate-800 truncate"><?= e($p[1]) ?></h4>
              <div class="text-[10px] font-bold text-amber-500 mt-0.5">⭐ <?= $p[2] ?></div>
            </div>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-3.5 h-3.5 text-slate-300 group-hover:text-sky-500 transition shrink-0"><path d="m9 6 6 6-6 6"/></svg>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="relative rounded-3xl bg-gradient-to-br from-sky-100 via-sky-50 to-indigo-100 border border-sky-100 p-5 overflow-hidden text-center">
      <div class="absolute top-2 left-2 text-2xl">📸</div>
      <div class="absolute top-2 right-2 text-2xl">✈️</div>
      <p class="text-sm italic font-extrabold text-sky-700 leading-snug mt-4">
        "Temukan tempat baru,<br>ciptakan cerita baru!"
      </p>
    </div>
  </aside>
</div>

<?php include __DIR__ . '/foot.php'; ?>