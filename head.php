<?php
require_once __DIR__ . '/func.php';

if (!function_exists('menu_links')) {
  function menu_links($cur) {
    $items = [
      ['index.php',        'Beranda',       '<path d="m3 10 9-7 9 7v10a2 2 0 0 1-2 2h-4v-6h-6v6H5a2 2 0 0 1-2-2z"/>'],
      ['rencana.php',      'Rencana Saya',  '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'],
      ['rencana_buat.php', 'Buat Rencana',  '<path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7z"/>'],
      ['dest.php',         'Destinasi',     '<path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/>'],
      ['profil.php',       'Profil',        '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/>'],
    ];
    foreach ($items as $it) {
      $active = ($it[0] === $cur);
      $cls = $active
        ? 'flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold bg-sky-500 text-white shadow-lg shadow-sky-200'
        : 'flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100';
      echo "<a href=\"{$it[0]}\" class=\"$cls\">";
      echo '<span class="w-5 h-5 shrink-0"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">' . $it[2] . '</svg></span>';
      echo htmlspecialchars($it[1], ENT_QUOTES, 'UTF-8');
      echo '</a>';
    }
  }
}

$cur = basename($_SERVER['PHP_SELF']);
$nm  = $_SESSION['nama'] ?? 'User';
$ini = strtoupper(substr($nm, 0, 1));

