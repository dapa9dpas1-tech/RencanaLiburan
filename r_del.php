<?php
require_once __DIR__ . '/func.php';
must_login();
$uid = (int)$_SESSION['uid'];
$id  = (int)($_GET['id'] ?? 0);

$s = $conn->prepare("SELECT r.destinasi_id FROM rencana r JOIN destinasi d ON d.id=r.destinasi_id WHERE r.id=? AND d.user_id=?");
$s->bind_param('ii', $id, $uid); $s->execute();
$row = $s->get_result()->fetch_assoc(); $s->close();

if ($row) {
    $s = $conn->prepare("DELETE FROM rencana WHERE id=?");
    $s->bind_param('i', $id); $s->execute(); $s->close();
    flash('s','Kegiatan dihapus.');
    header("Location: d_view.php?id=" . $row['destinasi_id']); exit;
}
flash('e','Kegiatan tidak ditemukan.');
header('Location: dest.php'); exit;     