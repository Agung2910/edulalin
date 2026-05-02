<?php
require_once "../config.php";
require_admin();

$filter_jenjang = $_GET['jenjang'] ?? '';
$filter_sekolah = $_GET['sekolah'] ?? '';

$where = [];
$params = [];
$types = '';

if (!empty($filter_jenjang)) {
    $where[] = "jenjang = ?";
    $params[] = $filter_jenjang;
    $types .= 's';
}

if (!empty($filter_sekolah)) {
    $where[] = "asal_sekolah = ?";
    $params[] = $filter_sekolah;
    $types .= 's';
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $conn->prepare("
    SELECT 
        nama,
        jenjang,
        asal_sekolah,
        kelas,
        email,
        role,
        created_at
    FROM users
    $where_clause
    ORDER BY jenjang, asal_sekolah, kelas, nama
");

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=users_export.xls");

echo "Nama\tJenjang\tAsal Sekolah\tKelas\tEmail\tRole\tTanggal Daftar\n";

while ($row = $result->fetch_assoc()) {
    echo 
        $row['nama'] . "\t" .
        $row['jenjang'] . "\t" .
        $row['asal_sekolah'] . "\t" .
        $row['kelas'] . "\t" .
        $row['email'] . "\t" .
        $row['role'] . "\t" .
        $row['created_at'] . "\n";
}
exit;
