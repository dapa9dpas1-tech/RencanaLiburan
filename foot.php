    </main>
  </div>
</div>

<div id="drawer" class="hidden fixed inset-0 z-[60] lg:hidden">
  <div class="absolute inset-0 bg-black/50" onclick="closeDrawer()"></div>
  <aside class="relative w-72 max-w-[80%] h-full bg-white shadow-2xl flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-slate-100 shrink-0">
      <div class="flex items-center gap-2 font-extrabold">
        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-500 to-indigo-500 grid place-items-center text-white">
          <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M2 12l19-7-7 19-3-8-9-4z"/></svg>
        </span>
        <span>Rencana<span class="text-sky-500">Liburan</span></span>
      </div>
      <button type="button" onclick="closeDrawer()" class="p-2 rounded-lg hover:bg-slate-100">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-5 h-5"><path d="M6 6l12 12M6 18L18 6"/></svg>
      </button>
    </div>
    <nav class="p-3 space-y-1 overflow-y-auto flex-1">
      <?php menu_links($cur); ?>
    </nav>
  </aside>
</div>

<div id="fabMenu" class="hidden fixed inset-0 z-[55] lg:hidden">
  <div class="absolute inset-0 bg-black/50" onclick="toggleFab()"></div>
  <div class="absolute bottom-28 left-1/2 -translate-x-1/2 w-[85%] max-w-sm bg-white rounded-3xl shadow-2xl p-3 space-y-1">
    <a href="d_add.php" class="flex items-center gap-3 p-3 rounded-2xl hover:bg-slate-50 transition">
      <span class="w-10 h-10 rounded-xl bg-sky-50 grid place-items-center text-sky-500">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
      </span>
      <div class="flex-1">
        <div class="font-bold text-sm text-slate-800">Tambah Destinasi</div>
        <div class="text-xs text-slate-500">Buat tujuan liburan baru</div>
      </div>
    </a>
    <a href="rencana.php" class="flex items-center gap-3 p-3 rounded-2xl hover:bg-slate-50 transition">
      <span class="w-10 h-10 rounded-xl bg-indigo-50 grid place-items-center text-indigo-500">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
      </span>
      <div class="flex-1">
        <div class="font-bold text-sm text-slate-800">Tambah Rencana</div>
        <div class="text-xs text-slate-500">Susun jadwal perjalanan</div>
      </div>
    </a>
  </div>
</div>

<nav class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-slate-200" style="box-shadow:0 -4px 20px rgba(0,0,0,.06)">
  <div class="grid grid-cols-5 relative max-w-md mx-auto pt-2 pb-2">
    <a href="index.php" class="flex flex-col items-center gap-0.5 py-1">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5 <?= $cur==='index.php' ? 'text-sky-500' : 'text-slate-400' ?>"><path d="m3 10 9-7 9 7v10a2 2 0 0 1-2 2h-4v-6h-6v6H5a2 2 0 0 1-2-2z"/></svg>
      <span class="text-[10px] font-bold <?= $cur==='index.php' ? 'text-sky-500' : 'text-slate-400' ?>">Home</span>
    </a>
    <a href="rencana.php" class="flex flex-col items-center gap-0.5 py-1">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5 <?= $cur==='rencana.php' ? 'text-sky-500' : 'text-slate-400' ?>"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
      <span class="text-[10px] font-bold <?= $cur==='rencana.php' ? 'text-sky-500' : 'text-slate-400' ?>">Rencana</span>
    </a>
    <div></div>
    <a href="dest.php" class="flex flex-col items-center gap-0.5 py-1">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5 <?= $cur==='dest.php' ? 'text-sky-500' : 'text-slate-400' ?>"><path d="M12 21s-7-6.5-7-12a7 7 0 1 1 14 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
      <span class="text-[10px] font-bold <?= $cur==='dest.php' ? 'text-sky-500' : 'text-slate-400' ?>">Destinasi</span>
    </a>
    <a href="profil.php" class="flex flex-col items-center gap-0.5 py-1">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5 <?= $cur==='profil.php' ? 'text-sky-500' : 'text-slate-400' ?>"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>
      <span class="text-[10px] font-bold <?= $cur==='profil.php' ? 'text-sky-500' : 'text-slate-400' ?>">Profil</span>
    </a>
    <button type="button" onclick="toggleFab(event)" class="absolute left-1/2 -translate-x-1/2 -top-4 w-14 h-14 rounded-full bg-gradient-to-br from-sky-500 to-indigo-500 text-white grid place-items-center shadow-xl shadow-sky-300 active:scale-95 transition z-10">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-7 h-7"><path d="M12 5v14M5 12h14"/></svg>
    </button>
  </div>
</nav>

<footer class="hidden lg:block text-center text-xs text-slate-400 py-6 px-4">
  &copy; <?= date('Y') ?> <b>Rencana Liburan</b> — Semua hak dilindungi
</footer>

<script>
function openDrawer(){var d=document.getElementById('drawer');if(d){d.classList.remove('hidden');document.body.style.overflow='hidden';}}
function closeDrawer(){var d=document.getElementById('drawer');if(d){d.classList.add('hidden');document.body.style.overflow='';}}
function toggleUser(ev){if(ev)ev.stopPropagation();var m=document.getElementById('userMenu');if(m)m.classList.toggle('hidden');}
function toggleFab(ev){if(ev)ev.stopPropagation();var m=document.getElementById('fabMenu');if(m)m.classList.toggle('hidden');}
document.addEventListener('click',function(ev){var m=document.getElementById('userMenu');if(m&&!m.classList.contains('hidden')&&!m.contains(ev.target))m.classList.add('hidden');});
window.addEventListener('resize',function(){if(window.innerWidth>=1024)closeDrawer();});
</script>
</body>
</html>