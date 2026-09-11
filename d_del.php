<?php
require_once __DIR__ . '/func.php';
must_login();
$uid = (int)$_SESSION['uid'];
$id  = (int)($_GET['id'] ?? 0);
$d   = get_dest($conn, $id, $uid);
if ($d) {
    del_foto($d['foto']);
    $s = $conn->prepare("DELETE FROM destinasi WHERE id=? AND user_id=?");
    $s->bind_param('ii', $id, $uid); $s->execute(); $s->close();
    flash('s','Destinasi dihapus.');
} else flash('e','Destinasi tidak ditemukan.');
header('Location: dest.php'); exit;