<?php
require_once __DIR__ . '/func.php';
must_login();
$uid = (int)$_SESSION['uid'];

$err = $_GET['err'] ?? '';
$ok  = $_GET['ok'] ?? '';

$title = 'Buat Rencana Destinasi';
include __DIR__ . '/head.php';
?>

<!-- BACK BUTTON -->
<a href="index.php" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-sky-600 font-semibold mb-3 transition">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
  Kembali
</a>

<div class="max-w-4xl mx-auto bg-white rounded-3xl border border-slate-100 overflow-hidden">

  <!-- HERO FORM -->
  <div class="relative h-40 sm:h-48 overflow-hidden">
    <img src="https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=1600&q=80"
         class="absolute inset-0 w-full h-full object-cover" alt="">
    <div class="absolute inset-0 bg-gradient-to-r from-sky-900/60 via-sky-700/30 to-transparent"></div>
    <div class="relative p-5 sm:p-7 h-full flex flex-col justify-center text-white">
      <h1 class="text-2xl sm:text-3xl font-extrabold">Buat Rencana Destinasi</h1>
      <p class="text-xs sm:text-sm text-white/85 mt-1">Atur perjalanan impianmu dengan mudah dan menyenangkan.</p>
    </div>
  </div>

  <!-- PROGRESS STEPS -->
  <div class="px-5 sm:px-7 pt-5 sm:pt-6 pb-4 border-b border-slate-100">
    <div class="flex items-center justify-between max-w-2xl mx-auto">
      <?php
      $steps = [
        ['1', 'Informasi Dasar', true],
        ['2', 'Detail Perjalanan', false],
        ['3', 'Budget & Fasilitas', false],
        ['4', 'Preview', false],
      ];
      foreach ($steps as $i => $step):
        $isActive = $step[2];
      ?>
        <div class="flex items-center <?= $i < count($steps)-1 ? 'flex-1' : '' ?>">
          <div class="flex flex-col items-center gap-1.5">
            <div class="w-8 h-8 rounded-full grid place-items-center text-xs font-bold border-2
              <?= $isActive ? 'bg-sky-500 border-sky-500 text-white' : 'bg-white border-slate-300 text-slate-400' ?>">
              <?= $step[0] ?>
            </div>
            <span class="text-[10px] sm:text-[11px] font-bold whitespace-nowrap <?= $isActive ? 'text-sky-600' : 'text-slate-400' ?>">
              <?= $step[1] ?>
            </span>
          </div>
          <?php if ($i < count($steps)-1): ?>
            <div class="flex-1 h-0.5 bg-slate-200 mx-1 sm:mx-2 mb-5"></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- FORM -->
  <form method="post" action="rencana_buat_simpan.php" class="p-5 sm:p-7">

    <?php if ($err): ?>
      <div class="mb-4 flex items-start gap-2 bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-2xl px-4 py-3">
        <span>⚠️</span><span><?= e($err) ?></span>
      </div>
    <?php endif; ?>

    <!-- SECTION 1: INFORMASI DASAR -->
    <div class="mb-6">
      <div class="flex items-start gap-3 mb-4">
        <span class="w-8 h-8 rounded-full bg-sky-500 text-white grid place-items-center text-sm font-bold shrink-0">1</span>
        <div>
          <h2 class="font-extrabold text-base sm:text-lg text-slate-800">Informasi Dasar</h2>
          <p class="text-xs text-slate-500 mt-0.5">Mulai dengan menentukan tujuan dan waktu perjalananmu.</p>
        </div>
      </div>

      <div class="space-y-4">
        <!-- Destinasi Tujuan -->
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-1.5">
            Destinasi Tujuan <span class="text-rose-500">*</span>
          </label>
          <div class="relative">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
              <path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/>
            </svg>
            <input type="text" name="destinasi_tujuan" required
                   placeholder="Cari destinasi (contoh: Yogyakarta, Bali, Labuan Bajo...)"
                   class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 transition">
          </div>
        </div>

        <!-- Tanggal -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-1.5">
              Tanggal Berangkat <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
                <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
              </svg>
              <input type="date" name="tanggal_berangkat" required
                     class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 transition">
            </div>
          </div>
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-1.5">
              Tanggal Pulang <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
                <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
              </svg>
              <input type="date" name="tanggal_pulang" required
                     class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 transition">
            </div>
          </div>
        </div>

        <!-- Jumlah Traveler + Jenis -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-1.5">
              Jumlah Traveler <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
                <circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/>
              </svg>
              <select name="jumlah_traveler"
                      class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 transition appearance-none">
                <?php for ($i = 1; $i <= 20; $i++): ?>
                  <option value="<?= $i ?>" <?= $i === 2 ? 'selected' : '' ?>><?= $i ?> Orang</option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-1.5">
              Jenis Perjalanan <span class="text-rose-500">*</span>
            </label>
            <div class="flex gap-2">
              <label class="flex-1 cursor-pointer">
                <input type="radio" name="jenis_perjalanan" value="Liburan" class="peer sr-only" checked>
                <div class="px-3 py-3 rounded-xl border border-slate-200 bg-slate-50 text-center text-xs font-bold text-slate-600 peer-checked:bg-sky-50 peer-checked:border-sky-400 peer-checked:text-sky-600 transition flex items-center justify-center gap-1.5">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path d="M2 12l19-7-7 19-3-8-9-4z"/></svg>
                  Liburan
                </div>
              </label>
              <label class="flex-1 cursor-pointer">
                <input type="radio" name="jenis_perjalanan" value="Dinas" class="peer sr-only">
                <div class="px-3 py-3 rounded-xl border border-slate-200 bg-slate-50 text-center text-xs font-bold text-slate-600 peer-checked:bg-sky-50 peer-checked:border-sky-400 peer-checked:text-sky-600 transition flex items-center justify-center gap-1.5">
                  💼 Dinas
                </div>
              </label>
              <label class="flex-1 cursor-pointer">
                <input type="radio" name="jenis_perjalanan" value="Lainnya" class="peer sr-only">
                <div class="px-3 py-3 rounded-xl border border-slate-200 bg-slate-50 text-center text-xs font-bold text-slate-600 peer-checked:bg-sky-50 peer-checked:border-sky-400 peer-checked:text-sky-600 transition flex items-center justify-center gap-1.5">
                  ••• Lainnya
                </div>
              </label>
            </div>
          </div>
        </div>

        <!-- Deskripsi -->
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-1.5">Deskripsi Singkat</label>
          <textarea name="deskripsi" rows="4" maxlength="500"
                    placeholder="Ceritakan sedikit tentang rencana perjalananmu..."
                    class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 transition resize-none"
                    oninput="document.getElementById('charCount').textContent = this.value.length"></textarea>
          <div class="text-right text-[11px] text-slate-400 mt-1">
            <span id="charCount">0</span>/500
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION 2: PREFERENSI & MINAT -->
    <div class="mb-6 pt-6 border-t border-slate-100">
      <div class="flex items-start gap-3 mb-4">
        <span class="w-8 h-8 rounded-full bg-sky-500 text-white grid place-items-center text-sm font-bold shrink-0">2</span>
        <div>
          <h2 class="font-extrabold text-base sm:text-lg text-slate-800">Preferensi & Minat</h2>
          <p class="text-xs text-slate-500 mt-0.5">Bantu kami memberikan rekomendasi yang lebih sesuai.</p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-bold text-slate-700 mb-2">
          Minat Utama <span class="text-[11px] font-normal text-slate-400">(bisa pilih lebih dari satu)</span>
        </label>
        <div class="flex flex-wrap gap-2" id="chipsMinat">
          <?php
          $minatList = [
            ['Alam', '🏔️'],
            ['Budaya', '🏛️'],
            ['Kuliner', '🍜'],
            ['Belanja', '🛍️'],
            ['Petualangan', '⛰️'],
            ['Hiburan', '🎭'],
            ['Religi', '🕌'],
          ];
          foreach ($minatList as $m):
          ?>
            <button type="button" data-value="<?= e($m[0]) ?>"
              class="chip shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full border border-slate-200 bg-white text-xs font-bold text-slate-600 hover:border-sky-400 transition">
              <span><?= $m[1] ?></span>
              <?= e($m[0]) ?>
            </button>
          <?php endforeach; ?>
        </div>
        <input type="hidden" name="minat" id="inputMinat">
      </div>

      <!-- Tip Box -->
      <div class="mt-5 flex items-start gap-3 bg-sky-50 border border-sky-100 rounded-2xl px-4 py-3.5">
        <span class="w-8 h-8 rounded-full bg-sky-500 text-white grid place-items-center shrink-0">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
            <path d="M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.7c.6.5 1 1.3 1 2.1v.2h6v-.2c0-.8.4-1.6 1-2.1A7 7 0 0 0 12 2z"/>
          </svg>
        </span>
        <p class="text-xs text-sky-700 leading-relaxed">
          <b>Tip!</b> Semakin detail informasi yang kamu berikan, semakin akurat rekomendasi yang kami berikan.
        </p>
      </div>
    </div>

    <!-- SUBMIT -->
    <div class="flex justify-end pt-2">
      <button type="submit"
              class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-lg shadow-sky-200 transition">
        Selanjutnya
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
      </button>
    </div>

  </form>
</div>

<script>
const chipContainer = document.getElementById('chipsMinat');
const inputMinat = document.getElementById('inputMinat');
if (chipContainer) {
  chipContainer.querySelectorAll('.chip').forEach(chip => {
    chip.addEventListener('click', () => {
      chip.classList.toggle('active');
      const selected = Array.from(chipContainer.querySelectorAll('.chip.active'))
        .map(c => c.dataset.value).join(',');
      inputMinat.value = selected;
    });
  });
}
</script>

<?php include __DIR__ . '/foot.php'; ?>