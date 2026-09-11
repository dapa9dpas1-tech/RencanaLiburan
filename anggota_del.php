<?php
require_once __DIR__ . '/func.php';
must_login();
$uid = (int)$_SESSION['uid'];
$id  = (int)($_GET['id'] ?? 0);
$did = (int)($_GET['dest'] ?? 0);

// Pastikan destinasi milik user
$d = get_dest($conn, $did, $uid);
if ($d && $id) {
    $s = $conn->prepare("DELETE FROM anggota WHERE id=? AND destinasi_id=?");
    $s->bind_param('ii', $id, $did);
    $s->execute(); $s->close();
    flash('s', 'Anggota dihapus.');
}
header('Location: d_view.php?id=' . $did);
exit;