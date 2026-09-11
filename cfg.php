<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$HOST = 'localhost';
$USER = 'root';
$PASS = '';
$NAME = 'rencana_liburan';

$conn = new mysqli($HOST, $USER, $PASS, $NAME);
if ($conn->connect_error) die('DB error: ' . $conn->connect_error);
$conn->set_charset('utf8mb4');