$fotoUser = '';
if (!empty($_SESSION['uid']) && isset($conn)) {
    $sFoto = $conn->prepare("SELECT foto FROM users WHERE id=?");
    $sFoto->bind_param('i', $_SESSION['uid']);
    $sFoto->execute();
    $rowFoto = $sFoto->get_result()->fetch_assoc();
    $sFoto->close();
    if (!empty($rowFoto['foto']) && file_exists(__DIR__ . '/uploads/avatar/' . $rowFoto['foto'])) {
        $fotoUser = 'uploads/avatar/' . $rowFoto['foto'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#0ea5e9">
<link rel="icon" type="image/svg+xml" href="logo.svg">
<title><?= isset($title) ? e($title).' — Rencana Liburan' : 'Rencana Liburan' ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: { maxWidth: { '8xl': '1400px', '9xl': '1600px' } } } }
</script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  html, body { overflow-x: hidden; }
  body { font-family: 'Plus Jakarta Sans', sans-serif; }
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#f7f9fc] min-h-screen flex flex-col">

<header class="sticky top-0 z-40 bg-white border-b border-slate-100">
  <div class="w-full max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 h-16 flex items-center gap-3 sm:gap-6">

    <!-- LOGO -->
    <div class="flex items-center gap-3 shrink-0">
      <a href="index.php" class="flex items-center gap-2.5">
      <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 rounded-xl shrink-0 shadow-md shadow-sky-200">
  <defs><linearGradient id="logoE" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#38bdf8"/><stop offset="100%" stop-color="#0369a1"/></linearGradient></defs>
  <rect width="100" height="100" rx="22" fill="url(#logoE)"/>
  <circle cx="42" cy="48" r="14" fill="#fff"/>
  <path d="M32 42 Q22 38 18 48 Q24 46 28 50 Q22 50 20 58 Q26 54 32 56 Q30 62 34 66 Q34 58 38 54 Q36 62 40 68 Q40 60 42 54 Z" fill="#fff"/>
  <path d="M50 78 Q62 62 72 72 L82 62 L94 78 Z" fill="#fff"/>
  <ellipse cx="76" cy="52" rx="9" ry="4" fill="#fff"/>
  <ellipse cx="70" cy="54" rx="6" ry="3" fill="#fff"/>
  <path d="M56 44 L88 24 L74 58 L66 48 Z" fill="#fff"/>
  <path d="M56 44 L74 58 L64 62 L66 48 Z" fill="#fff" opacity="0.85"/>
</svg>
        <div class="leading-tight">
          <div class="font-extrabold text-base">Rencana<span class="text-sky-500">Liburan</span></div>
          <div class="hidden sm:block text-[10px] text-slate-400 font-medium">Jelajah, Rencanakan, Wujudkan</div>
        </div>
      </a>
    </div>

    <div class="flex-1"></div>

    <!-- NOTIF + PROFIL -->
    <div class="flex items-center gap-1 sm:gap-2 shrink-0">
      <button type="button" class="relative p-2 rounded-lg hover:bg-slate-100">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5 text-slate-500"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500"></span>
      </button>
      <div class="relative">
        <button type="button" onclick="toggleUser(event)" class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-full hover:bg-slate-100 transition">
          <?php if ($fotoUser): ?>
            <img src="<?= e($fotoUser) ?>?v=<?= time() ?>" alt="<?= e($nm) ?>" class="w-8 h-8 rounded-full object-cover shrink-0 ring-2 ring-sky-100">
          <?php else: ?>
            <span class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-500 to-indigo-500 text-white grid place-items-center font-bold text-sm shrink-0"><?= $ini ?></span>
          <?php endif; ?>
          <span class="hidden sm:block text-sm font-semibold text-slate-700 max-w-[100px] truncate"><?= e($nm) ?></span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="hidden sm:block w-4 h-4 text-slate-400"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 p-2 z-50">
          <div class="px-3 py-2 text-xs text-slate-500 border-b border-slate-100 mb-1 flex items-center gap-2">
            <?php if ($fotoUser): ?>
              <img src="<?= e($fotoUser) ?>?v=<?= time() ?>" alt="" class="w-8 h-8 rounded-full object-cover shrink-0">
            <?php else: ?>
              <span class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-500 to-indigo-500 text-white grid place-items-center font-bold text-xs shrink-0"><?= $ini ?></span>
            <?php endif; ?>
            <div class="min-w-0">Masuk sebagai<br><b class="text-slate-700 truncate block"><?= e($nm) ?></b></div>
          </div>
          <a href="profil.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>
            Profil Saya
          </a>
          <a href="logout.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-rose-600 hover:bg-rose-50">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg>
            Logout
          </a>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="flex-1 w-full max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12">
  <div class="grid grid-cols-1 lg:grid-cols-[220px_1fr] xl:grid-cols-[230px_1fr] gap-4 lg:gap-6 xl:gap-7 py-4 sm:py-6 lg:py-7 pb-28 lg:pb-8">

    <aside class="hidden lg:block">
      <div class="sticky top-20">
        <nav class="space-y-1"><?php menu_links($cur); ?></nav>

        <div class="mt-6 rounded-3xl overflow-hidden border border-sky-100 relative bg-gradient-to-b from-[#c5e8f7] via-[#a8dcf0] to-[#7ec8e3]">
          <svg viewBox="0 0 240 260" class="w-full" preserveAspectRatio="xMidYMid slice">
            <defs>
              <linearGradient id="sb-sky" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#bae6fd"/><stop offset="100%" stop-color="#38bdf8"/>
              </linearGradient>
              <linearGradient id="sb-mount1" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#e0f2fe"/><stop offset="100%" stop-color="#60a5fa"/>
              </linearGradient>
              <linearGradient id="sb-mount2" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#bae6fd"/><stop offset="100%" stop-color="#0ea5e9"/>
              </linearGradient>
            </defs>
            <rect width="240" height="260" fill="url(#sb-sky)"/>
            <circle cx="200" cy="30" r="12" fill="#fef9c3"/>
            <ellipse cx="50" cy="35" rx="22" ry="6" fill="#ffffff" opacity=".85"/>
            <ellipse cx="68" cy="33" rx="14" ry="5" fill="#ffffff" opacity=".85"/>
            <path d="M0 150 L50 70 L90 120 L140 55 L190 115 L240 90 L240 180 L0 180 Z" fill="url(#sb-mount1)" opacity=".9"/>
            <path d="M0 175 L40 130 L80 155 L120 110 L170 155 L220 120 L240 145 L240 200 L0 200 Z" fill="url(#sb-mount2)"/>
            <g transform="translate(105 90)"><path d="M0 0 L10 2 L0 4 L2 2 Z" fill="#fff"/></g>
          </svg>
          <div class="absolute inset-x-0 bottom-0 bg-white/95 backdrop-blur px-3 py-3 text-center">
            <p class="text-[11px] italic text-sky-700 font-bold leading-snug">Liburan hari ini,<br>cerita untuk nanti.</p>
            <div class="text-rose-400 text-xs mt-0.5">♥</div>
          </div>
        </div>
      </div>
    </aside>

    <main class="min-w-0">