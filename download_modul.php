<?php
require_once "config.php";
require_login(); 

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit("ID tidak valid");
}

$stmt = $conn->prepare("SELECT file_path, judul FROM modul WHERE id = ? AND is_active = 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($filePath, $judul);
if (!$stmt->fetch() || empty($filePath)) {
    http_response_code(404);
    exit("File tidak ditemukan");
}
$stmt->close();

$fullPath = __DIR__ . "/" . $filePath;
if (!is_file($fullPath)) {
    http_response_code(404);
    exit("File tidak ditemukan di server");
}

$filename = basename($fullPath);
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($fullPath));
flush();
readfile($fullPath);
exit;