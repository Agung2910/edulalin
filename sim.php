<?php
require_once "config.php";
require_login();

$stmt = $conn->prepare("SELECT tanggal_lahir FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($tgl_lahir);
$stmt->fetch();
$stmt->close();

$usia = null;
if ($tgl_lahir) {
    $usia = (int) floor(
        (time() - strtotime($tgl_lahir)) / (365.25 * 24 * 60 * 60)
    );
}

$batasUsia = 17;
$bolehAkses = !empty($_SESSION['bypass_access']) || ($usia !== null && $usia >= $batasUsia);

$stmt = $conn->prepare("
    SELECT 
        m.id,
        m.judul,
        m.deskripsi,
        m.tipe,
        m.file_path,
        m.video_url,
        COUNT(q.id) AS total_soal
    FROM modul m
    LEFT JOIN quiz q ON q.materi_id = m.id
    WHERE m.kategori = 'sim'
    AND m.is_active = 1
    GROUP BY m.id
    ORDER BY m.id ASC
");
$stmt->execute();
$modulSIM = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Program SIM - Edu Lalin</title>
<link rel="icon" href="assets/img/logo-jr.png">
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
                            <h2>Program Persiapan SIM</h2>
                            <p style="font-size:13px;color:#64748b;margin:0;">
                                Persiapkan dirimu sebelum ujian SIM
                            </p>
                        </div>
                        <?php if ($usia !== null): ?>
                            <?php if ($bolehAkses): ?>
                                <span class="badge-soft badge-green">✔ Usia <?= $usia ?> tahun — Memenuhi syarat</span>
                            <?php else: ?>
                                <span class="badge-soft" style="background:#fee2e2;color:#991b1b;">🔒 Usia <?= $usia ?> tahun — Min. <?= $batasUsia ?> tahun</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (!$bolehAkses): ?>
                        <div class="alert">
                            Anda belum dapat mengakses program SIM karena belum memenuhi batas usia minimum <?= $batasUsia ?> tahun.
                        </div>
                    <?php endif; ?>

                    <?php if ($bolehAkses): ?>
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

                            if ($modulSIM->num_rows > 0):
                                while ($m = $modulSIM->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($m['judul']) ?></strong>
                                    <?php if (!empty($m['deskripsi'])): ?>
                                        <br>
                                        <span class="desc">
                                            <?= htmlspecialchars($m['deskripsi']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge soal">
                                        <?= $m['total_soal'] ?> soal
                                    </span>
                                </td>
                                <td>
                                    <?php if ($m['total_soal'] > 0): ?>
                                        <span class="badge ready">Tersedia</span>
                                    <?php else: ?>
                                        <span class="badge empty">Kosong</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($m['tipe'] === 'pdf' && $m['file_path']): ?>
                                        <a href="read_sim.php?id=<?= $m['id'] ?>" target="_blank" class="btn btn-outline">
                                            📄 PDF
                                        </a>
                                    <?php elseif ($m['tipe'] === 'video' && $m['video_url']): ?>
                                        <a href="<?= htmlspecialchars($m['video_url']) ?>" target="_blank" class="btn btn-outline">
                                            ▶ Video
                                        </a>
                                    <?php endif; ?>

                                    <a href="sim_quiz.php?modul_id=<?= $m['id'] ?>" class="btn btn-primary">
                                        📝 Kuis
                                    </a>
                                </td>
                            </tr>

                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding:20px;">
                                    Belum ada materi SIM tersedia.
                                </td>
                            </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                        </div>
                        <div class="alert" style="margin-top:16px;">
                            Tips: Pelajari semua materi sebelum mengerjakan kuis SIM.
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