<?php
require_once __DIR__ . '/func.php';

if (login_ok()) { header('Location: index.php'); exit; }

$err = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email === '' || $pass === '') {
        $err = 'Email dan password wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Format email tidak valid.';
    } else {
        $s = $conn->prepare("SELECT id, nama, email, password FROM users WHERE email=? LIMIT 1");
        $s->bind_param('s', $email);
        $s->execute();
        $u = $s->get_result()->fetch_assoc();
        $s->close();

        if (!$u) {
            $err = 'Email tidak terdaftar.';
        } else {
            $hash = $u['password'];
            $valid = password_verify($pass, $hash) || $pass === $hash;

            if (!$valid) {
                $err = 'Password salah.';
            } else {
                $_SESSION['uid']   = (int)$u['id'];
                $_SESSION['nama']  = $u['nama'];
                $_SESSION['email'] = $u['email'];

                if ($pass === $hash) {
                    $newHash = password_hash($pass, PASSWORD_DEFAULT);
                    $up = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                    $up->bind_param('si', $newHash, $u['id']);
                    $up->execute();
                    $up->close();
                }
                header('Location: index.php');
                exit;
            }
        }
    }
}

$title = 'Login';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#0ea5e9">
<link rel="icon" type="image/svg+xml" href="logo.svg">
<title>Login — Rencana Liburan</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; }
  body { font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; background: #f1f5f9; }
  .login-wrapper { min-height: 100vh; display: block; }
  .login-hero { position: relative; height: 288px; overflow: hidden; }
  .login-hero img.bg { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
  .login-hero .overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,.4) 0%, rgba(0,0,0,.2) 40%, rgba(0,0,0,.7) 100%); }
  .login-form-wrap { background: #fff; border-top-left-radius: 32px; border-top-right-radius: 32px; margin-top: -24px; position: relative; z-index: 10; padding: 32px 24px; display: flex; align-items: center; justify-content: center; }
  .login-form-inner { width: 100%; max-width: 400px; }
  @media (min-width: 900px) {
    .login-wrapper { display: grid; grid-template-columns: 1fr 1fr; min-height: 100vh; }
    .login-hero { height: 100vh; position: sticky; top: 0; }
    .login-hero .overlay { background: linear-gradient(135deg, rgba(7,89,133,.85) 0%, rgba(3,105,161,.65) 50%, rgba(56,189,248,.35) 100%); }
    .login-form-wrap { border-radius: 0; margin-top: 0; min-height: 100vh; padding: 40px 60px; }
    .login-form-inner { max-width: 420px; }
  }
</style>
</head>
<body>

<div class="login-wrapper">

  <div class="login-hero">
    <img class="bg" src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=80" alt="Beach">
    <div class="overlay"></div>

    <a href="index.php" style="position:absolute; top:24px; left:24px; display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,.15); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,.3); border-radius:999px; padding:6px 16px 6px 6px; text-decoration:none;">
    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" style="width:28px; height:28px; border-radius:999px; flex-shrink:0;">
  <defs><linearGradient id="logoA" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#38bdf8"/><stop offset="100%" stop-color="#0369a1"/></linearGradient></defs>
  <rect width="100" height="100" rx="22" fill="url(#logoA)"/>
  <circle cx="42" cy="48" r="14" fill="#fff"/>
  <path d="M32 42 Q22 38 18 48 Q24 46 28 50 Q22 50 20 58 Q26 54 32 56 Q30 62 34 66 Q34 58 38 54 Q36 62 40 68 Q40 60 42 54 Z" fill="#fff"/>
  <path d="M50 78 Q62 62 72 72 L82 62 L94 78 Z" fill="#fff"/>
  <ellipse cx="76" cy="52" rx="9" ry="4" fill="#fff"/>
  <ellipse cx="70" cy="54" rx="6" ry="3" fill="#fff"/>
  <path d="M56 44 L88 24 L74 58 L66 48 Z" fill="#fff"/>
  <path d="M56 44 L74 58 L64 62 L66 48 Z" fill="#fff" opacity="0.85"/>
</svg>
      <span style="color:#fff; font-weight:800; font-size:14px; white-space:nowrap;">Rencana <span style="color:#7dd3fc;">Liburan</span></span>
    </a>

    <div style="position:absolute; bottom:32px; left:24px; right:24px; color:#fff;">
      <h1 style="font-size:32px; font-weight:800; line-height:1.1; margin:0 0 8px 0; text-shadow:0 2px 8px rgba(0,0,0,.4);">Rencanakan<br>Liburan Impianmu</h1>
      <p style="font-size:13px; line-height:1.6; margin:0; color:rgba(255,255,255,.9); max-width:300px;">Temukan destinasi terbaik, atur jadwal, dan buat pengalaman liburanmu jadi lebih mudah!</p>
    </div>
  </div>

  <div class="login-form-wrap">
    <div class="login-form-inner">

      <div style="display:flex; justify-content:center;">
        <div style="width:64px; height:64px; border-radius:16px; background:linear-gradient(135deg,#38bdf8,#0284c7); display:grid; place-items:center; box-shadow:0 8px 24px rgba(56,189,248,.35);">
         <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" style="width:44px; height:44px;">
  <defs><linearGradient id="logoB" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#38bdf8"/><stop offset="100%" stop-color="#0369a1"/></linearGradient></defs>
  <rect width="100" height="100" rx="22" fill="url(#logoB)"/>
  <circle cx="42" cy="48" r="14" fill="#fff"/>
  <path d="M32 42 Q22 38 18 48 Q24 46 28 50 Q22 50 20 58 Q26 54 32 56 Q30 62 34 66 Q34 58 38 54 Q36 62 40 68 Q40 60 42 54 Z" fill="#fff"/>
  <path d="M50 78 Q62 62 72 72 L82 62 L94 78 Z" fill="#fff"/>
  <ellipse cx="76" cy="52" rx="9" ry="4" fill="#fff"/>
  <ellipse cx="70" cy="54" rx="6" ry="3" fill="#fff"/>
  <path d="M56 44 L88 24 L74 58 L66 48 Z" fill="#fff"/>
  <path d="M56 44 L74 58 L64 62 L66 48 Z" fill="#fff" opacity="0.85"/>
</svg>
        </div>
      </div>

      <h2 style="text-align:center; margin:20px 0 0 0; font-size:30px; font-weight:800; color:#1e293b;">Login</h2>
      <p style="text-align:center; margin:8px 0 0 0; font-size:14px; color:#64748b;">Selamat datang kembali 👋</p>
      <p style="text-align:center; margin:4px 0 0 0; font-size:14px; color:#64748b;">Masuk untuk melanjutkan perjalananmu.</p>

      <?php if ($err): ?>
        <div style="margin-top:20px; display:flex; gap:8px; align-items:flex-start; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; font-size:14px; border-radius:16px; padding:12px 16px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px; height:20px; flex-shrink:0; margin-top:2px;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
          <span><?= e($err) ?></span>
        </div>
      <?php endif; ?>

      <form method="post" style="margin-top:24px; display:flex; flex-direction:column; gap:16px;">

        <div style="position:relative;">
          <span style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#94a3b8; pointer-events:none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:20px; height:20px;"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/></svg>
          </span>
          <input type="email" name="email" required autofocus value="<?= e($email) ?>" placeholder="Email"
                 style="width:100%; padding:16px 16px 16px 48px; border-radius:16px; border:1px solid #e2e8f0; background:#fff; font-size:14px; color:#334155; outline:none; font-family:inherit; transition:.2s;"
                 onfocus="this.style.borderColor='#38bdf8'; this.style.boxShadow='0 0 0 4px rgba(56,189,248,.15)';"
                 onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
        </div>

        <div style="position:relative;">
          <span style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#94a3b8; pointer-events:none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:20px; height:20px;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input type="password" name="password" id="passInput" required placeholder="Password"
                 style="width:100%; padding:16px 48px 16px 48px; border-radius:16px; border:1px solid #e2e8f0; background:#fff; font-size:14px; color:#334155; outline:none; font-family:inherit; transition:.2s;"
                 onfocus="this.style.borderColor='#38bdf8'; this.style.boxShadow='0 0 0 4px rgba(56,189,248,.15)';"
                 onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
          <button type="button" onclick="togglePassword()" style="position:absolute; right:16px; top:50%; transform:translateY(-50%); background:none; border:none; color:#94a3b8; cursor:pointer; padding:0;">
            <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:20px; height:20px;"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>

        <button type="submit" style="width:100%; margin-top:8px; display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:16px 24px; border-radius:16px; background:#0ea5e9; color:#fff; font-size:16px; font-weight:800; border:none; cursor:pointer; font-family:inherit; box-shadow:0 8px 24px rgba(14,165,233,.35); transition:.2s;"
                onmouseover="this.style.background='#0284c7';" onmouseout="this.style.background='#0ea5e9';">
          Login
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px; height:16px;"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </button>
      </form>

      <div style="position:relative; margin:28px 0;">
        <div style="position:absolute; inset:0; display:flex; align-items:center;"><div style="width:100%; border-top:1px solid #e2e8f0;"></div></div>
        <div style="position:relative; display:flex; justify-content:center;"><span style="background:#fff; padding:0 16px; font-size:12px; color:#94a3b8;">atau</span></div>
      </div>

      <p style="text-align:center; font-size:14px; color:#64748b; margin:0 0 16px 0;">
        Belum punya akun?
        <a href="reg.php" style="color:#0ea5e9; font-weight:800; text-decoration:none; margin-left:4px;">Daftar di sini</a>
      </p>

    </div>
  </div>
</div>

<script>
function togglePassword() {
  const input = document.getElementById('passInput');
  const icon  = document.getElementById('eyeIcon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
  } else {
    input.type = 'password';
    icon.innerHTML = '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>';
  }
}
</script>

</body>
</html>