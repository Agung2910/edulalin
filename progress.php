<?php
require_once "config.php";
require_login();

$user_id = $_SESSION['user_id'];
$page = 'progress';

$sql_stats = "SELECT 
    COUNT(*) as total_quiz,
    COALESCE(AVG(skor),0) as avg_score,
    COALESCE(SUM(attempt),0) as total_attempts
    FROM progress
    WHERE user_id = ?";

$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->bind_param("i", $user_id);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
$stmt_stats->close();

$sql = "SELECT 
            p.modul_id,
            m.judul,
            m.jenjang,
            m.kelas,
            p.skor,
            p.status,
            p.attempt,
            p.updated_at
        FROM progress p
        JOIN modul m ON p.modul_id = m.id
        WHERE p.user_id = ?
        ORDER BY p.updated_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Progress Belajar - Edu Lalin</title>
    <link rel="icon" type="image/png" href="assets/img/logo-jr.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include 'header_common.php'; ?>
<div class="site-bg">
    <div class="site-paper">
        <div class="container">
            <div class="progress-container">
                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert" style="margin-bottom: 24px;">
                        <?php echo htmlspecialchars($_GET['msg']); ?>
                    </div>
                <?php endif; ?>
                <div class="progress-header">
                    <h1>📊 Progress Belajar</h1>
                    <p>Pantau perkembangan belajar dan hasil quiz kamu</p>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#dbeafe;color:#1e40af;">📚</div>
                        <div class="stat-value" style="color:#1e40af;">
                            <?= $stats['total_quiz']; ?>
                        </div>
                        <div class="stat-label">Quiz Dikerjakan</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#dcfce7;color:#065f46;">🎯</div>
                        <div class="stat-value" style="color:#065f46;">
                            <?= number_format($stats['avg_score'],1); ?>
                        </div>
                        <div class="stat-label">Rata-rata Skor</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#fef3c7;color:#92400e;">🔄</div>
                        <div class="stat-value" style="color:#92400e;">
                            <?= $stats['total_attempts']; ?>
                        </div>
                        <div class="stat-label">Total Percobaan</div>
                    </div>
                </div>
                <div class="progress-table">
                    <div class="table-header">
                        <h2>Riwayat Quiz</h2>
                        <p class="table-subtitle">Daftar semua quiz yang sudah kamu kerjakan</p>
                    </div>
                    <?php if ($result->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Modul</th>
                                    <th style="text-align:center;">Skor</th>
                                    <th style="text-align:center;">Status</th>
                                    <th style="text-align:center;">Percobaan</th>
                                    <th>Terakhir Dikerjakan</th>
                                    <th style="text-align:center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): 
                                    $scoreClass = 'score-poor';
                                    if ($row['skor'] >= 9) $scoreClass = 'score-excellent';
                                    elseif ($row['skor'] >= 7) $scoreClass = 'score-good';
                                    elseif ($row['skor'] >= 5) $scoreClass = 'score-fair';
                                ?>
                                <tr>
                                    <td>
                                        <div class="modul-title">
                                            <?= htmlspecialchars($row['judul']); ?>
                                        </div>
                                        <div class="modul-meta">
                                            <?= strtoupper($row['jenjang']); ?> · Kelas <?= $row['kelas']; ?>
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="score-badge <?= $scoreClass; ?>">
                                            <?= $row['skor']; ?>/10
                                        </span>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="status-badge status-<?= $row['status']; ?>">
                                            <?= ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td style="text-align:center;font-weight:600;color:#6b7280;">
                                        <?= $row['attempt']; ?>x
                                    </td>
                                    <td style="font-size:13px;color:#6b7280;">
                                        <?= date('d M Y, H:i', strtotime($row['updated_at'])); ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <a href="quiz_detail.php?modul_id=<?= $row['modul_id']; ?>" 
                                           class="btn btn-primary"
                                           style="padding:6px 16px;font-size:13px;">
                                            🔄 Ulangi
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">📝</div>
                            <h3 class="empty-title">Belum Ada Progress</h3>
                            <p class="empty-text">
                                Kamu belum mengerjakan quiz apapun. Mulai belajar dan kerjakan quiz sekarang!
                            </p>
                            <a href="modul.php" class="btn btn-primary">
                                📚 Lihat Modul
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer_common.php'; ?>
</body>
</html>
