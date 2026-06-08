<?php
require_once "config.php";
require_login();

$user_id = (int)$_SESSION['user_id'];
$modul_id = (int)($_GET['id'] ?? 0);

if ($modul_id <= 0) {
    exit("Modul tidak valid");
}

// ambil data modul
$stmt = $conn->prepare("
    SELECT file_path
    FROM modul
    WHERE id = ?
    AND is_active = 1
    LIMIT 1
");

$stmt->bind_param("i", $modul_id);
$stmt->execute();

$modul = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$modul) {
    exit("Modul tidak ditemukan");
}

// =========================
// UPDATE PROGRESS
// =========================

$cek = $conn->prepare("
    SELECT id
    FROM progress
    WHERE user_id = ?
    AND modul_id = ?
");

$cek->bind_param("ii", $user_id, $modul_id);
$cek->execute();

$exists = $cek->get_result()->fetch_assoc();
$cek->close();

if ($exists) {

    $update = $conn->prepare("
        UPDATE progress
        SET is_read = 1,
            status = 'selesai',
            updated_at = NOW()
        WHERE user_id = ?
        AND modul_id = ?
    ");

    $update->bind_param("ii", $user_id, $modul_id);
    $update->execute();
    $update->close();

} else {

    $insert = $conn->prepare("
        INSERT INTO progress
        (user_id, modul_id, is_read, skor, status, attempt, updated_at)
        VALUES (?, ?, 1, 0, 'selesai', 0, NOW())
    ");

    $insert->bind_param("ii", $user_id, $modul_id);
    $insert->execute();
    $insert->close();
}

// buka pdf
header("Location: " . $modul['file_path']);
exit;