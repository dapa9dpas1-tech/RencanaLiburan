<?php
require_once __DIR__ . '/cfg.php';

function login_ok(): bool { return isset($_SESSION['uid']); }
function must_login(): void {
    if (!login_ok()) { flash('e','Silakan login dulu.'); header('Location: login.php'); exit; }
}
function flash(string $t, string $m): void { $_SESSION['f'] = ['t'=>$t,'m'=>$m]; }
function get_flash(): ?array {
    if (empty($_SESSION['f'])) return null;
    $x = $_SESSION['f']; unset($_SESSION['f']); return $x;
}
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rp($n): string { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function tgl($d): string {
    $b = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $ts = strtotime($d);
    return date('d', $ts) . ' ' . $b[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}
function up_foto(array $f, ?string &$err = null): ?string {
    if (empty($f['tmp_name']) || $f['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK) { $err = 'Upload gagal.'; return null; }
    if ($f['size'] > 2*1024*1024) { $err = 'Maks 2MB.'; return null; }
    $ok = ['jpg','jpeg','png','webp','gif'];
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $ok, true)) { $err = 'Format harus JPG/PNG/WEBP/GIF.'; return null; }
    $dir = __DIR__ . '/uploads';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $n = uniqid('d_', true) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $dir.'/'.$n)) { $err = 'Gagal simpan file.'; return null; }
    return $n;
}
function del_foto(?string $n): void {
    if ($n && file_exists(__DIR__.'/uploads/'.$n)) @unlink(__DIR__.'/uploads/'.$n);
}
function get_dest(mysqli $c, int $id, int $uid): ?array {
    $s = $c->prepare("SELECT * FROM destinasi WHERE id=? AND user_id=?");
    $s->bind_param('ii', $id, $uid); $s->execute();
    $r = $s->get_result()->fetch_assoc(); $s->close();
    return $r ?: null;
}