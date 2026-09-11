<?php
require_once __DIR__ . '/func.php';
must_login();
$uid = (int)$_SESSION['uid'];
$id  = (int)($_GET['id'] ?? 0);
$d   = get_dest($conn, $id, $uid);
if (!$d) { flash('e','Destinasi tidak ditemukan.'); header('Location: dest.php'); exit; }

// ============ HANDLE TAMBAH ANGGOTA ============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'add_anggota') {
    $nama  = trim($_POST['an_nama'] ?? '');
    $email = trim($_POST['an_email'] ?? '');
    $peran = trim($_POST['an_peran'] ?? 'Anggota');

    if ($nama === '') {
        flash('e', 'Nama anggota wajib diisi.');
    } else {
        $s = $conn->prepare("INSERT INTO anggota (destinasi_id, nama, email, peran) VALUES (?,?,?,?)");
        $s->bind_param('isss', $id, $nama, $email, $peran);
        if ($s->execute()) flash('s', 'Anggota berhasil ditambahkan!');
        else               flash('e', 'Gagal menambahkan anggota.');
        $s->close();
    }
    header('Location: d_view.php?id=' . $id); exit;
}

// ============ AMBIL ANGGOTA ============
$s = $conn->prepare("SELECT * FROM anggota WHERE destinasi_id=? ORDER BY created_at ASC");
$s->bind_param('i', $id); $s->execute();
$anggota = $s->get_result()->fetch_all(MYSQLI_ASSOC); $s->close();

// ============ AMBIL RENCANA ============
$s = $conn->prepare("SELECT * FROM rencana WHERE destinasi_id=? ORDER BY hari ASC, jam ASC, id ASC");
$s->bind_param('i', $id); $s->execute();
$rows = $s->get_result()->fetch_all(MYSQLI_ASSOC); $s->close();

$byHari = [];
foreach ($rows as $r) $byHari[(int)$r['hari']][] = $r;
ksort($byHari);

$tab = $_GET['tab'] ?? 'rencana';
if (!in_array($tab, ['rencana','detail','anggaran'], true)) $tab = 'rencana';

// Hitung budget per hari untuk tombol hapus
$budgetPerHari = (int)$d['lama'] > 0 ? (float)$d['budget'] / (int)$d['lama'] : 0;

$title = 'Detail ' . $d['judul'];
include __DIR__ . '/head.php';
?>

<!-- HERO -->
<section class="relative rounded-3xl overflow-hidden mb-5 shadow-lg">
  <?php if ($d['foto']): ?>
    <img src="uploads/<?= e($d['foto']) ?>" class="w-full h-56 sm:h-72 object-cover">
  <?php else: ?>
    <div class="w-full h-56 sm:h-72 bg-gradient-to-br from-sky-400 to-indigo-500"></div>
  <?php endif; ?>
  <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>

  <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-sky-500 text-white text-[11px] font-bold shadow-lg">Populer</span>

  <div class="absolute top-3 right-3 flex gap-2">
    <a href="d_edit.php?id=<?= (int)$d['id'] ?>" class="w-9 h-9 rounded-full bg-white/90 grid place-items-center text-slate-700 hover:bg-white shadow">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
    </a>
    <a href="d_del.php?id=<?= (int)$d['id'] ?>" onclick="return confirm('Hapus destinasi ini?')" class="w-9 h-9 rounded-full bg-white/90 grid place-items-center text-rose-600 hover:bg-white shadow">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg>
    </a>
  </div>

  <div class="absolute bottom-0 p-4 sm:p-7 text-white w-full">
    <p class="text-xs text-white/80 flex items-center gap-1">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2"/></svg>
      <?= e($d['judul']) ?>
    </p>
    <h1 class="text-2xl sm:text-4xl font-extrabold mt-1"><?= e($d['judul']) ?></h1>
    <div class="flex flex-wrap items-center gap-2 mt-2 text-xs sm:text-sm">
      <span class="px-2.5 py-1 rounded-full bg-white/20 backdrop-blur border border-white/20">📅 <?= tgl($d['tanggal']) ?></span>
      <span class="px-2.5 py-1 rounded-full bg-white/20 backdrop-blur border border-white/20">💰 <?= rp($d['budget']) ?></span>
      <span class="px-2.5 py-1 rounded-full font-bold <?= (int)$d['status']===1 ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-amber-900' ?>">
        <?= (int)$d['status']===1 ? '✓ Tercapai' : '⏳ Belum' ?>
      </span>
    </div>
  </div>
