<?php
require_once __DIR__ . '/func.php';
must_login();
$uid = (int)$_SESSION['uid'];

// ============ DEBUG MODE ============
$debug = isset($_GET['debug']);

if ($debug) {
    echo "<pre style='background:#f0f0f0;padding:20px;'>";
    echo "UID: $uid\n";
    echo "FILES: " . print_r($_FILES, true);
    echo "</pre>";
}

// ============ VALIDASI ============
if (empty($_FILES['foto']['name'])) {
    if ($debug) die('Tidak ada file yang diupload.');
    header('Location: profil.php'); exit;
}

if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    $errMsg = 'Error upload kode: ' . $_FILES['foto']['error'];
    if ($debug) die($errMsg);
    header('Location: profil.php'); exit;
}

$allowed = ['jpg', 'jpeg', 'png', 'webp'];
$ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    if ($debug) die('Format tidak diizinkan: ' . $ext);
    header('Location: profil.php'); exit;
}

if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
    if ($debug) die('File terlalu besar: ' . $_FILES['foto']['size'] . ' bytes');
    header('Location: profil.php'); exit;
}

// ============ BUAT FOLDER ============
$dir = __DIR__ . '/uploads/avatar/';

if (!is_dir($dir)) {
    if (!mkdir($dir, 0777, true)) {
        if ($debug) die('Gagal buat folder: ' . $dir);
        header('Location: profil.php'); exit;
    }
}

if (!is_writable($dir)) {
    if ($debug) die('Folder tidak writable: ' . $dir);
    header('Location: profil.php'); exit;
}

// ============ UPLOAD ============
$namaBaru = 'avatar_' . $uid . '_' . time() . '.' . $ext;
$targetFile = $dir . $namaBaru;

if (!move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
    if ($debug) die('Gagal move_uploaded_file. Cek permission folder.');
    header('Location: profil.php'); exit;
}

if ($debug) {
    echo "<div style='background:#d1fae5;padding:20px;margin-top:20px;'>";
    echo "✅ Berhasil upload: $namaBaru<br>";
    echo "Path: $targetFile<br>";
    echo "<a href='profil.php'>Kembali ke Profil</a>";
    echo "</div></pre>";
    exit;
}

// ============ HAPUS FOTO LAMA ============
$s = $conn->prepare("SELECT foto FROM users WHERE id=?");
$s->bind_param('i', $uid); $s->execute();
$old = $s->get_result()->fetch_assoc()['foto'] ?? '';
$s->close();

if ($old && file_exists($dir . $old)) {
    @unlink($dir . $old);
}

// ============ UPDATE DB ============
$up = $conn->prepare("UPDATE users SET foto=? WHERE id=?");
$up->bind_param('si', $namaBaru, $uid);
$up->execute();
$up->close();

// ============ REDIRECT ============
header('Location: profil.php?ok_foto=1');
exit;