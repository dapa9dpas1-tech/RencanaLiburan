<?php
require_once __DIR__ . '/func.php';
must_login();
$uid = (int)$_SESSION['uid'];

$filter = $_GET['filter'] ?? 'semua';

// ============================================
// HITUNG STATISTIK
// ============================================
$s = $conn->prepare("SELECT 
  COUNT(*) total,
  SUM(CASE WHEN status IN (0,1) THEN 1 ELSE 0 END) akan_datang,
  SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) selesai,
  SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) dibatalkan
  FROM destinasi WHERE user_id=?");
$s->bind_param('i', $uid); $s->execute();
$stat = $s->get_result()->fetch_assoc(); $s->close();

$totalRencana  = (int)($stat['total'] ?? 0);
$totalAkanDatang = (int)($stat['akan_datang'] ?? 0);
$totalSelesai  = (int)($stat['selesai'] ?? 0);
$totalBatal    = (int)($stat['dibatalkan'] ?? 0);

// ============================================
// QUERY LIST RENCANA
// ============================================
$sql = "SELECT * FROM destinasi WHERE user_id=?";
$p = [$uid]; $t = 'i';

if ($filter === 'akan_datang')    { $sql .= " AND status IN (0,1)"; }
elseif ($filter === 'selesai')    { $sql .= " AND status = 2"; }
elseif ($filter === 'dibatalkan') { $sql .= " AND status = 3"; }

$sql .= " ORDER BY created_at DESC";
$s = $conn->prepare($sql); $s->bind_param($t, ...$p); $s->execute();
$list = $s->get_result()->fetch_all(MYSQLI_ASSOC); $s->close();

// ============================================
// RENCANA TERDEKAT (untuk sidebar)
// ============================================
$s = $conn->prepare("SELECT * FROM destinasi WHERE user_id=? AND status IN (0,1) ORDER BY tanggal ASC LIMIT 3");
$s->bind_param('i', $uid); $s->execute();
$terdekat = $s->get_result()->fetch_all(MYSQLI_ASSOC); $s->close();

$statusMap = [
  0 => ['label' => 'Akan Datang', 'color' => 'bg-sky-100 text-sky-700',       'icon' => '⏱'],
  1 => ['label' => 'Akan Datang', 'color' => 'bg-sky-100 text-sky-700',       'icon' => '⏱'],
  2 => ['label' => 'Selesai',     'color' => 'bg-emerald-100 text-emerald-700','icon' => '✓'],
  3 => ['label' => 'Dibatalkan',  'color' => 'bg-rose-100 text-rose-700',     'icon' => '✕'],
];

$title = 'Rencana Saya';
include __DIR__ . '/head.php';
?>

<!-- HERO -->
<section class="relative rounded-3xl overflow-hidden mb-5 shadow-lg shadow-sky-100">
  <img src="https://images.unsplash.com/photo-1493246507139-91e8fad9978e?auto=format&fit=crop&w=1600&q=80"
       class="absolute inset-0 w-full h-full object-cover" alt="">
  <div class="absolute inset-0 bg-gradient-to-r from-sky-50 via-sky-50/80 to-transparent"></div>

  <div class="relative p-6 sm:p-10 lg:p-12 min-h-[220px] sm:min-h-[280px] flex flex-col justify-center">
    <div class="flex items-start gap-4 max-w-2xl">
      <span class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-500 grid place-items-center text-white shadow-lg shadow-sky-200 shrink-0">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-7 h-7">
          <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"/>
        </svg>
      </span>
      <div>
        <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-800 leading-tight">Rencana Saya</h1>
        <p class="text-xs sm:text-sm text-slate-600 mt-2 max-w-md">
          Kelola semua rencana perjalananmu di sini.<br>Ubah impian menjadi rencana nyata.
        </p>
      </div>
    </div>
  </div>

  <div class="hidden lg:block absolute right-10 top-1/2 -translate-y-1/2 text-right select-none pointer-events-none">
    <p class="text-2xl font-extrabold italic text-sky-600/70 leading-tight">Rencana hari ini,<br>petualangan esok hari!</p>
    <div class="text-5xl mt-2">✈️</div>
  </div>
