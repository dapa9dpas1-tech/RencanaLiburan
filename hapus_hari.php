<?php
require_once __DIR__ . '/func.php';
must_login();
$uid = (int)$_SESSION['uid'];

$destinasi_id = (int)($_GET['destinasi_id'] ?? 0);
$hari         = (int)($_GET['hari'] ?? 0);
$debug        = isset($_GET['debug']);

if (!$destinasi_id || !$hari) {
    if ($debug) die('Parameter destinasi_id atau hari kosong.');
    header('Location: dest.php'); exit;
}

// Cek kepemilikan
$s = $conn->prepare("SELECT * FROM destinasi WHERE id=? AND user_id=?");
$s->bind_param('ii', $destinasi_id, $uid);
$s->execute();
$dest = $s->get_result()->fetch_assoc();
$s->close();

if (!$dest) {
    if ($debug) die('Destinasi tidak ditemukan atau bukan milik Anda. destinasi_id='.$destinasi_id.' uid='.$uid);
    header('Location: dest.php'); exit;
}

if ($debug) {
    echo "<h3>DEBUG MODE</h3>";
    echo "Destinasi ID: $destinasi_id<br>";
    echo "Hari yang dihapus: $hari<br>";
    echo "Lama lama: " . $dest['lama'] . "<br>";
    echo "Budget lama: " . $dest['budget'] . "<br><hr>";
}

// 1. Hapus kegiatan di hari tersebut
$del = $conn->prepare("DELETE FROM rencana WHERE destinasi_id=? AND hari=?");
$del->bind_param('ii', $destinasi_id, $hari);
$del->execute();
$aff = $del->affected_rows;
$del->close();

if ($debug) echo "DELETE rencana: $aff baris terhapus<br>";

// 2. Geser hari setelahnya
$geser = $conn->prepare("UPDATE rencana SET hari = hari - 1 WHERE destinasi_id=? AND hari > ?");
$geser->bind_param('ii', $destinasi_id, $hari);
$geser->execute();
$aff2 = $geser->affected_rows;
$geser->close();

if ($debug) echo "UPDATE geser hari: $aff2 baris terupdate<br>";

// 3. Hitung ulang
$lamaLama   = (int)$dest['lama'];
$budgetLama = (float)$dest['budget'];
$budgetPerHari = $lamaLama > 0 ? $budgetLama / $lamaLama : 0;
$lamaBaru = max(1, $lamaLama - 1);
$budgetBaru = $budgetPerHari * $lamaBaru;

if ($debug) {
    echo "Budget per hari: Rp " . number_format($budgetPerHari) . "<br>";
    echo "Lama baru: $lamaBaru hari<br>";
    echo "Budget baru: Rp " . number_format($budgetBaru) . "<br><hr>";
}

// 4. Update destinasi
$up = $conn->prepare("UPDATE destinasi SET lama=?, budget=? WHERE id=?");
$up->bind_param('idi', $lamaBaru, $budgetBaru, $destinasi_id);
$up->execute();
$aff3 = $up->affected_rows;
$up->close();

if ($debug) {
    echo "UPDATE destinasi: $aff3 baris terupdate<br><hr>";
    echo "<b>SELESAI!</b> <a href='d_view.php?id=$destinasi_id'>Kembali ke Detail</a>";
    exit;
}

// Redirect
header('Location: d_view.php?id=' . $destinasi_id . '&tab=rencana');
exit;