<?php
require_once "config.php";
require_login();

$user_id = (int)$_SESSION['user_id'];

// Ambil data user + bypass langsung dari DB (bukan cuma session)
$stmt = $conn->prepare("SELECT jenjang, kelas, bypass_access FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$bypass     = (int)($user['bypass_access'] ?? 0);
$jenjang    = strtolower($user['jenjang'] ?? '');
$kelas      = (int)($user['kelas'] ?? 0);

// ⬇️ Query semua modul tanpa filter jenjang/kelas kalau bypass
if ($bypass) {
    $stmt = $conn->prepare("
        SELECT m.id, m.judul, COUNT(q.id) AS total_soal
        FROM modul m
        LEFT JOIN quiz q ON q.materi_id = m.id
        WHERE m.is_active = 1
        GROUP BY m.id
    ");
    $stmt->execute();
} else {
    $stmt = $conn->prepare("
        SELECT m.id, m.judul, COUNT(q.id) AS total_soal
        FROM modul m
        LEFT JOIN quiz q ON q.materi_id = m.id
        WHERE m.jenjang = ? AND m.kelas = ? AND m.is_active = 1
        GROUP BY m.id
    ");
    $stmt->bind_param("si", $jenjang, $kelas);
    $stmt->execute();
}

$quizList = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Quiz - <?php echo strtoupper($jenjang); ?> Kelas <?php echo $kelas; ?></title>
    <link rel="icon" type="image/png" href="assets/img/logo-jr.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include 'header_common.php'; ?>
    <div class="site-bg">
        <div class="site-paper">
            <div class="container">
                <div class="card">
                <div class="card-header-flex" style="margin-bottom:12px;">
                    <div>
                    <h2>
                        Daftar Quiz
                        · Kelas <?php echo $kelas; ?> -
                        <?php echo strtoupper($jenjang); ?>
                    </h2>
                    </div>
                    <span class="badge-soft badge-green">
                    <?php echo strtoupper($jenjang); ?> · Kelas <?php echo $kelas; ?>
                    </span>
                </div>
                <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul Materi</th>
                        <th>Soal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $no = 1;
                    if ($quizList->num_rows > 0):
                        while ($row = $quizList->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>

                        <td>
                        <strong><?= htmlspecialchars($row['judul']) ?></strong>
                        </td>

                        <td>
                        <span class="badge soal">
                            <?= $row['total_soal'] ?> soal
                        </span>
                        </td>

                        <td>
                        <?php if ($row['total_soal'] > 0): ?>
                            <span class="badge ready">Tersedia</span>
                        <?php else: ?>
                            <span class="badge empty">Kosong</span>
                        <?php endif; ?>
                        </td>

                        <td>
                        <?php if ($row['total_soal'] > 0): ?>
                            <a href="quiz_detail.php?materi_id=<?= $row['id'] ?>" class="btn btn-primary">Mulai</a>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:20px;">
                        Belum ada quiz tersedia.
                        </td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                </div>
                <div class="alert" style="margin-top:16px;">
                    Tips: Pastikan sudah mempelajari materi sebelum mengerjakan quiz.
                </div>
                    <div class="bottom-btn-row">
                        <a href="materi.php?modul_id=<?php echo $modul_id; ?>" class="btn btn-outline">📚 Review Materi</a>
                        <a href="progress.php" class="btn btn-outline">📊 Lihat Progress</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include 'footer_common.php'; ?>
</body>
</html>