</section>

<!-- FILTER TABS -->
<div class="mb-5 flex items-center gap-2 overflow-x-auto no-scrollbar">
  <?php
  $tabs = [
    ['semua',      'Semua Rencana', $totalRencana],
    ['akan_datang','Akan Datang',   $totalAkanDatang],
    ['selesai',    'Selesai',       $totalSelesai],
    ['dibatalkan', 'Dibatalkan',    $totalBatal],
  ];
  foreach ($tabs as $t):
    $active = $filter === $t[0];
  ?>
    <a href="?filter=<?= $t[0] ?>"
       class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs sm:text-sm font-bold transition
              <?= $active ? 'bg-sky-500 text-white shadow-lg shadow-sky-200' : 'bg-white border border-slate-200 text-slate-600 hover:border-sky-400' ?>">
      <?php if ($t[0] === 'semua'): ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      <?php elseif ($t[0] === 'akan_datang'): ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
      <?php elseif ($t[0] === 'selesai'): ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
      <?php else: ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>
      <?php endif; ?>
      <?= $t[1] ?>
      <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold <?= $active ? 'bg-white/25' : 'bg-slate-100 text-slate-500' ?>"><?= $t[2] ?></span>
    </a>
  <?php endforeach; ?>
</div>

<!-- KONTEN -->
<div class="grid grid-cols-1 xl:grid-cols-[1fr_340px] gap-5 xl:gap-6">

  <!-- KIRI: LIST RENCANA -->
  <div class="min-w-0">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
      <div>
        <h2 class="font-extrabold text-lg text-slate-800">Daftar Rencana Perjalanan</h2>
        <p class="text-xs text-slate-500 mt-0.5"><?= count($list) ?> rencana ditampilkan</p>
      </div>
      <a href="rencana_buat.php"
         class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold shadow-lg shadow-sky-200 transition shrink-0">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M12 5v14M5 12h14"/></svg>
        Buat Rencana Baru
      </a>
    </div>

    <?php if (!$list): ?>
      <div class="bg-white rounded-3xl border border-dashed border-slate-200 p-10 text-center">
        <div class="text-5xl mb-3">🗺️</div>
        <h3 class="font-extrabold text-slate-800">Belum ada rencana</h3>
        <p class="text-sm text-slate-500 mt-1">Mulai dengan membuat rencana perjalanan pertamamu.</p>
        <a href="rencana_buat.php" class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold">+ Buat Rencana Baru</a>
      </div>
    <?php else: ?>
      <div class="space-y-3">
        <?php foreach ($list as $d):
          $st = $statusMap[(int)$d['status']] ?? $statusMap[0];
          $lama = (int)$d['lama'];
          $tglMulai   = date('d M Y', strtotime($d['tanggal']));
          $tglSelesai = date('d M Y', strtotime($d['tanggal'] . ' +' . ($lama - 1) . ' days'));
          $malam = max(0, $lama - 1);
        ?>
          <div class="bg-white rounded-3xl border border-slate-100 p-3 sm:p-4 hover:shadow-lg transition">
            <div class="flex flex-col lg:flex-row gap-4">

              <!-- Thumbnail -->
              <div class="relative w-full lg:w-64 h-40 sm:h-44 lg:h-36 rounded-2xl overflow-hidden shrink-0 bg-slate-100">
                <?php if (!empty($d['foto']) && file_exists(__DIR__ . '/uploads/' . $d['foto'])): ?>
                  <img src="uploads/<?= e($d['foto']) ?>" class="w-full h-full object-cover" alt="<?= e($d['judul']) ?>">
                <?php else: ?>
                  <div class="w-full h-full grid place-items-center text-4xl bg-gradient-to-br from-sky-100 to-indigo-100">🏝️</div>
                <?php endif; ?>
                <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-white/95 backdrop-blur text-[10px] font-bold text-slate-700 shadow">
                  <?= $lama ?> Hari <?= $malam ?> Malam
                </span>
              </div>

              <!-- Info -->
              <div class="flex-1 min-w-0 flex flex-col">
                <div class="flex items-start justify-between gap-2">
                  <h3 class="font-extrabold text-base sm:text-lg text-slate-800 flex items-center gap-1.5">
                    <?= e($d['judul']) ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 text-slate-400"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                  </h3>
                  <button class="w-7 h-7 rounded-lg hover:bg-slate-100 grid place-items-center text-slate-400 shrink-0">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                  </button>
                </div>

                <div class="text-xs text-slate-500 mt-1.5 flex items-center gap-1.5">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 shrink-0"><path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2"/></svg>
                  <span class="truncate"><?= e($d['judul']) ?></span>
                </div>

                <div class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 shrink-0"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                  <span><?= $tglMulai ?> – <?= $tglSelesai ?></span>
                </div>

                <p class="text-xs text-slate-500 mt-2 leading-relaxed line-clamp-2">
                  <?= e($d['deskripsi'] ?? 'Nikmati keindahan dan pengalaman seru selama perjalanan ini.') ?>
                </p>

                <!-- Status & Actions -->
                <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                  <div class="flex items-center gap-3">
                    <div class="flex -space-x-2">
                      <?php $colors = ['from-sky-400 to-sky-600','from-rose-400 to-rose-600','from-amber-400 to-amber-600'];
                      for ($i = 0; $i < min(3, ($lama % 3) + 1); $i++): ?>
                        <span class="w-7 h-7 rounded-full bg-gradient-to-br <?= $colors[$i] ?> text-white text-[9px] font-bold grid place-items-center ring-2 ring-white">
                          <?= chr(65 + $i) ?>
                        </span>
                      <?php endfor; ?>
                    </div>
                    <span class="text-[11px] text-slate-500 flex items-center gap-1">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>
                      <?= ($lama % 3) + 1 ?> orang
                    </span>
                  </div>

                  <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold inline-flex items-center gap-1 <?= $st['color'] ?>">
                      <span><?= $st['icon'] ?></span> <?= $st['label'] ?>
                    </span>
                  </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="mt-3 flex gap-2">
                  <a href="d_view.php?id=<?= (int)$d['id'] ?>"
                     class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold shadow-sm hover:shadow-md transition">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                    Lihat Detail
                  </a>
                  <a href="d_edit.php?id=<?= (int)$d['id'] ?>"
                     class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                    Edit Rencana
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- KANAN: SIDEBAR -->
  <aside class="space-y-4 min-w-0">

    <!-- Total Rencana -->
    <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-sky-500 via-sky-400 to-sky-600 p-5 shadow-lg shadow-sky-200">
      <div class="absolute -top-4 -right-4 w-32 h-32 rounded-full bg-white/10"></div>
      <div class="absolute -bottom-4 -right-8 w-24 h-24 rounded-full bg-white/10"></div>
      <div class="relative">
        <div class="flex items-center gap-2 text-white">
          <span class="text-xs font-bold uppercase tracking-wide">Total Rencana</span>
        </div>
        <div class="text-5xl font-extrabold text-white mt-2"><?= $totalRencana ?></div>
        <p class="text-xs text-white/85 mt-1">Perjalanan</p>
      </div>
    </div>

    <!-- Breakdown Status -->
    <div class="bg-white rounded-3xl border border-slate-100 p-5">
      <div class="grid grid-cols-3 gap-3 text-center">
        <div>
          <div class="w-9 h-9 mx-auto rounded-xl bg-sky-50 text-sky-600 grid place-items-center mb-2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          </div>
          <div class="text-lg font-extrabold text-slate-800"><?= $totalAkanDatang ?></div>
          <div class="text-[10px] text-slate-400 font-bold uppercase">Akan Datang</div>
        </div>
        <div>
          <div class="w-9 h-9 mx-auto rounded-xl bg-emerald-50 text-emerald-600 grid place-items-center mb-2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
          </div>
          <div class="text-lg font-extrabold text-slate-800"><?= $totalSelesai ?></div>
          <div class="text-[10px] text-slate-400 font-bold uppercase">Selesai</div>
        </div>
        <div>
          <div class="w-9 h-9 mx-auto rounded-xl bg-rose-50 text-rose-600 grid place-items-center mb-2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>
          </div>
          <div class="text-lg font-extrabold text-slate-800"><?= $totalBatal ?></div>
          <div class="text-[10px] text-slate-400 font-bold uppercase">Dibatalkan</div>
        </div>
      </div>
    </div>

    <!-- Rencana Terdekat -->
    <div class="bg-white rounded-3xl border border-slate-100 p-5">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
          <span class="w-7 h-7 rounded-lg bg-sky-100 text-sky-600 grid place-items-center">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          </span>
          Rencana Terdekat
        </h3>
        <a href="?filter=akan_datang" class="text-[11px] font-bold text-sky-600 hover:underline">Lihat Semua →</a>
      </div>

      <?php if (!$terdekat): ?>
        <p class="text-xs text-slate-500 text-center py-4">Belum ada rencana akan datang.</p>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($terdekat as $r):
            $selisih = (int)((strtotime($r['tanggal']) - time()) / 86400);
            $label = $selisih > 0 ? $selisih . ' hari lagi' : ($selisih == 0 ? 'Hari ini' : 'Lewat');
          ?>
            <a href="d_view.php?id=<?= (int)$r['id'] ?>" class="flex gap-3 group">
              <div class="w-14 h-14 rounded-xl overflow-hidden shrink-0 bg-slate-100">
                <?php if (!empty($r['foto']) && file_exists(__DIR__ . '/uploads/' . $r['foto'])): ?>
                  <img src="uploads/<?= e($r['foto']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition">
                <?php else: ?>
                  <div class="w-full h-full grid place-items-center text-2xl bg-gradient-to-br from-sky-100 to-indigo-100">🏝️</div>
                <?php endif; ?>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-1">
                  <h4 class="font-bold text-xs text-slate-800 truncate"><?= e($r['judul']) ?></h4>
                  <span class="shrink-0 px-2 py-0.5 rounded-full text-[9px] font-bold bg-sky-50 text-sky-600"><?= $label ?></span>
                </div>
                <p class="text-[10px] text-slate-500 mt-0.5 truncate"><?= e($r['judul']) ?></p>
                <p class="text-[10px] text-slate-400 mt-0.5"><?= date('d M Y', strtotime($r['tanggal'])) ?></p>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- CTA -->
    <div class="relative rounded-3xl bg-gradient-to-br from-sky-100 via-sky-50 to-indigo-100 border border-sky-100 p-5 overflow-hidden">
      <div class="absolute -top-6 -right-6 w-20 h-20 rounded-full bg-white/60"></div>
      <div class="relative">
        <div class="text-4xl mb-2">🗺️</div>
        <h3 class="font-extrabold text-sm text-sky-800 leading-snug">Sudah punya rencana baru?</h3>
        <p class="text-[11px] text-sky-700 mt-1 leading-relaxed">Jangan lupa catat rencana perjalananmu agar tidak terlupakan!</p>
        <a href="rencana_buat.php" class="mt-3 w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold shadow-md shadow-sky-200 transition">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-3.5 h-3.5"><path d="M12 5v14M5 12h14"/></svg>
          Buat Rencana Baru
        </a>
      </div>
    </div>

    <!-- Quote -->
    <div class="relative rounded-3xl overflow-hidden border border-slate-100">
      <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=600&q=80"
           class="w-full h-32 object-cover" alt="">
      <div class="absolute inset-0 bg-gradient-to-t from-sky-900/80 via-sky-900/40 to-transparent"></div>
      <div class="absolute inset-0 p-5 flex items-center justify-center text-center">
        <div>
          <p class="text-xs italic text-white/95 font-bold leading-relaxed">"Bukan sekadar pergi,<br>tapi tentang menemukan<br>versi terbaik dari diri sendiri."</p>
          <div class="text-rose-300 text-xs mt-2">♥</div>
        </div>
      </div>
    </div>
  </aside>
</div>

<?php include __DIR__ . '/foot.php'; ?>