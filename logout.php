<?php
require_once __DIR__ . '/func.php';
session_unset(); session_destroy(); session_start();
flash('s','Anda berhasil logout.');
header('Location: login.php'); exit;