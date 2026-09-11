<?php
require_once __DIR__ . '/func.php';
must_login();
$uid = (int)$_SESSION['uid'];

// ============================================
// PROSES SIMPAN (semua form dalam 1 file)
// ============================================
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';

    // ---- Update Informasi Pribadi ----
    if ($act === 'update_info') {
        $nama          = trim($_POST['nama'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $tanggal_lahir = $_POST['tanggal_lahir'] ?: null;
        $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '') ?: null;
        $no_telepon    = trim($_POST['no_telepon'] ?? '') ?: null;
        $lokasi        = trim($_POST['lokasi'] ?? '') ?: null;

        if ($nama === '' || $email === '') {
            $flash = ['e', 'Nama dan email wajib diisi.'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $flash = ['e', 'Format email tidak valid.'];
        } else {
            $chk = $conn->prepare("SELECT id FROM users WHERE email=? AND id<>?");
            $chk->bind_param('si', $email, $uid);
            $chk->execute(); $chk->store_result();
            if ($chk->num_rows > 0) {
                $flash = ['e', 'Email sudah digunakan pengguna lain.'];
            } else {
                $up = $conn->prepare("UPDATE users SET nama=?, email=?, tanggal_lahir=?, jenis_kelamin=?, no_telepon=?, lokasi=? WHERE id=?");
                $up->bind_param('ssssssi', $nama, $email, $tanggal_lahir, $jenis_kelamin, $no_telepon, $lokasi, $uid);
                if ($up->execute()) {
                    $_SESSION['nama'] = $nama;
                    $flash = ['s', 'Informasi pribadi berhasil diperbarui.'];
                } else {
                    $flash = ['e', 'Gagal menyimpan: ' . $conn->error];
                }
                $up->close();
            }
            $chk->close();
        }
    }

    // ---- Update Tentang Saya ----
    if ($act === 'update_tentang') {
        $tentang = trim($_POST['tentang_saya'] ?? '');
        $up = $conn->prepare("UPDATE users SET tentang_saya=? WHERE id=?");
        $up->bind_param('si', $tentang, $uid);
        if ($up->execute()) $flash = ['s', 'Tentang saya berhasil diperbarui.'];
        else               $flash = ['e', 'Gagal menyimpan.'];
        $up->close();
    }

    // ---- Ganti Password ----
    if ($act === 'update_password') {
        $lama  = $_POST['pass_lama'] ?? '';
        $baru  = $_POST['pass_baru'] ?? '';
        $ulang = $_POST['pass_ulang'] ?? '';

        if ($lama === '' || $baru === '' || $ulang === '') {
            $flash = ['e', 'Semua kolom password wajib diisi.'];
        } elseif (strlen($baru) < 6) {
            $flash = ['e', 'Password baru minimal 6 karakter.'];
        } elseif ($baru !== $ulang) {
            $flash = ['e', 'Konfirmasi password tidak cocok.'];
        } else {
            $s = $conn->prepare("SELECT password FROM users WHERE id=?");
            $s->bind_param('i', $uid); $s->execute();
            $row = $s->get_result()->fetch_assoc(); $s->close();
            $hash = $row['password'] ?? '';
            $valid = password_verify($lama, $hash) || $lama === $hash;

            if (!$valid) {
                $flash = ['e', 'Password lama salah.'];
            } else {
                $hashBaru = password_hash($baru, PASSWORD_DEFAULT);
                $up = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                $up->bind_param('si', $hashBaru, $uid);
                if ($up->execute()) $flash = ['s', 'Password berhasil diubah.'];
                else               $flash = ['e', 'Gagal mengubah password.'];
                $up->close();
            }
        }
    }

    // ---- Upload Foto Profil ----
    if ($act === 'upload_foto' && !empty($_FILES['foto']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $flash = ['e', 'Format foto harus JPG, PNG, atau WEBP.'];
        } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $flash = ['e', 'Ukuran foto maksimal 2MB.'];
        } else {
            $dir = __DIR__ . '/uploads/avatar/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $namaBaru = 'avatar_' . $uid . '_' . time() . '.' . $ext;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $dir . $namaBaru)) {
                // Hapus foto lama
                $s = $conn->prepare("SELECT foto FROM users WHERE id=?");
                $s->bind_param('i', $uid); $s->execute();
                $old = $s->get_result()->fetch_assoc()['foto'] ?? ''; $s->close();
                if ($old && file_exists($dir . $old)) @unlink($dir . $old);

                $up = $conn->prepare("UPDATE users SET foto=? WHERE id=?");
                $up->bind_param('si', $namaBaru, $uid);
                $up->execute(); $up->close();

                $flash = ['s', 'Foto profil berhasil diperbarui.'];
            } else {
                $flash = ['e', 'Gagal mengunggah foto.'];
            }
        }
    }

    // ---- Upload Foto Sampul ----
    if ($act === 'upload_sampul' && !empty($_FILES['sampul']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['sampul']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $flash = ['e', 'Format sampul harus JPG, PNG, atau WEBP.'];
        } elseif ($_FILES['sampul']['size'] > 5 * 1024 * 1024) {
            $flash = ['e', 'Ukuran sampul maksimal 5MB.'];
        } else {
            $dir = __DIR__ . '/uploads/sampul/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $namaBaru = 'sampul_' . $uid . '_' . time() . '.' . $ext;

            if (move_uploaded_file($_FILES['sampul']['tmp_name'], $dir . $namaBaru)) {
                $s = $conn->prepare("SELECT foto_sampul FROM users WHERE id=?");
                $s->bind_param('i', $uid); $s->execute();
                $old = $s->get_result()->fetch_assoc()['foto_sampul'] ?? ''; $s->close();
                if ($old && file_exists($dir . $old)) @unlink($dir . $old);

                $up = $conn->prepare("UPDATE users SET foto_sampul=? WHERE id=?");
                $up->bind_param('si', $namaBaru, $uid);
                $up->execute(); $up->close();

                $flash = ['s', 'Foto sampul berhasil diperbarui.'];
            } else {
                $flash = ['e', 'Gagal mengunggah sampul.'];
            }
        }
    }
}

