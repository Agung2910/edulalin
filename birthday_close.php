<?php
require_once "config.php";
session_start();

if (!isset($_SESSION['user_id'])) exit;

$user_id = (int)$_SESSION['user_id'];
$tahun   = date('Y');

$stmt = $conn->prepare("
    INSERT INTO birthday_popup_log (user_id, tahun, tanggal)
    VALUES (?, YEAR(CURDATE()), CURDATE())
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();