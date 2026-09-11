<?php
require_once __DIR__ . '/../config/database.php';

function is_login() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_login()) {
        header('Location: login.php');
        exit;
    }
}

function current_user() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'nama' => $_SESSION['user_nama'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
    ];
}