// ============================================
// AMBIL DATA USER (setelah kemungkinan update)
// ============================================
$s = $conn->prepare("SELECT * FROM users WHERE id=?");
$s->bind_param('i', $uid); $s->execute();
$user = $s->get_result()->fetch_assoc(); $s->close();
if (!$user) { header('Location: login.php'); exit; }

$fotoUser = '';
if (!empty($user['foto']) && file_exists(__DIR__ . '/uploads/avatar/' . $user['foto'])) {
    $fotoUser = 'uploads/avatar/' . $user['foto'];
}
$fotoSampul = 'https://images.unsplash.com/photo-1505228395891-9a51e7e86bf6?auto=format&fit=crop&w=1600&q=80';
if (!empty($user['foto_sampul']) && file_exists(__DIR__ . '/uploads/sampul/' . $user['foto_sampul'])) {
    $fotoSampul = 'uploads/sampul/' . $user['foto_sampul'];
}

$tab = $_GET['tab'] ?? 'rencana';
if (!in_array($tab, ['rencana','favorit','ulasan','foto','tentang'], true)) $tab = 'rencana';

// Statistik
$s = $conn->prepare("SELECT COUNT(*) c FROM destinasi WHERE user_id=?");
$s->bind_param('i', $uid); $s->execute();
$totalRencana = (int)$s->get_result()->fetch_assoc()['c']; $s->close();
$totalFavorit = $totalRencana;
$totalPengikut = 243;
$totalMengikuti = 150;

// List rencana
$s = $conn->prepare("SELECT * FROM destinasi WHERE user_id=? ORDER BY created_at DESC LIMIT 12");
$s->bind_param('i', $uid); $s->execute();
$listRencana = $s->get_result()->fetch_all(MYSQLI_ASSOC); $s->close();

