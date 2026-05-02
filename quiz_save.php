<?php
require_once "config.php";
require_login();

$user_id  = $_SESSION['user_id'];
$materi_id = (int)($_POST['materi_id'] ?? 0);
$skor      = (int)($_POST['skor']      ?? 0);
$total     = (int)($_POST['total']     ?? 0);
$poin      = (int)($_POST['poin']      ?? 0);
$detail_raw = $_POST['detail'] ?? '[]';

if (!$materi_id || !$total) { echo json_encode(['ok'=>false,'msg'=>'invalid']); exit; }

// Validasi JSON detail
$detail = json_decode($detail_raw, true);
if (!is_array($detail)) $detail = [];

$persentase   = round(($skor / $total) * 100, 2);
$status       = $persentase >= 70 ? 'lulus' : 'proses';
$detail_json  = json_encode($detail);

// Cek apakah sudah ada record
$stmt = $conn->prepare("SELECT id, attempt, poin_total FROM progress WHERE user_id=? AND modul_id=?");
$stmt->bind_param("ii", $user_id, $materi_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $row     = $res->fetch_assoc();
    $attempt = $row['attempt'] + 1;

    $upd = $conn->prepare("
        UPDATE progress
        SET skor=?, status=?, attempt=?, poin_total=?, detail_jawaban=?, updated_at=NOW()
        WHERE id=?
    ");
    $upd->bind_param("isiisi", $skor, $status, $attempt, $poin, $detail_json, $row['id']);
    $upd->execute();
    $upd->close();
} else {
    $attempt = 1;
    $ins = $conn->prepare("
        INSERT INTO progress (user_id, modul_id, is_read, skor, status, attempt, poin_total, detail_jawaban, updated_at)
        VALUES (?, ?, 1, ?, ?, ?, ?, ?, NOW())
    ");
    $ins->bind_param("iiisiss", $user_id, $materi_id, $skor, $status, $attempt, $poin, $detail_json);
    $ins->execute();
    $ins->close();
}

$stmt->close();
echo json_encode(['ok' => true, 'poin' => $poin, 'status' => $status]);