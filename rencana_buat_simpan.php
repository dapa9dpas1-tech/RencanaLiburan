  <!-- ============================================ -->
  <!-- KOLOM KANAN: RINGKASAN RENCANA -->
  <!-- ============================================ -->
  <aside class="space-y-4 min-w-0">

    <!-- Kartu Ringkasan Live -->
    <div class="bg-white rounded-3xl border border-slate-100 p-5 sticky top-20">
      <div class="flex items-center gap-2 mb-4">
        <span class="w-8 h-8 rounded-full bg-sky-100 text-sky-600 grid place-items-center">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
            <path d="M9 11H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2h-4M9 11V7a3 3 0 1 1 6 0v4M9 11h6"/>
          </svg>
        </span>
        <h3 class="font-extrabold text-sm text-slate-800">Ringkasan Rencana</h3>
      </div>

      <div class="space-y-3 text-xs">
        <div class="flex justify-between gap-3 py-2 border-b border-slate-100">
          <span class="text-slate-500 shrink-0">Destinasi</span>
          <span class="font-bold text-slate-700 text-right truncate" id="sumDestinasi">Belum diisi</span>
        </div>
        <div class="flex justify-between gap-3 py-2 border-b border-slate-100">
          <span class="text-slate-500 shrink-0">Tanggal</span>
          <span class="font-bold text-slate-700 text-right" id="sumTanggal">Belum diisi</span>
        </div>
        <div class="flex justify-between gap-3 py-2 border-b border-slate-100">
          <span class="text-slate-500 shrink-0">Lama</span>
          <span class="font-bold text-slate-700 text-right" id="sumLama">-</span>
        </div>
        <div class="flex justify-between gap-3 py-2 border-b border-slate-100">
          <span class="text-slate-500 shrink-0">Traveler</span>
          <span class="font-bold text-slate-700 text-right" id="sumTraveler">2 Orang</span>
        </div>
        <div class="flex justify-between gap-3 py-2 border-b border-slate-100">
          <span class="text-slate-500 shrink-0">Jenis</span>
          <span class="font-bold text-slate-700 text-right" id="sumJenis">Liburan</span>
        </div>
        <div class="flex justify-between gap-3 py-2">
          <span class="text-slate-500 shrink-0">Minat</span>
          <span class="font-bold text-slate-700 text-right text-[11px]" id="sumMinat">-</span>
        </div>
      </div>
    </div>

    <!-- Kartu Tips -->
    <div class="relative rounded-3xl bg-gradient-to-br from-sky-100 via-sky-50 to-indigo-100 border border-sky-100 p-5 overflow-hidden">
      <div class="absolute -top-4 -right-4 text-6xl opacity-20">✈️</div>
      <div class="relative">
        <div class="text-3xl mb-2">🎒</div>
        <h3 class="font-extrabold text-sm text-sky-800 leading-snug">Tips Merencanakan Liburan</h3>
        <ul class="mt-3 space-y-2 text-[11px] text-sky-700">
          <li class="flex items-start gap-1.5"><span>✓</span><span>Tentukan tanggal jauh-jauh hari</span></li>
          <li class="flex items-start gap-1.5"><span>✓</span><span>Riset destinasi & cuaca</span></li>
          <li class="flex items-start gap-1.5"><span>✓</span><span>Siapkan budget cadangan 20%</span></li>
          <li class="flex items-start gap-1.5"><span>✓</span><span>Buat itinerary yang fleksibel</span></li>
        </ul>
      </div>
    </div>

    <!-- Kartu Ilustrasi -->
    <div class="relative rounded-3xl overflow-hidden border border-slate-100">
      <img src="https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=600&q=80"
           class="w-full h-44 object-cover" alt="">
      <div class="absolute inset-0 bg-gradient-to-t from-sky-900/70 via-transparent to-transparent"></div>
      <div class="absolute bottom-4 right-4 text-right text-white">
        <p class="text-base font-extrabold italic leading-tight">Liburan<br>Jadi Lebih<br>Mudah!</p>
        <div class="text-rose-300 text-xs mt-1">♥</div>
      </div>
    </div>
  </aside>
</div>

<!-- Script Live Update Ringkasan -->
<script>
const formBuat = document.querySelector('form[action="rencana_buat_simpan.php"]');
const sumDestinasi = document.getElementById('sumDestinasi');
const sumTanggal  = document.getElementById('sumTanggal');
const sumLama     = document.getElementById('sumLama');
const sumTraveler = document.getElementById('sumTraveler');
const sumJenis    = document.getElementById('sumJenis');
const sumMinat    = document.getElementById('sumMinat');

function fmtTanggal(d) {
  if (!d) return null;
  const t = new Date(d);
  return t.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function updateRingkasan() {
  if (!formBuat) return;
  const tujuan = formBuat.querySelector('input[name="destinasi_tujuan"]').value.trim();
  const tglB   = formBuat.querySelector('input[name="tanggal_berangkat"]').value;
  const tglP   = formBuat.querySelector('input[name="tanggal_pulang"]').value;
  const jml    = formBuat.querySelector('select[name="jumlah_traveler"]').value;
  const jenis  = formBuat.querySelector('input[name="jenis_perjalanan"]:checked')?.value || 'Liburan';
  const minat  = document.getElementById('inputMinat').value;

  sumDestinasi.textContent = tujuan || 'Belum diisi';

  if (tglB && tglP) {
    sumTanggal.textContent = fmtTanggal(tglB) + ' → ' + fmtTanggal(tglP);
    const diff = Math.ceil((new Date(tglP) - new Date(tglB)) / 86400000) + 1;
    sumLama.textContent = diff > 0 ? diff + ' hari' : '-';
  } else if (tglB) {
    sumTanggal.textContent = fmtTanggal(tglB) + ' → ?';
  } else {
    sumTanggal.textContent = 'Belum diisi';
  }

  sumTraveler.textContent = jml + ' Orang';
  sumJenis.textContent    = jenis;
  sumMinat.textContent    = minat || '-';
}

if (formBuat) {
  formBuat.querySelectorAll('input, select, textarea').forEach(el => {
    el.addEventListener('input', updateRingkasan);
    el.addEventListener('change', updateRingkasan);
  });
  // Trigger untuk chip minat
  document.querySelectorAll('.chip').forEach(c => c.addEventListener('click', updateRingkasan));
  updateRingkasan();
}
</script>

<?php include __DIR__ . '/foot.php'; ?>