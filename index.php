<?php
require_once __DIR__ . '/func.php';
must_login();
$uid = (int)$_SESSION['uid'];

// Statistik
$s = $conn->prepare("SELECT COUNT(*) t, COALESCE(SUM(lama),0) h FROM destinasi WHERE user_id=?");
$s->bind_param('i', $uid); $s->execute();
$stat = $s->get_result()->fetch_assoc(); $s->close();

$s = $conn->prepare("SELECT COUNT(*) c FROM rencana r JOIN destinasi d ON d.id=r.destinasi_id WHERE d.user_id=?");
$s->bind_param('i', $uid); $s->execute();
$totalKegiatan = (int)$s->get_result()->fetch_assoc()['c']; $s->close();

$all = require __DIR__ . '/template.php';

// Preset destinasi (dengan kategori untuk filter)
$preset = [
  ['jepang',      'Jepang',        'Tokyo - Osaka',     4.9, 'Asia',      'https://images.unsplash.com/photo-1542051841857-5f90071e7989?auto=format&fit=crop&w=600&q=80'],
  ['korea',       'Korea Selatan', 'Seoul - Busan',     4.8, 'Asia',      'https://images.unsplash.com/photo-1517154421773-0529f29ea451?auto=format&fit=crop&w=600&q=80'],
  ['thailand',    'Thailand',      'Bangkok - Phuket',  4.8, 'Asia',      'https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=600&q=80'],
  ['bali',        'Bali',          'Indonesia',         4.7, 'Indonesia', 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=600&q=80'],
  ['labuan-bajo', 'Labuan Bajo',   'Indonesia',         4.7, 'Indonesia', 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=600&q=80'],
  ['yogyakarta',  'Yogyakarta',    'Indonesia',         4.7, 'Indonesia', 'https://images.unsplash.com/photo-1596422846543-75c6fc197f07?auto=format&fit=crop&w=600&q=80'],
  ['raja-ampat',  'Raja Ampat',    'Indonesia',         4.9, 'Indonesia', 'https://images.unsplash.com/photo-1516690561799-46d8f74f9abf?auto=format&fit=crop&w=600&q=80'],
  ['singapura',   'Singapura',     'Singapura',         4.6, 'Asia',      'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=600&q=80'],
  ['vietnam',     'Vietnam',       'Vietnam',           4.6, 'Asia',      'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=600&q=80'],
  ['maldives',    'Maldives',      'Maldives',          4.8, 'Asia',      'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=600&q=80'],
];

$title = 'Home';
include __DIR__ . '/head.php';
?>

<!-- HERO -->
<section class="relative w-full rounded-3xl overflow-hidden mb-5 shadow-lg shadow-sky-100">
  <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1600&q=80"
       class="absolute inset-0 w-full h-full object-cover" alt="">
  <div class="absolute inset-0 bg-gradient-to-r from-sky-900/40 via-sky-700/20 to-transparent"></div>

  <div class="relative p-6 sm:p-10 lg:p-12 text-white min-h-[260px] sm:min-h-[300px] flex flex-col justify-center">
    <!-- Badge -->
    <div class="inline-flex items-center gap-2 bg-white/95 backdrop-blur text-slate-700 rounded-full px-3 py-1.5 text-xs font-bold w-fit shadow-md">
      <span class="w-5 h-5 rounded-full bg-sky-500 grid place-items-center">
        <svg viewBox="0 0 24 24" fill="white" class="w-3 h-3"><path d="M2 12l19-7-7 19-3-8-9-4z"/></svg>
      </span>
      Jelajahi • Rencanakan • Liburan!
    </div>

    <h1 class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight text-white drop-shadow-lg max-w-xl">
      Destinasi Liburan
    </h1>
    <p class="mt-1 sm:mt-2 text-sm sm:text-base text-white/90 max-w-lg drop-shadow">
      Pilih template destinasi atau kelola rencanamu sendiri.
    </p>

    <!-- Search di dalam hero -->
    <form method="get" action="dest.php" class="mt-5 max-w-2xl">
      <div class="bg-white rounded-full p-1.5 pl-4 flex items-center gap-2 shadow-xl">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5 text-sky-500 shrink-0"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" name="q" placeholder="Cari destinasi atau tempat menarik..."
               class="flex-1 min-w-0 py-2.5 text-sm text-slate-700 outline-none placeholder:text-slate-400 bg-transparent">
        <button class="shrink-0 px-6 py-2.5 rounded-full bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold flex items-center gap-2 transition">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          Cari
        </button>
      </div>
    </form>
  </div>

  <!-- Sticker kanan -->
  <div class="hidden lg:block absolute right-8 top-12 text-right text-white drop-shadow-lg rotate-[-4deg]">
    <p class="text-2xl font-extrabold italic leading-tight">Jelajahi<br>Dunia, Ciptakan<br>Cerita!</p>
    <svg viewBox="0 0 100 40" class="w-20 h-8 mt-2 ml-auto opacity-70">
      <path d="M5 30 Q30 5 60 15 L70 8 L68 20 L80 22" stroke="white" stroke-width="1.5" fill="none" stroke-dasharray="3 3"/>
      <path d="M78 20 L85 24 L80 27 Z" fill="white"/>
    </svg>
  </div>
</section>

<!-- STATISTIK + QUOTE -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
  <div class="bg-white rounded-2xl border border-slate-100 p-5 flex items-center justify-between shadow-sm">
    <div>
      <div class="flex items-center gap-2 text-xs text-slate-400 font-bold uppercase tracking-wide">
        <span class="w-9 h-9 rounded-full bg-sky-500 text-white grid place-items-center">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2"/></svg>
        </span>
        <span class="text-slate-700">Destinasi</span>
      </div>
      <div class="text-3xl font-extrabold text-slate-800 mt-3"><?= (int)$stat['t'] ?></div>
      <p class="text-[11px] text-slate-400 mt-0.5">Pilihan destinasi tersedia</p>
    </div>
    <a href="dest.php" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-sky-100 grid place-items-center text-slate-400 hover:text-sky-500 transition">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
    </a>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 p-5 flex items-center justify-between shadow-sm">
    <div>
      <div class="flex items-center gap-2 text-xs text-slate-400 font-bold uppercase tracking-wide">
        <span class="w-9 h-9 rounded-full bg-emerald-500 text-white grid place-items-center">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        </span>
        <span class="text-slate-700">Total Hari</span>
      </div>
      <div class="text-3xl font-extrabold text-slate-800 mt-3"><?= (int)$stat['h'] ?></div>
      <p class="text-[11px] text-slate-400 mt-0.5">Rencana perjalanan</p>
    </div>
    <a href="dest.php" class="w-8 h-8 rounded-full bg-emerald-100 grid place-items-center text-emerald-500">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
    </a>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 p-5 flex items-center justify-between shadow-sm">
    <div>
      <div class="flex items-center gap-2 text-xs text-slate-400 font-bold uppercase tracking-wide">
        <span class="w-9 h-9 rounded-full bg-rose-500 text-white grid place-items-center">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2"/></svg>
        </span>
        <span class="text-slate-700">Kegiatan</span>
      </div>
      <div class="text-3xl font-extrabold text-slate-800 mt-3"><?= $totalKegiatan ?></div>
      <p class="text-[11px] text-slate-400 mt-0.5">Aktivitas direncanakan</p>
    </div>
    <a href="rencana.php" class="w-8 h-8 rounded-full bg-rose-100 grid place-items-center text-rose-500">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
    </a>
  </div>

  <!-- Kartu quote dengan ilustrasi -->
  <div class="relative rounded-2xl overflow-hidden border border-sky-100 shadow-sm">
    <img src="https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=600&q=80"
         class="absolute inset-0 w-full h-full object-cover" alt="">
    <div class="absolute inset-0 bg-gradient-to-r from-white via-white/70 to-transparent"></div>
    <div class="relative p-5">
      <p class="text-sm italic font-bold text-sky-700 leading-snug">
        "Setiap perjalanan<br>adalah investasi<br>untuk diri sendiri"
      </p>
      <svg viewBox="0 0 100 30" class="w-20 h-6 mt-2">
        <path d="M5 20 Q30 5 60 12 L70 6 L68 15" stroke="#0284c7" stroke-width="1.5" fill="none" stroke-dasharray="3 3"/>
        <path d="M68 15 L74 18 L70 20 Z" fill="#0284c7"/>
      </svg>
      <div class="text-rose-400 text-xs mt-1">♥</div>
    </div>
  </div>
</div>

<!-- MAU TRAVEL KEMANA -->
<div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-5">

  <div class="min-w-0">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
      <div>
        <h2 class="font-extrabold text-xl lg:text-2xl text-slate-800">Mau Travel Kemana?</h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Klik salah satu, itinerary otomatis dibuat.</p>
      </div>

      <!-- Filter kategori -->
      <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
        <button class="filter-btn active shrink-0 px-4 py-2 rounded-full text-xs font-bold border border-slate-200 bg-white text-slate-600 hover:border-sky-400 transition" data-filter="all">🌐 Semua</button>
        <button class="filter-btn shrink-0 px-4 py-2 rounded-full text-xs font-bold border border-slate-200 bg-white text-slate-600 hover:border-sky-400 transition" data-filter="Indonesia">🇮🇩 Indonesia</button>
        <button class="filter-btn shrink-0 px-4 py-2 rounded-full text-xs font-bold border border-slate-200 bg-white text-slate-600 hover:border-sky-400 transition" data-filter="Asia">🌏 Asia</button>
        <button class="filter-btn shrink-0 px-4 py-2 rounded-full text-xs font-bold border border-slate-200 bg-white text-slate-600 hover:border-sky-400 transition" data-filter="Eropa">🏰 Eropa</button>
        <button class="filter-btn shrink-0 px-4 py-2 rounded-full text-xs font-bold border border-slate-200 bg-white text-slate-600 hover:border-sky-400 transition" data-filter="Lainnya">••• Lainnya</button>
      </div>
    </div>

    <div id="gridPreset" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-4 gap-4">
      <?php foreach ($preset as $pr):
        $slug = $pr[0]; $tData = $all[$slug] ?? null;
        $total = $tData ? $tData['lama'] * $tData['budget_per_hari'] : 0;
      ?>
        <a href="tpl.php?slug=<?= urlencode($slug) ?>" data-category="<?= e($pr[4]) ?>"
           class="preset-card group bg-white rounded-2xl border border-slate-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition flex flex-col">
          <div class="relative h-36 sm:h-40 overflow-hidden bg-slate-100">
            <img src="<?= $pr[5] ?>"
                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=600&q=80';"
                 class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
                 alt="<?= e($pr[1]) ?>">
            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent"></div>
            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-white/95 backdrop-blur text-[10px] font-bold text-amber-500 shadow">⭐ <?= $pr[3] ?></span>
            <div class="absolute bottom-3 left-3 right-3 text-white">
              <h3 class="font-extrabold text-base leading-tight"><?= e($pr[1]) ?></h3>
              <p class="text-[11px] text-white/85 flex items-center gap-1 mt-0.5">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2"/></svg>
                <?= e($pr[2]) ?>
              </p>
            </div>
          </div>
          <div class="p-3 flex-1 flex flex-col">
            <?php if ($tData): ?>
              <div class="flex items-center justify-between text-xs text-slate-500 mb-1.5">
                <span>⏱ <?= $tData['lama'] ?> hari</span>
                <span class="font-bold text-sky-600"><?= rp($total) ?></span>
              </div>
              <p class="text-[11px] text-slate-500 leading-relaxed line-clamp-2 flex-1"><?= e($tData['deskripsi']) ?></p>
            <?php endif; ?>
            <div class="mt-2 flex items-center gap-1.5 text-sky-600 text-xs font-bold group-hover:gap-2.5 transition-all">
              Lihat & Pakai
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-3.5 h-3.5"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- SIDEBAR KANAN -->
  <aside class="space-y-4">
    <div class="bg-white rounded-3xl border border-slate-100 p-5 relative overflow-hidden">
      <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full bg-sky-50"></div>
      <div class="relative">
        <div class="flex items-center gap-2">
          <h3 class="font-extrabold text-lg text-slate-800 leading-tight">Kenapa<br>RencanaLiburan?</h3>
          <span class="text-3xl">🌍</span>
        </div>
        <ul class="mt-4 space-y-3 text-sm">
          <li class="flex items-start gap-2.5">
            <span class="w-5 h-5 rounded-full bg-sky-100 text-sky-600 grid place-items-center shrink-0 mt-0.5">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="w-3 h-3"><path d="M20 6 9 17l-5-5"/></svg>
            </span>
            <span class="text-slate-600">Template itinerary lengkap</span>
          </li>
          <li class="flex items-start gap-2.5">
            <span class="w-5 h-5 rounded-full bg-sky-100 text-sky-600 grid place-items-center shrink-0 mt-0.5">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="w-3 h-3"><path d="M20 6 9 17l-5-5"/></svg>
            </span>
            <span class="text-slate-600">Mudah dikustomisasi</span>
          </li>
          <li class="flex items-start gap-2.5">
            <span class="w-5 h-5 rounded-full bg-sky-100 text-sky-600 grid place-items-center shrink-0 mt-0.5">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="w-3 h-3"><path d="M20 6 9 17l-5-5"/></svg>
            </span>
            <span class="text-slate-600">Banyak pilihan destinasi</span>
          </li>
          <li class="flex items-start gap-2.5">
            <span class="w-5 h-5 rounded-full bg-sky-100 text-sky-600 grid place-items-center shrink-0 mt-0.5">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="w-3 h-3"><path d="M20 6 9 17l-5-5"/></svg>
            </span>
            <span class="text-slate-600">Akses kapan saja & dimana saja</span>
          </li>
        </ul>
        <a href="dest.php" class="mt-5 w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-lg shadow-sky-200 transition">
          Buat Rencana Sekarang
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </a>
      </div>
    </div>

    <!-- Kartu ilustrasi traveler -->
    <div class="relative rounded-3xl overflow-hidden border border-slate-100">
      <img src="https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=600&q=80"
           class="w-full h-48 object-cover" alt="">
      <div class="absolute inset-0 bg-gradient-to-t from-sky-900/70 via-transparent to-transparent"></div>
      <div class="absolute bottom-4 right-4 text-right text-white">
        <p class="text-lg font-extrabold italic leading-tight">Liburan<br>Jadi Lebih<br>Mudah!</p>
        <div class="text-rose-300 text-sm mt-1">♥</div>
      </div>
    </div>
  </aside>
</div>

<!-- Footer quote -->
<div class="mt-8 relative rounded-3xl bg-gradient-to-br from-sky-100 via-white to-indigo-100 border border-sky-100 p-6 text-center overflow-hidden">
  <div class="absolute left-4 top-1/2 -translate-y-1/2 text-4xl hidden sm:block">🧳</div>
  <div class="absolute right-4 top-1/2 -translate-y-1/2 text-4xl hidden sm:block">✈️</div>
  <p class="font-extrabold text-sky-700 italic text-sm lg:text-base">"Trip Never Ends"</p>
  <p class="text-xs lg:text-sm text-sky-600/70 mt-1">Setiap perjalanan adalah cerita baru.</p>
</div>

<!-- Script Filter Kategori -->
<script>
document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const filter = btn.dataset.filter;
    document.querySelectorAll('.preset-card').forEach(card => {
      if (filter === 'all' || card.dataset.category === filter) {
        card.style.display = '';
      } else {
        card.style.display = 'none';
      }
    });
  });
});
</script>

<?php include __DIR__ . '/foot.php'; ?>