<?php
require_once "../config.php";

$jenjang = $_GET['jenjang'] ?? '';
$kelas   = (int)($_GET['kelas'] ?? 0);

$data = [];

if ($jenjang === 'sim') {
    $stmt = $conn->prepare("SELECT id, judul FROM modul WHERE kategori = 'sim' AND is_active = 1 ORDER BY id ASC");
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();

} elseif (in_array($jenjang, ['sd','smp','sma']) && $kelas > 0) {
    $stmt = $conn->prepare("SELECT id, judul FROM modul WHERE jenjang = ? AND kelas = ? AND is_active = 1");
    $stmt->bind_param("si", $jenjang, $kelas);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($data);