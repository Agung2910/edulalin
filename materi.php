<?php
require_once "config.php";
require_login();

$modul_id = isset($_GET['modul_id']) ? (int)$_GET['modul_id'] : 0;

if (!$modul_id) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT m1.id, m1.judul, m1.deskripsi, m1.file_path, m1.video_url, m1.tipe, m1.jenjang, m1.kelas
    FROM modul m1
    WHERE m1.jenjang = (SELECT jenjang FROM modul WHERE id = ?)
    AND m1.kelas = (SELECT kelas FROM modul WHERE id = ?)
    AND m1.is_active = 1
    ORDER BY m1.id
");
$stmt->bind_param("ii", $modul_id, $modul_id);
$stmt->execute();
$result = $stmt->get_result();
$modulList = [];
$jenjang = '';
$kelas = 0;
while ($row = $result->fetch_assoc()) {
    $modulList[] = $row;
    if (!$jenjang) {
        $jenjang = $row['jenjang'];
        $kelas = $row['kelas'];
    }
}
$stmt->close();

// Ambil data user dari DB, jangan andalkan session untuk validasi
$stmtProgress = $conn->prepare("
    INSERT INTO progress (
        user_id,
        modul_id,
        is_read,
        skor,
        poin_total,
        status,
        attempt,
        updated_at
    )
    VALUES (?, ?, 1, 0, 0, 'selesai', 1, NOW())

    ON DUPLICATE KEY UPDATE
        is_read = 1,
        status = 'selesai',
        updated_at = NOW()
");

$stmtProgress->bind_param(
    "ii",
    $_SESSION['user_id'],
    $modul_id
);

$stmtProgress->execute();
$stmtProgress->close();
$uid  = (int)$_SESSION['user_id'];
$stmtU = $conn->prepare("SELECT jenjang, kelas, bypass_access FROM users WHERE id = ? LIMIT 1");
$stmtU->bind_param("i", $uid);
$stmtU->execute();
$dbUser = $stmtU->get_result()->fetch_assoc();
$stmtU->close();

$bypass      = (int)($dbUser['bypass_access'] ?? 0);
$userJenjang = strtolower($dbUser['jenjang'] ?? '');
$userKelas   = (int)($dbUser['kelas'] ?? 0);

// Kalau bukan bypass, jenjang & kelas harus cocok dengan modul yang dibuka
if (!$bypass) {
    if (strtolower($jenjang) !== $userJenjang || (int)$kelas !== $userKelas) {
        header("Location: blocked.php");
        exit;
    }
}

// Update session biar konsisten
$_SESSION['jenjang'] = strtoupper($userJenjang);
$_SESSION['kelas']   = $userKelas;

$namaJenjang = [
    'sd' => 'SD',
    'smp' => 'SMP',
    'sma' => 'SMA'
];
$page = 'materi';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Materi - <?php echo strtoupper($jenjang); ?> Kelas <?php echo $kelas; ?></title>
    <link rel="icon" type="image/png" href="assets/img/logo-jr.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include 'header_common.php'; ?>
    <div class="site-bg">
        <div class="site-paper">
            <div class="container">
                <div class="materi-container">
                    <div class="card">
                        <div class="card-header-flex" style="margin-bottom:12px;">
                            <div>
                            <h2>
                                Daftar Materi
                                · Kelas <?php echo $kelas; ?> -
                                <?php echo $namaJenjang[strtolower($jenjang)] ?? strtoupper($jenjang); ?>
                            </h2>
                            </div>
                            <span class="badge-soft badge-green">
                            <?php echo strtoupper($jenjang); ?> · Kelas <?php echo $kelas; ?>
                            </span>
                        </div>
                        <?php if (empty($modulList)): ?>
                        <div class="empty-modul-box">
                            <p class="empty-title">Belum ada materi untuk kelas ini.</p>
                            <p>Admin dapat menambahkan materi dari menu Kelola Modul.</p>
                        </div>
                        <?php else: ?>
                        <div class="table-wrapper">
                            <table class="custom-table">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Judul Materi</th>
                                    <th>Tipe</th>
                                    <th>Aksi</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($modulList as $i => $m): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                    <strong><?= htmlspecialchars($m['judul']) ?></strong>
                                    <?php if (!empty($m['deskripsi']) && $m['deskripsi'] != '-'): ?>
                                        <br>
                                        <span class="desc"><?= htmlspecialchars($m['deskripsi']) ?></span>
                                    <?php endif; ?>
                                    </td>
                                    <td>
                                    <span class="badge <?= $m['tipe'] ?>">
                                        <?= strtoupper($m['tipe']) ?>
                                    </span>
                                    </td>
                                    <td>
                                    <?php if($m['tipe'] == 'pdf' && $m['file_path']): ?>
                                        <a href="<?= htmlspecialchars($m['file_path']) ?>" target="_blank" class="btn btn-outline">Buka PDF</a>
                                    <?php elseif($m['tipe'] == 'video' && $m['video_url']): ?>
                                        <a href="<?= htmlspecialchars($m['video_url']) ?>" target="_blank" class="btn btn-primary">Tonton</a>
                                    <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            </div>
                        <?php endif; ?>
                        <div class="alert" style="margin-top:16px;">
                            Tips: Pelajari materi secara berurutan sebelum mengerjakan quiz.
                        </div>
                        <div class="bottom-btn-row">
                            <a href="quiz_list.php?modul_id=<?php echo $modul_id; ?>" class="btn btn-primary">📝 Kerjakan Quiz</a>
                            <a href="progress.php" class="btn btn-outline">📊 Lihat Progress</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include 'footer_common.php'; ?>
</body>
</html>