</section>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

  <div class="lg:col-span-2 space-y-4 min-w-0">
    <!-- TABS -->
    <div class="bg-white rounded-2xl border border-slate-100 p-1.5 flex gap-1">
      <?php foreach ([['rencana','Rencana Perjalanan'],['detail','Detail'],['anggaran','Anggaran']] as $t): ?>
        <a href="?id=<?= (int)$d['id'] ?>&tab=<?= $t[0] ?>" class="flex-1 text-center px-3 py-2 rounded-xl text-xs sm:text-sm font-bold transition <?= $tab===$t[0] ? 'bg-sky-500 text-white shadow-lg shadow-sky-200' : 'text-slate-500 hover:bg-slate-50' ?>"><?= $t[1] ?></a>
      <?php endforeach; ?>
    </div>

    <?php if ($tab === 'rencana'): ?>
      <div class="bg-white rounded-2xl border border-slate-100 p-4 sm:p-5">
        <?php if (!$byHari): ?>
          <div class="text-center py-10 text-slate-400">
            <div class="text-4xl mb-2">📝</div>
            <p class="font-bold text-slate-700">Belum ada rencana</p>
            <a href="r_add.php?id=<?= (int)$d['id'] ?>" class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-sky-500 text-white text-sm font-bold hover:bg-sky-600">+ Tambah Kegiatan</a>
          </div>
        <?php else: ?>

          <?php foreach ($byHari as $hari => $items): ?>
            <?php
              // Hitung tanggal hari ini (tanggal mulai + (hari-1) hari)
              $tglHari = date('Y-m-d', strtotime($d['tanggal'] . ' +' . ($hari - 1) . ' days'));
            ?>

            <!-- ============ HEADER HARI + TOMBOL HAPUS ============ -->
            <div class="flex items-center justify-between mb-3 flex-wrap gap-2 <?= $hari > 1 ? 'mt-6' : '' ?>">
              <div class="flex items-center gap-2 flex-wrap">
                <h3 class="font-extrabold text-base sm:text-lg text-slate-800">
                  Hari <?= $hari ?>
                  <span class="text-slate-400 font-normal text-sm">– <?= tgl($tglHari) ?></span>
                </h3>
                <?php if ($hari === 1): ?>
                  <span class="px-3 py-1 rounded-full bg-sky-50 text-sky-600 text-xs font-bold">☀️ 28°C Cerah</span>
                <?php else: ?>
                  <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-600 text-xs font-bold">🏔 Eksplor</span>
                <?php endif; ?>
              </div>

              <!-- TOMBOL HAPUS HARI -->
              <a href="hapus_hari.php?destinasi_id=<?= (int)$d['id'] ?>&hari=<?= $hari ?>"
                 onclick="return confirm('Hapus Hari <?= $hari ?> beserta semua kegiatannya?\n\nTotal budget akan otomatis dihitung ulang.');"
                 class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold transition shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5">
                  <path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/>
                </svg>
                Hapus Hari
              </a>
            </div>

            <!-- KEGIATAN HARI INI -->
            <div class="relative border-l-2 border-sky-100 ml-3 pl-5 space-y-3">
              <?php foreach ($items as $r): ?>
                <div class="relative bg-slate-50 rounded-xl p-3 flex gap-3">
                  <span class="absolute -left-[27px] top-4 w-3 h-3 rounded-full bg-sky-500 ring-4 ring-white"></span>
                  <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 text-[11px] mb-1.5">
                      <?php if ($r['jam']): ?>
                        <span class="px-2 py-0.5 rounded-md bg-white border border-sky-100 text-sky-600 font-bold">🕐 <?= date('H:i', strtotime($r['jam'])) ?></span>
                      <?php endif; ?>
                      <?php if ($r['lokasi']): ?>
                        <span class="px-2 py-0.5 rounded-md bg-white border border-rose-100 text-rose-600 font-bold">📍 <?= e($r['lokasi']) ?></span>
                      <?php endif; ?>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-700 leading-relaxed"><?= e($r['kegiatan']) ?></p>
                    <div class="flex gap-1.5 mt-2">
                      <a href="r_edit.php?id=<?= (int)$r['id'] ?>" class="px-2.5 py-1 rounded-md bg-white border border-slate-200 text-slate-600 text-[11px] font-bold hover:bg-slate-50">Edit</a>
                      <a href="r_del.php?id=<?= (int)$r['id'] ?>" onclick="return confirm('Hapus?')" class="px-2.5 py-1 rounded-md bg-rose-50 text-rose-600 text-[11px] font-bold hover:bg-rose-100">Hapus</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>

              <?php if (!$items): ?>
                <p class="text-xs text-slate-400 italic py-2">Belum ada kegiatan di hari ini.</p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>

          <a href="r_add.php?id=<?= (int)$d['id'] ?>" class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-50 text-sky-600 text-sm font-bold hover:bg-sky-100">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Kegiatan
          </a>
        <?php endif; ?>
      </div>

    <?php elseif ($tab === 'detail'): ?>
      <div class="bg-white rounded-2xl border border-slate-100 p-5">
        <h2 class="font-extrabold text-base sm:text-lg text-slate-800 mb-4">Detail Destinasi</h2>
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div class="bg-slate-50 rounded-xl p-3">
            <div class="text-[11px] text-slate-400 uppercase font-semibold">Judul</div>
            <div class="font-bold text-slate-700 mt-1"><?= e($d['judul']) ?></div>
          </div>
          <div class="bg-slate-50 rounded-xl p-3">
            <div class="text-[11px] text-slate-400 uppercase font-semibold">Tanggal</div>
            <div class="font-bold text-slate-700 mt-1"><?= tgl($d['tanggal']) ?></div>
          </div>
          <div class="bg-slate-50 rounded-xl p-3">
            <div class="text-[11px] text-slate-400 uppercase font-semibold">Lama</div>
            <div class="font-bold text-slate-700 mt-1"><?= (int)$d['lama'] ?> hari</div>
          </div>
          <div class="bg-slate-50 rounded-xl p-3">
            <div class="text-[11px] text-slate-400 uppercase font-semibold">Budget</div>
            <div class="font-bold text-sky-600 mt-1"><?= rp($d['budget']) ?></div>
          </div>
        </div>
      </div>

    <?php else: ?>
      <?php $perHari = $d['lama'] > 0 ? $d['budget'] / $d['lama'] : 0; ?>
      <div class="bg-white rounded-2xl border border-slate-100 p-5">
        <h2 class="font-extrabold text-base sm:text-lg text-slate-800 mb-4">Anggaran</h2>
        <div class="space-y-3">
          <div class="flex justify-between items-center py-2 border-b border-slate-100">
            <span class="text-sm text-slate-600">Total Budget</span>
            <span class="font-extrabold text-sky-600"><?= rp($d['budget']) ?></span>
          </div>
          <div class="flex justify-between items-center py-2 border-b border-slate-100">
            <span class="text-sm text-slate-600">Lama Perjalanan</span>
            <span class="font-bold text-slate-800"><?= (int)$d['lama'] ?> hari</span>
          </div>
          <div class="flex justify-between items-center py-2">
            <span class="text-sm text-slate-600">Estimasi per Hari</span>
            <span class="font-bold text-emerald-600"><?= rp($perHari) ?></span>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- SIDEBAR KANAN -->
  <aside class="lg:col-span-1 space-y-4 min-w-0">
    <div class="bg-white rounded-2xl border border-slate-100 p-4">
      <h3 class="font-extrabold text-sm text-slate-800 mb-3">Ringkasan</h3>
      <div class="space-y-2.5 text-sm">
        <div class="flex justify-between"><span class="text-slate-500">Lama Perjalanan</span><span class="font-bold text-slate-800"><?= (int)$d['lama'] ?> Hari</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Tanggal</span><span class="font-bold text-slate-800"><?= tgl($d['tanggal']) ?></span></div>
        <div class="flex justify-between"><span class="text-slate-500">Total Budget</span><span class="font-bold text-sky-600"><?= rp($d['budget']) ?></span></div>
        <div class="flex justify-between"><span class="text-slate-500">Destinasi</span><span class="font-bold text-slate-800">Indonesia</span></div>
      </div>
    </div>

    <!-- ============ ANGGOTA ============ -->
    <div class="bg-white rounded-2xl border border-slate-100 p-4">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-extrabold text-sm text-slate-800">Anggota</h3>
        <span class="text-[11px] text-slate-400"><?= count($anggota) + 1 ?> orang</span>
      </div>

      <div class="flex items-center gap-3 py-2">
        <span class="w-9 h-9 rounded-full bg-gradient-to-br from-sky-500 to-indigo-500 text-white grid place-items-center font-bold text-sm">
          <?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?>
        </span>
        <div class="flex-1 min-w-0">
          <div class="font-bold text-sm text-slate-800 truncate"><?= e($_SESSION['nama']) ?></div>
          <div class="text-[11px] text-slate-400">Admin</div>
        </div>
      </div>

      <?php foreach ($anggota as $a): ?>
        <div class="flex items-center gap-3 py-2 border-t border-slate-100">
          <span class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 grid place-items-center font-bold text-sm">
            <?= strtoupper(substr($a['nama'], 0, 1)) ?>
          </span>
          <div class="flex-1 min-w-0">
            <div class="font-bold text-sm text-slate-800 truncate"><?= e($a['nama']) ?></div>
            <div class="text-[11px] text-slate-400 truncate"><?= e($a['peran']) ?><?= $a['email'] ? ' · ' . e($a['email']) : '' ?></div>
          </div>
          <a href="anggota_del.php?id=<?= (int)$a['id'] ?>&dest=<?= (int)$d['id'] ?>"
             onclick="return confirm('Hapus anggota ini?')"
             class="w-7 h-7 rounded-lg bg-rose-50 text-rose-500 grid place-items-center hover:bg-rose-100 transition">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg>
          </a>
        </div>
      <?php endforeach; ?>

      <button type="button" onclick="toggleAddAnggota()"
        class="mt-3 w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl border border-dashed border-slate-200 text-slate-500 text-xs font-bold hover:bg-slate-50 transition">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Anggota
      </button>

      <form id="formAnggota" method="post" class="hidden mt-3 space-y-3 bg-slate-50 rounded-xl p-3 border border-slate-100">
        <input type="hidden" name="act" value="add_anggota">

        <div>
          <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Anggota *</label>
          <input type="text" name="an_nama" required placeholder="Contoh: Budi Santoso"
            class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-sky-500 outline-none text-sm">
        </div>

        <div>
          <label class="block text-[11px] font-bold text-slate-600 mb-1">Email (opsional)</label>
          <input type="email" name="an_email" placeholder="budi@email.com"
            class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-sky-500 outline-none text-sm">
        </div>

        <div>
          <label class="block text-[11px] font-bold text-slate-600 mb-1">Peran</label>
          <select name="an_peran"
            class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:border-sky-500 outline-none text-sm bg-white">
            <option value="Anggota">Anggota</option>
            <option value="Koordinator">Koordinator</option>
            <option value="Driver">Driver</option>
            <option value="Fotografer">Fotografer</option>
            <option value="Bendahara">Bendahara</option>
          </select>
        </div>

        <div class="flex gap-2 pt-1">
          <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold transition">
            Simpan Anggota
          </button>
          <button type="button" onclick="toggleAddAnggota()" class="px-3 py-2 rounded-lg bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-100">
            Batal
          </button>
        </div>
      </form>
    </div>

    <a href="rencana.php?did=<?= (int)$d['id'] ?>" class="block w-full text-center px-4 py-3 rounded-2xl bg-sky-500 hover:bg-sky-600 text-white font-bold text-sm shadow-lg shadow-sky-200 transition">
      🗺️ Lihat Peta
    </a>
  </aside>
</div>

<script>
function toggleAddAnggota() {
  var f = document.getElementById('formAnggota');
  if (!f) return;
  f.classList.toggle('hidden');
  if (!f.classList.contains('hidden')) {
    f.querySelector('input[name="an_nama"]').focus();
  }
}
</script>

<?php include __DIR__ . '/foot.php'; ?>