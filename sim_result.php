<?php
require_once "config.php";
require_login();

$user_id  = (int)$_SESSION['user_id'];
$modul_id = (int)($_POST['modul_id'] ?? 0);
$skor     = (int)($_POST['skor'] ?? 0);
$total    = (int)($_POST['total'] ?? 0);
$poin     = (int)($_POST['poin'] ?? 0);
$detail   = $_POST['detail'] ?? '';

if ($modul_id <= 0 || $total <= 0) {
    http_response_code(400);
    exit("Data tidak valid");
}

$persen = round(($skor / $total) * 100);
$status = ($persen >= 70) ? 'selesai' : 'proses';

// ── Update progress ───────────────────────────────────────────
$stmt = $conn->prepare("
    INSERT INTO progress
    (user_id, modul_id, is_read, skor, poin_total, status, attempt, updated_at, detail_jawaban)
    VALUES (?, ?, 1, ?, ?, ?, 1, NOW(), ?)
    ON DUPLICATE KEY UPDATE
        is_read        = 1,
        skor           = VALUES(skor),
        poin_total     = VALUES(poin_total),
        status         = VALUES(status),
        detail_jawaban = VALUES(detail_jawaban),
        attempt        = attempt + 1,
        updated_at     = NOW()
");
$stmt->bind_param("iiiiss", $user_id, $modul_id, $persen, $poin, $status, $detail);
$stmt->execute();
$stmt->close();

// ── Simpan ke quiz_attempts ───────────────────────────────────
// Ambil satu quiz_id dari modul ini (soal pertama)
$stmt = $conn->prepare("
    SELECT id FROM quiz WHERE materi_id = ? AND is_active = 1 LIMIT 1
");
$stmt->bind_param("i", $modul_id);
$stmt->execute();
$quiz_row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($quiz_row) {
    $quiz_id = (int)$quiz_row['id'];
    $stmt = $conn->prepare("
        INSERT INTO quiz_attempts
        (user_id, quiz_id, score, total_questions, correct_answers, attempted_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("iiiii", $user_id, $quiz_id, $persen, $total, $skor);
    $stmt->execute();
    $stmt->close();
}

echo "OK";
?>