$statusMap = [
  0 => ['label' => 'Draft',        'color' => 'text-slate-600',   'icon' => '📝'],
  1 => ['label' => 'Dalam Proses', 'color' => 'text-sky-700',     'icon' => '⏱'],
  2 => ['label' => 'Selesai',      'color' => 'text-emerald-700', 'icon' => '✓'],
];

$title = 'Profil Saya';
include __DIR__ . '/head.php';
?>

<!-- FLASH MESSAGE -->
<?php if ($flash): ?>
  <div class="mb-4 flex items-start gap-2 <?= $flash[0] === 's' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700' ?> border text-sm rounded-2xl px-4 py-3">
    <span><?= $flash[0] === 's' ? '✅' : '⚠️' ?></span>
    <span><?= e($flash[1]) ?></span>
  </div>
<?php endif; ?>

<!-- ============================================ -->
<!-- HERO PROFIL -->
<!-- ============================================ -->
<section class="relative rounded-3xl overflow-hidden mb-5 shadow-lg shadow-sky-100">
  <div class="relative h-44 sm:h-52">
    <img src="<?= e($fotoSampul) ?>?v=<?= time() ?>" class="absolute inset-0 w-full h-full object-cover" alt="Cover">
    <div class="absolute inset-0 bg-gradient-to-r from-sky-900/40 via-transparent to-transparent"></div>

    <div class="hidden lg:block absolute right-8 top-8 text-right text-white drop-shadow-lg rotate-[-4deg]">
      <p class="text-xl font-extrabold italic leading-tight">Jelajah<br>Dunia, Ciptakan<br>Cerita!</p>
    </div>

    <div class="absolute bottom-4 right-4 flex items-center gap-2">
      <button type="button" onclick="openModal('modalInfo')"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold shadow-lg transition">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="w-3.5 h-3.5"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
        Edit Profil
      </button>
      <button type="button" onclick="openModal('modalSampul')"
         class="w-9 h-9 rounded-full bg-white hover:bg-slate-100 text-slate-700 grid place-items-center shadow-lg transition" title="Ubah Sampul">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="w-4 h-4"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
      </button>
    </div>
  </div>

  <div class="relative bg-white px-5 sm:px-7 pb-5">
    <div class="flex flex-col sm:flex-row sm:items-end gap-4 -mt-14 sm:-mt-16">
      <div class="relative shrink-0">
        <?php if ($fotoUser): ?>
          <img src="<?= e($fotoUser) ?>?v=<?= time() ?>" alt=""
               class="w-28 h-28 sm:w-32 sm:h-32 rounded-full object-cover ring-4 ring-white shadow-xl">
        <?php else: ?>
          <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-full bg-gradient-to-br from-sky-400 to-indigo-500 grid place-items-center text-white text-5xl font-extrabold ring-4 ring-white shadow-xl">
            <?= strtoupper(substr($user['nama'], 0, 1)) ?>
          </div>
        <?php endif; ?>
        <button type="button" onclick="openModal('modalFoto')"
                class="absolute bottom-1 right-1 w-9 h-9 rounded-full bg-sky-500 hover:bg-sky-600 text-white grid place-items-center shadow-lg ring-2 ring-white transition">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="w-4 h-4"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
        </button>
      </div>

      <div class="flex-1 min-w-0 pb-2">
        <div class="flex items-center gap-2 flex-wrap">
          <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800"><?= e($user['nama']) ?></h1>
          <span class="w-6 h-6 rounded-full bg-sky-500 grid place-items-center shadow">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3.5" class="w-3.5 h-3.5"><path d="M20 6 9 17l-5-5"/></svg>
          </span>
        </div>
        <p class="text-sm text-slate-500 mt-1"><?= e($user['email']) ?></p>
        <p class="text-xs sm:text-sm text-slate-600 mt-1.5">Traveller | Pecinta Alam & Kuliner | Selalu mencari cerita baru</p>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 mt-2">
          <span class="flex items-center gap-1.5">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2"/></svg>
            <?= e($user['lokasi'] ?? 'Belum diatur') ?>
          </span>
          <span class="flex items-center gap-1.5">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            Bergabung sejak <?= isset($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '-' ?>
          </span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- STATISTIK -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5">
  <div class="bg-white rounded-2xl border border-slate-100 p-4 flex items-center gap-3">
    <span class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 grid place-items-center shrink-0">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
    </span>
    <div><div class="text-xl font-extrabold text-slate-800"><?= $totalRencana ?></div><div class="text-[11px] text-slate-500">Rencana Liburan</div></div>
  </div>
  <div class="bg-white rounded-2xl border border-slate-100 p-4 flex items-center gap-3">
    <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 grid place-items-center shrink-0">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2"/></svg>
    </span>
    <div><div class="text-xl font-extrabold text-slate-800"><?= $totalFavorit ?></div><div class="text-[11px] text-slate-500">Destinasi Favorit</div></div>
  </div>
  <div class="bg-white rounded-2xl border border-slate-100 p-4 flex items-center gap-3">
    <span class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 grid place-items-center shrink-0">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><circle cx="9" cy="8" r="3.5"/><path d="M2 21c0-3.5 3-6 7-6s7 2.5 7 6"/><circle cx="17" cy="7" r="3"/><path d="M22 19c0-2.5-2-4-5-4"/></svg>
    </span>
    <div><div class="text-xl font-extrabold text-slate-800"><?= $totalPengikut ?></div><div class="text-[11px] text-slate-500">Pengikut</div></div>
  </div>
  <div class="bg-white rounded-2xl border border-slate-100 p-4 flex items-center gap-3">
    <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 grid place-items-center shrink-0">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>
    </span>
    <div><div class="text-xl font-extrabold text-slate-800"><?= $totalMengikuti ?></div><div class="text-[11px] text-slate-500">Mengikuti</div></div>
  </div>
</div>

<!-- TABS -->
<div class="mb-5 overflow-x-auto no-scrollbar">
  <div class="inline-flex gap-1.5 bg-white rounded-2xl border border-slate-100 p-1.5 min-w-full sm:min-w-0">
    <?php foreach ([['rencana','Rencana Saya'],['favorit','Favorit'],['ulasan','Ulasan'],['foto','Foto'],['tentang','Tentang']] as $t):
      $active = $tab === $t[0]; ?>
      <a href="?tab=<?= $t[0] ?>" class="shrink-0 px-4 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition whitespace-nowrap <?= $active ? 'bg-sky-500 text-white shadow-lg shadow-sky-200' : 'text-slate-500 hover:bg-slate-50' ?>"><?= $t[1] ?></a>
    <?php endforeach; ?>
  </div>
</div>

<!-- KONTEN -->
<div class="grid grid-cols-1 xl:grid-cols-[1fr_340px] gap-5 xl:gap-6">
  <div class="min-w-0">
    <?php if ($tab === 'rencana'): ?>
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="flex items-start gap-3">
          <span class="w-10 h-10 rounded-full bg-sky-100 text-sky-600 grid place-items-center shrink-0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path d="M2 12l19-7-7 19-3-8-9-4z"/></svg>
          </span>
          <div>
            <h2 class="font-extrabold text-lg text-slate-800">Rencana Perjalanan Saya</h2>
            <p class="text-xs text-slate-500 mt-0.5">Daftar rencana liburan yang pernah dan sedang kamu susun.</p>
          </div>
        </div>
        <a href="rencana_buat.php" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold shadow-lg shadow-sky-200 transition shrink-0">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M12 5v14M5 12h14"/></svg>
          Buat Rencana
        </a>
      </div>

      <?php if (!$listRencana): ?>
        <div class="bg-white rounded-3xl border border-dashed border-slate-200 p-8 text-center">
          <div class="text-5xl mb-3">🗺️</div>
          <h3 class="font-extrabold text-slate-800">Belum ada rencana</h3>
          <p class="text-sm text-slate-500 mt-1">Yuk mulai buat rencana pertamamu!</p>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
          <?php foreach ($listRencana as $d):
            $st = $statusMap[(int)$d['status']] ?? $statusMap[0];
            $lama = (int)$d['lama'];
            $tglMulai = date('d M Y', strtotime($d['tanggal']));
            $tglSelesai = date('d M Y', strtotime($d['tanggal'] . ' +' . ($lama - 1) . ' days'));
          ?>
            <div class="group bg-white rounded-2xl border border-slate-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition flex flex-col">
              <div class="relative h-36 overflow-hidden bg-slate-100">
                <?php if (!empty($d['foto']) && file_exists(__DIR__ . '/uploads/' . $d['foto'])): ?>
                  <img src="uploads/<?= e($d['foto']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                <?php else: ?>
                  <div class="w-full h-full grid place-items-center text-4xl bg-gradient-to-br from-sky-100 to-indigo-100">🏝️</div>
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                <span class="absolute top-2.5 right-2.5 inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-white/95 backdrop-blur text-[10px] font-bold shadow <?= $st['color'] ?>">
                  <span><?= $st['icon'] ?></span> <?= $st['label'] ?>
                </span>
              </div>
              <div class="p-4 flex-1 flex flex-col">
                <h3 class="font-extrabold text-sm text-slate-800 truncate"><?= e($d['judul']) ?></h3>
                <div class="text-[11px] text-slate-500 mt-1.5 flex items-center gap-1.5">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3 shrink-0"><path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2"/></svg>
                  <span class="truncate"><?= e($d['judul']) ?></span>
                </div>
                <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1.5">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3 shrink-0"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                  <span><?= $tglMulai ?> - <?= $tglSelesai ?></span>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <div class="flex -space-x-2">
                      <?php $colors = ['from-sky-400 to-sky-600','from-rose-400 to-rose-600','from-amber-400 to-amber-600'];
                      for ($i = 0; $i < min(3, $lama % 4 + 1); $i++): ?>
                        <span class="w-6 h-6 rounded-full bg-gradient-to-br <?= $colors[$i] ?> text-white text-[9px] font-bold grid place-items-center ring-2 ring-white"><?= chr(65 + $i) ?></span>
                      <?php endfor; ?>
                    </div>
                    <span class="text-[11px] text-slate-500"><?= $lama ?> anggota</span>
                  </div>
                  <a href="d_view.php?id=<?= (int)$d['id'] ?>" class="w-7 h-7 rounded-lg hover:bg-slate-100 grid place-items-center text-slate-400 transition">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    <?php elseif ($tab === 'tentang'): ?>
      <div class="bg-white rounded-3xl border border-slate-100 p-5 sm:p-6">
        <div class="flex items-start gap-3 mb-4">
          <span class="w-10 h-10 rounded-full bg-sky-100 text-sky-600 grid place-items-center shrink-0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
          </span>
          <div>
            <h2 class="font-extrabold text-lg text-slate-800">Tentang Saya</h2>
            <p class="text-xs text-slate-500 mt-0.5">Cerita singkat tentang perjalananmu.</p>
          </div>
        </div>
        <p class="text-sm text-slate-600 leading-relaxed">
          <?= !empty($user['tentang_saya']) ? nl2br(e($user['tentang_saya'])) : 'Belum ada deskripsi.' ?>
        </p>
        <button type="button" onclick="openModal('modalTentang')" class="mt-4 px-4 py-2 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold">Edit</button>
      </div>
    <?php else: ?>
      <div class="bg-white rounded-3xl border border-dashed border-slate-200 p-10 text-center">
        <div class="text-5xl mb-3">🚧</div>
        <h3 class="font-extrabold text-slate-800">Segera Hadir</h3>
        <p class="text-sm text-slate-500 mt-1">Fitur ini sedang dalam pengembangan.</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- SIDEBAR KANAN -->
  <aside class="space-y-4 min-w-0">

    <div class="bg-white rounded-3xl border border-slate-100 p-5">
      <div class="flex items-center gap-2 mb-4">
        <span class="w-8 h-8 rounded-full bg-sky-100 text-sky-600 grid place-items-center">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>
        </span>
        <h3 class="font-extrabold text-sm text-slate-800">Informasi Profil</h3>
      </div>

      <div class="space-y-1">
        <?php
        $infoItems = [
          ['Nama Lengkap',    $user['nama'],                'circle'],
          ['Email',           $user['email'],               'mail'],
          ['No. Telepon',     $user['no_telepon'] ?? '-',   'phone'],
          ['Jenis Kelamin',   $user['jenis_kelamin'] ?? '-','user'],
          ['Tanggal Lahir',   !empty($user['tanggal_lahir']) ? date('d F Y', strtotime($user['tanggal_lahir'])) : '-', 'calendar'],
          ['Lokasi Saat Ini', $user['lokasi'] ?? '-',       'map'],
        ];
        foreach ($infoItems as $item): ?>
          <div class="flex items-center gap-3 py-2.5 border-b border-slate-50 last:border-0">
            <span class="w-7 h-7 rounded-full bg-slate-50 text-slate-400 grid place-items-center shrink-0">
              <?php if ($item[2]==='circle'): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>
              <?php elseif ($item[2]==='mail'): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/></svg>
              <?php elseif ($item[2]==='phone'): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.37 1.9.72 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.35 1.85.59 2.81.72A2 2 0 0 1 22 16.92z"/></svg>
              <?php elseif ($item[2]==='user'): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>
              <?php elseif ($item[2]==='calendar'): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
              <?php else: ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2"/></svg>
              <?php endif; ?>
            </span>
            <div class="flex-1 min-w-0">
              <div class="text-[10px] text-slate-400 font-bold uppercase"><?= e($item[0]) ?></div>
              <div class="text-xs font-bold text-slate-700 truncate"><?= e($item[1]) ?></div>
            </div>
            <button onclick="openModal('modalInfo')" class="text-[11px] font-bold text-sky-600 hover:underline shrink-0 flex items-center gap-0.5">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="w-3 h-3"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
              Edit
            </button>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 p-5">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
          <span class="w-8 h-8 rounded-full bg-sky-100 text-sky-600 grid place-items-center">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
          </span>
          <h3 class="font-extrabold text-sm text-slate-800">Tentang Saya</h3>
        </div>
        <button onclick="openModal('modalTentang')" class="text-[11px] font-bold text-sky-600 hover:underline">Edit</button>
      </div>
      <p class="text-xs text-slate-600 leading-relaxed">
        <?= !empty($user['tentang_saya']) ? nl2br(e($user['tentang_saya'])) : 'Saya adalah seorang traveler yang suka menjelajahi alam, menciptakan kuliner lokal, dan mengabadikan setiap momen indah dalam perjalanan.' ?>
      </p>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 p-5">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
          <span class="w-8 h-8 rounded-full bg-sky-100 text-sky-600 grid place-items-center">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M3 3v18h18"/><path d="M7 14l4-4 4 4 5-5"/></svg>
          </span>
          <h3 class="font-extrabold text-sm text-slate-800">Statistik Perjalanan</h3>
        </div>
        <a href="rencana.php" class="text-[11px] font-bold text-sky-600 hover:underline">Lihat →</a>
      </div>
      <div class="grid grid-cols-3 gap-2 text-center">
        <div><div class="text-lg font-extrabold text-slate-800"><?= $totalRencana ?></div><div class="text-[10px] text-slate-400 font-bold uppercase">Rencana</div></div>
        <div><div class="text-lg font-extrabold text-slate-800"><?= $totalRencana ?></div><div class="text-[10px] text-slate-400 font-bold uppercase">Destinasi</div></div>
        <div><div class="text-lg font-extrabold text-slate-800">3</div><div class="text-[10px] text-slate-400 font-bold uppercase">Negara</div></div>
      </div>
    </div>

    <div class="relative rounded-3xl bg-gradient-to-br from-sky-100 via-sky-50 to-indigo-100 border border-sky-100 p-5 overflow-hidden">
      <div class="absolute -top-4 -right-4 text-6xl opacity-20">🌏</div>
      <div class="relative flex items-start gap-3">
        <div class="text-4xl shrink-0">🎒</div>
        <div class="flex-1">
          <h3 class="font-extrabold text-sm text-sky-800 leading-snug">Masih banyak destinasi yang menunggu untuk kamu jelajahi!</h3>
          <a href="rencana_buat.php" class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold shadow-md shadow-sky-200 transition">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-3.5 h-3.5"><path d="M2 12l19-7-7 19-3-8-9-4z"/></svg>
            Buat Rencana Baru
          </a>
        </div>
      </div>
    </div>
  </aside>
</div>

<!-- ============================================ -->
<!-- MODAL: EDIT INFO -->
<!-- ============================================ -->
<div id="modalInfo" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl">
    <div class="flex items-center justify-between p-5 border-b border-slate-100 sticky top-0 bg-white rounded-t-3xl">
      <h3 class="font-extrabold text-base text-slate-800">Edit Informasi Pribadi</h3>
      <button onclick="closeModal('modalInfo')" class="w-8 h-8 rounded-lg hover:bg-slate-100 grid place-items-center text-slate-400">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>

    <form method="post" class="p-5 space-y-4">
      <input type="hidden" name="act" value="update_info">

      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1.5">Nama Lengkap *</label>
        <input type="text" name="nama" required value="<?= e($user['nama']) ?>"
               class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1.5">Email *</label>
        <input type="email" name="email" required value="<?= e($user['email']) ?>"
               class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1.5">No. Telepon</label>
        <input type="tel" name="no_telepon" value="<?= e($user['no_telepon'] ?? '') ?>"
               class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1.5">Jenis Kelamin</label>
          <select name="jenis_kelamin" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm outline-none focus:border-sky-400">
            <option value="">-- Pilih --</option>
            <option value="Laki-laki" <?= ($user['jenis_kelamin'] ?? '') === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
            <option value="Perempuan" <?= ($user['jenis_kelamin'] ?? '') === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1.5">Tanggal Lahir</label>
          <input type="date" name="tanggal_lahir" value="<?= e($user['tanggal_lahir'] ?? '') ?>"
                 class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm outline-none focus:border-sky-400">
        </div>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1.5">Lokasi</label>
        <input type="text" name="lokasi" value="<?= e($user['lokasi'] ?? '') ?>" placeholder="Jakarta, Indonesia"
               class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100">
      </div>

      <div class="flex gap-2 pt-2">
        <button type="button" onclick="closeModal('modalInfo')" class="flex-1 px-4 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold">Batal</button>
        <button type="submit" class="flex-1 px-4 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold shadow-lg shadow-sky-200">Simpan</button>
      </div>
    </form>

    <div class="px-5 pb-5">
      <div class="border-t border-slate-100 pt-4">
        <h4 class="font-bold text-xs text-slate-500 mb-2">GANTI PASSWORD</h4>
        <form method="post" class="space-y-3">
          <input type="hidden" name="act" value="update_password">
          <input type="password" name="pass_lama" placeholder="Password lama" required
                 class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm outline-none focus:border-sky-400">
          <input type="password" name="pass_baru" placeholder="Password baru (min 6)" required minlength="6"
                 class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm outline-none focus:border-sky-400">
          <input type="password" name="pass_ulang" placeholder="Konfirmasi password baru" required minlength="6"
                 class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm outline-none focus:border-sky-400">
          <button type="submit" class="w-full px-4 py-3 rounded-xl bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold">Simpan Password</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ============================================ -->
<!-- MODAL: EDIT TENTANG -->
<!-- ============================================ -->
<div id="modalTentang" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl">
    <div class="flex items-center justify-between p-5 border-b border-slate-100">
      <h3 class="font-extrabold text-base text-slate-800">Edit Tentang Saya</h3>
      <button onclick="closeModal('modalTentang')" class="w-8 h-8 rounded-lg hover:bg-slate-100 grid place-items-center text-slate-400">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <form method="post" class="p-5">
      <input type="hidden" name="act" value="update_tentang">
      <textarea name="tentang_saya" rows="6" maxlength="500"
                class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100 resize-none"
                placeholder="Ceritakan tentang dirimu..."><?= e($user['tentang_saya'] ?? '') ?></textarea>
      <div class="flex gap-2 mt-4">
        <button type="button" onclick="closeModal('modalTentang')" class="flex-1 px-4 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold">Batal</button>
        <button type="submit" class="flex-1 px-4 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- ============================================ -->
<!-- MODAL: UPLOAD FOTO -->
<!-- ============================================ -->
<div id="modalFoto" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl">
    <div class="flex items-center justify-between p-5 border-b border-slate-100">
      <h3 class="font-extrabold text-base text-slate-800">Ganti Foto Profil</h3>
      <button onclick="closeModal('modalFoto')" class="w-8 h-8 rounded-lg hover:bg-slate-100 grid place-items-center text-slate-400">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <form method="post" enctype="multipart/form-data" class="p-5">
      <input type="hidden" name="act" value="upload_foto">
      <input type="file" name="foto" accept="image/*" required
             class="w-full text-sm text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-sky-50 file:text-sky-600 file:font-bold file:text-sm hover:file:bg-sky-100 file:cursor-pointer cursor-pointer rounded-xl border border-slate-200 bg-slate-50">
      <p class="text-[11px] text-slate-400 mt-2">Format: JPG, PNG, WEBP. Maks 2MB.</p>
      <div class="flex gap-2 mt-4">
        <button type="button" onclick="closeModal('modalFoto')" class="flex-1 px-4 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold">Batal</button>
        <button type="submit" class="flex-1 px-4 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold">Upload</button>
      </div>
    </form>
  </div>
</div>

<!-- ============================================ -->
<!-- MODAL: UPLOAD SAMPUL -->
<!-- ============================================ -->
<div id="modalSampul" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl">
    <div class="flex items-center justify-between p-5 border-b border-slate-100">
      <h3 class="font-extrabold text-base text-slate-800">Ganti Foto Sampul</h3>
      <button onclick="closeModal('modalSampul')" class="w-8 h-8 rounded-lg hover:bg-slate-100 grid place-items-center text-slate-400">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <form method="post" enctype="multipart/form-data" class="p-5">
      <input type="hidden" name="act" value="upload_sampul">
      <input type="file" name="sampul" accept="image/*" required
             class="w-full text-sm text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-sky-50 file:text-sky-600 file:font-bold file:text-sm hover:file:bg-sky-100 file:cursor-pointer cursor-pointer rounded-xl border border-slate-200 bg-slate-50">
      <p class="text-[11px] text-slate-400 mt-2">Format: JPG, PNG, WEBP. Maks 5MB.</p>
      <div class="flex gap-2 mt-4">
        <button type="button" onclick="closeModal('modalSampul')" class="flex-1 px-4 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold">Batal</button>
        <button type="submit" class="flex-1 px-4 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold">Upload</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) {
  const m = document.getElementById(id);
  m.classList.remove('hidden');
  m.classList.add('flex');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  const m = document.getElementById(id);
  m.classList.add('hidden');
  m.classList.remove('flex');
  document.body.style.overflow = '';
}
// Tutup modal kalau klik background
document.querySelectorAll('[id^="modal"]').forEach(m => {
  m.addEventListener('click', e => {
    if (e.target === m) closeModal(m.id);
  });
});
// ESC untuk tutup
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('[id^="modal"]').forEach(m => {
      if (!m.classList.contains('hidden')) closeModal(m.id);
    });
  }
});
</script>

<?php include __DIR__ . '/foot.php'; ?>