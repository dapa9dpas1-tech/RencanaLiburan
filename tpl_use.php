<?php
require_once __DIR__ . '/func.php';
must_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

$uid  = (int)$_SESSION['uid'];
$slug = $_POST['slug'] ?? '';
$all  = require __DIR__ . '/template.php';

if (!isset($all[$slug])) {
    flash('e', 'Template tidak ditemukan.');
    header('Location: index.php'); exit;
}

$t = $all[$slug];
$totalBudget = $t['lama'] * $t['budget_per_hari'];
$tanggal     = date('Y-m-d', strtotime('+30 days'));
$judul       = $t['nama'] . ' (' . $t['lama'] . ' Hari)';
$foto        = null;

$conn->begin_transaction();
try {
    $s = $conn->prepare("INSERT INTO destinasi (user_id,foto,judul,tanggal,budget,lama,status) VALUES (?,?,?,?,?,?,0)");
    $s->bind_param('isssdi', $uid, $foto, $judul, $tanggal, $totalBudget, $t['lama']);
    $s->execute();
    $destId = (int)$conn->insert_id;
    $s->close();

    $ins = $conn->prepare("INSERT INTO rencana (destinasi_id,hari,jam,lokasi,kegiatan) VALUES (?,?,?,?,?)");
    foreach ($t['hari'] as $hari => $items) {
        foreach ($items as $r) {
            $jam = $r['jam'] !== '' ? $r['jam'] : null;
            $ins->bind_param('iisss', $destId, $hari, $jam, $r['lokasi'], $r['kegiatan']);
            $ins->execute();
        }
    }
    $ins->close();

    $conn->commit();
    flash('s', 'Template ' . $t['nama'] . ' berhasil ditambahkan! Silakan cek & edit.');
    header('Location: d_view.php?id=' . $destId);
    exit;
} catch (Throwable $ex) {
    $conn->rollback();
    flash('e', 'Gagal menambahkan template: ' . $ex->getMessage());
    header('Location: tpl.php?slug=' . urlencode($slug));
    exit;
}