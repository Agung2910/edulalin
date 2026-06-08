<?php
require_once "config.php";
require_login();

$user_id = (int)$_SESSION['user_id'];
$modul_id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT file_path
    FROM modul
    WHERE id = ?
    AND kategori = 'sim'
    AND is_active = 1
    LIMIT 1
");

$stmt->bind_param("i", $modul_id);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    exit("Modul tidak ditemukan");
}

$stmtP = $conn->prepare("
    INSERT INTO progress
    (user_id, modul_id, is_read, skor, poin_total, status, attempt, updated_at)
    VALUES (?, ?, 1, 0, 0, 'proses', 0, NOW())

    ON DUPLICATE KEY UPDATE
    is_read = 1,
    updated_at = NOW()
");

$stmtP->bind_param("ii", $user_id, $modul_id);
$stmtP->execute();
$stmtP->close();

header("Location: " . $data['file_path']);
exit;
?>