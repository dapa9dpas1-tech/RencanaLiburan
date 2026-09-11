<?php
require_once __DIR__ . '/func.php';
must_login();
$uid = (int)$_SESSION['uid'];

$act = $_POST['act'] ?? '';

// ==========================================
// UPDATE INFORMASI PRIBADI
// ==========================================
if ($act === 'update_info') {
    $nama          = trim($_POST['nama'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? null;
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
    $no_telepon    = trim($_POST['no_telepon'] ?? '');
    $lokasi        = trim($_POST['lokasi'] ?? '');

    if ($nama === '' || $email === '') {
        header('Location: profil.php?err=' . urlencode('Nama dan email wajib diisi.')); exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: profil.php?err=' . urlencode('Format email tidak valid.')); exit;
    }

    $chk = $conn->prepare("SELECT id FROM users WHERE email=? AND id<>?");
    $chk->bind_param('si', $email, $uid);
    $chk->execute(); $chk->store_result();
    if ($chk->num_rows > 0) {
        $chk->close();
        header('Location: profil.php?err=' . urlencode('Email sudah digunakan pengguna lain.')); exit;
    }
    $chk->close();

    $tl = $tanggal_lahir ?: null;
    $jk = $jenis_kelamin ?: null;
    $nt = $no_telepon ?: null;
    $lk = $lokasi ?: null;

    $up = $conn->prepare("UPDATE users SET nama=?, email=?, tanggal_lahir=?, jenis_kelamin=?, no_telepon=?, lokasi=? WHERE id=?");
    $up->bind_param('ssssssi', $nama, $email, $tl, $jk, $nt, $lk, $uid);

    if ($up->execute()) {
        $_SESSION['nama'] = $nama;
        header('Location: profil.php?ok=' . urlencode('Informasi pribadi berhasil diperbarui.')); exit;
    } else {
        header('Location: profil.php?err=' . urlencode('Gagal menyimpan: ' . $conn->error)); exit;
    }
}

// ==========================================
// UPDATE TENTANG SAYA
// ==========================================
if ($act === 'update_tentang') {
    $tentang = trim($_POST['tentang_saya'] ?? '');

    $up = $conn->prepare("UPDATE users SET tentang_saya=? WHERE id=?");
    $up->bind_param('si', $tentang, $uid);

    if ($up->execute()) {
        header('Location: profil.php?ok=' . urlencode('Tentang saya berhasil diperbarui.')); exit;
    } else {
        header('Location: profil.php?err=' . urlencode('Gagal menyimpan.')); exit;
    }
}

// ==========================================
// GANTI PASSWORD
// ==========================================
if ($act === 'update_password') {
    $lama  = $_POST['pass_lama'] ?? '';
    $baru  = $_POST['pass_baru'] ?? '';
    $ulang = $_POST['pass_ulang'] ?? '';

    if ($lama === '' || $baru === '' || $ulang === '') {
        header('Location: profil.php?err=' . urlencode('Semua kolom password wajib diisi.')); exit;
    }
    if (strlen($baru) < 6) {
        header('Location: profil.php?err=' . urlencode('Password baru minimal 6 karakter.')); exit;
    }
    if ($baru !== $ulang) {
        header('Location: profil.php?err=' . urlencode('Konfirmasi password tidak cocok.')); exit;
    }

    // Ambil password lama
    $s = $conn->prepare("SELECT password FROM users WHERE id=?");
    $s->bind_param('i', $uid); $s->execute();
    $row = $s->get_result()->fetch_assoc(); $s->close();

    $hash = $row['password'] ?? '';
    $valid = password_verify($lama, $hash) || $lama === $hash;

    if (!$valid) {
        header('Location: profil.php?err=' . urlencode('Password lama salah.')); exit;
    }

    $hashBaru = password_hash($baru, PASSWORD_DEFAULT);
    $up = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $up->bind_param('si', $hashBaru, $uid);
    $up->execute();
    $up->close();

    header('Location: profil.php?ok=' . urlencode('Password berhasil diubah.')); exit;
}

header('Location: profil.php');
exit;