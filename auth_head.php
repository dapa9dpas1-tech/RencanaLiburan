<?php
require_once __DIR__ . '/func.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#0ea5e9">
<title><?= isset($title) ? e($title).' — Rencana Liburan' : 'Rencana Liburan' ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  body{font-family:'Plus Jakarta Sans',sans-serif;}
  .bg-fade::before{
    content:''; position:absolute; inset:0;
    background: linear-gradient(180deg, rgba(14,165,233,.25), rgba(30,64,175,.65));
  }
</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-sky-50 via-white to-indigo-50 p-3 sm:p-6 flex items-center justify-center">