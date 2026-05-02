<?php
require_once "config.php";
require_login();

$user_id   = $_SESSION['user_id'];
$materi_id = (int)($_GET['materi_id'] ?? 0);

if (!$materi_id) { header("Location: modul.php"); exit; }

// Info modul
$stmtm = $conn->prepare("SELECT judul, jenjang, kelas FROM modul WHERE id=?");
$stmtm->bind_param("i", $materi_id);
$stmtm->execute();
$stmtm->bind_result($judul_modul, $jenjang_modul, $kelas_modul);
$stmtm->fetch();
$stmtm->close();

// Top 10 leaderboard berdasarkan poin_total
$stmtLb = $conn->prepare("
    SELECT 
        u.id,
        u.nama AS username,
        u.asal_sekolah,
        p.poin_total,
        p.skor,
        p.attempt,
        p.updated_at
    FROM progress p
    JOIN users u ON u.id = p.user_id
    WHERE p.modul_id = ?
    ORDER BY p.poin_total DESC, p.skor DESC, p.updated_at ASC
    LIMIT 10
");
$stmtLb->bind_param("i", $materi_id);
$stmtLb->execute();
$lb = $stmtLb->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtLb->close();

// Posisi user sendiri
$stmtRank = $conn->prepare("
    SELECT COUNT(*) + 1 AS my_rank
    FROM progress
    WHERE modul_id = ?
    AND (poin_total > (SELECT COALESCE(poin_total,0) FROM progress WHERE user_id=? AND modul_id=? LIMIT 1)
    OR (poin_total = (SELECT COALESCE(poin_total,0) FROM progress WHERE user_id=? AND modul_id=? LIMIT 1)
        AND skor > (SELECT COALESCE(skor,0) FROM progress WHERE user_id=? AND modul_id=? LIMIT 1)))
");
$stmtRank->bind_param("iiiiiii", $materi_id, $user_id, $materi_id, $user_id, $materi_id, $user_id, $materi_id);
$stmtRank->execute();
$stmtRank->bind_result($my_rank);
$stmtRank->fetch();
$stmtRank->close();

// Skor user sendiri
$stmtMe = $conn->prepare("SELECT poin_total, skor, attempt FROM progress WHERE user_id=? AND modul_id=?");
$stmtMe->bind_param("ii", $user_id, $materi_id);
$stmtMe->execute();
$stmtMe->bind_result($my_poin, $my_skor, $my_attempt);
$stmtMe->fetch();
$stmtMe->close();
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leaderboard — <?= htmlspecialchars($judul_modul) ?></title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Segoe UI', system-ui, sans-serif;
  background: #0f1117;
  color: #fff;
  min-height: 100dvh;
}

.topbar {
  height: 56px;
  background: #1a1d2e;
  border-bottom: 1px solid rgba(255,255,255,0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  position: sticky;
  top: 0;
  z-index: 10;
}

.topbar-left { display: flex; align-items: center; gap: 10px; }
.topbar-logo { font-size: 16px; font-weight: 800; color: #facc15; }
.topbar-title { font-size: 13px; color: rgba(255,255,255,0.5); }

.back-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 8px;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  color: rgba(255,255,255,0.7);
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.2s;
}

.back-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }

.container {
  max-width: 640px;
  margin: 0 auto;
  padding: 28px 20px 60px;
}

/* Header */
.lb-header {
  text-align: center;
  margin-bottom: 28px;
}

.lb-icon { font-size: 48px; margin-bottom: 8px; }
.lb-title { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
.lb-sub { font-size: 13px; color: rgba(255,255,255,0.45); }

/* Podium */
.podium {
  display: flex;
  justify-content: center;
  align-items: flex-end;
  gap: 12px;
  margin-bottom: 28px;
  height: 160px;
}

.podium-slot {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.podium-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  font-weight: 800;
  color: #fff;
  border: 2px solid rgba(255,255,255,0.2);
  flex-shrink: 0;
}

.podium-name {
  font-size: 11px;
  font-weight: 700;
  color: rgba(255,255,255,0.7);
  max-width: 80px;
  text-align: center;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.podium-poin {
  font-size: 12px;
  font-weight: 800;
  color: #facc15;
}

.podium-block {
  border-radius: 10px 10px 0 0;
  width: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
}

.podium-1 { height: 90px; background: linear-gradient(180deg,#fbbf24,#d97706); }
.podium-2 { height: 65px; background: linear-gradient(180deg,#94a3b8,#64748b); }
.podium-3 { height: 48px; background: linear-gradient(180deg,#d97706,#92400e); }

/* My rank card */
.my-rank-card {
  background: rgba(250,204,21,0.08);
  border: 1px solid rgba(250,204,21,0.25);
  border-radius: 14px;
  padding: 14px 18px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.my-rank-left { display: flex; align-items: center; gap: 12px; }
.my-rank-num { font-size: 28px; font-weight: 900; color: #facc15; min-width: 40px; }
.my-rank-name { font-size: 14px; font-weight: 700; }
.my-rank-sub { font-size: 12px; color: rgba(255,255,255,0.45); margin-top: 2px; }
.my-rank-poin { font-size: 20px; font-weight: 900; color: #facc15; }

/* List */
.lb-list { display: flex; flex-direction: column; gap: 10px; }

.lb-row {
  border-radius: 16px;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: transform 0.2s, box-shadow 0.2s;
  position: relative;
  overflow: hidden;
}

/* Background gradient per rank */
.lb-row.rank-1 {
  background: linear-gradient(135deg, #2d1f00, #3d2a00);
  border: 1px solid rgba(251,191,36,0.4);
  box-shadow: 0 4px 20px rgba(251,191,36,0.15);
}

.lb-row.rank-2 {
  background: linear-gradient(135deg, #1a2030, #1e2540);
  border: 1px solid rgba(148,163,184,0.3);
  box-shadow: 0 4px 16px rgba(148,163,184,0.08);
}

.lb-row.rank-3 {
  background: linear-gradient(135deg, #1f1000, #2a1800);
  border: 1px solid rgba(217,119,6,0.35);
  box-shadow: 0 4px 16px rgba(217,119,6,0.1);
}

.lb-row.rank-other {
  background: #1a1d2e;
  border: 1px solid rgba(255,255,255,0.07);
}

.lb-row.rank-other:hover {
  border-color: rgba(255,255,255,0.15);
  transform: translateY(-1px);
  box-shadow: 0 4px 16px rgba(0,0,0,0.3);
}

.lb-row.is-me {
  border-color: rgba(250,204,21,0.4) !important;
  box-shadow: 0 0 0 1px rgba(250,204,21,0.2), 0 4px 20px rgba(250,204,21,0.12) !important;
}

.lb-rank {
  font-size: 18px;
  font-weight: 900;
  min-width: 36px;
  text-align: center;
}

.lb-rank.r1 { color: #fbbf24; }
.lb-rank.r2 { color: #94a3b8; }
.lb-rank.r3 { color: #d97706; }
.lb-rank.rother { color: rgba(255,255,255,0.35); font-size: 14px; }

.lb-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: 800;
  color: #fff;
  flex-shrink: 0;
  border: 2px solid rgba(255,255,255,0.15);
}

.lb-info { flex: 1; min-width: 0; }

.lb-name {
  font-size: 14px;
  font-weight: 700;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: #fff;
}

.lb-school {
  font-size: 11px;
  color: rgba(255,255,255,0.45);
  margin-top: 2px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.lb-answered {
  font-size: 11px;
  color: rgba(255,255,255,0.35);
  margin-top: 1px;
}

.lb-poin {
  font-size: 17px;
  font-weight: 900;
  color: #facc15;
  white-space: nowrap;
  text-align: right;
}

.lb-poin-lbl {
  font-size: 10px;
  color: rgba(255,255,255,0.35);
  text-align: right;
  margin-top: 1px;
}

.section-title {
  font-size: 12px;
  font-weight: 700;
  color: rgba(255,255,255,0.4);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 12px;
}

.empty-lb {
  text-align: center;
  padding: 40px 20px;
  color: rgba(255,255,255,0.35);
  font-size: 14px;
}

.retry-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 20px;
  padding: 10px 20px;
  background: #3b82f6;
  color: #fff;
  border-radius: 10px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 700;
  transition: filter 0.2s;
}

.retry-btn:hover { filter: brightness(1.1); }
</style>
</head>
<body>

<div class="topbar">
  <div class="topbar-left">
    <div class="topbar-logo">EduLalin</div>
    <div class="topbar-title">Leaderboard</div>
  </div>
  <a href="quiz_detail.php?materi_id=<?= $materi_id ?>" class="back-btn">← Kembali ke Kuis</a>
</div>

<div class="container">

  <!-- Header -->
  <div class="lb-header">
    <div class="lb-icon">🏆</div>
    <div class="lb-title"><?= htmlspecialchars($judul_modul) ?></div>
    <div class="lb-sub"><?= strtoupper($jenjang_modul) ?> · Kelas <?= $kelas_modul ?> · Top 10 Skor Tertinggi</div>
  </div>

  <?php if (count($lb) === 0): ?>
  <div class="empty-lb">
    <div style="font-size:40px;margin-bottom:12px">📭</div>
    <div>Belum ada yang mengerjakan kuis ini.</div>
    <div>Jadilah yang pertama!</div>
    <a href="quiz_detail.php?materi_id=<?= $materi_id ?>" class="retry-btn">🚀 Mulai Kuis</a>
  </div>

  <?php else: ?>

  <!-- Podium top 3 -->
  <?php if (count($lb) >= 3): ?>
  <div class="podium">
    <!-- Rank 2 -->
    <?php $p2 = $lb[1]; ?>
    <div class="podium-slot">
      <div class="podium-avatar" style="background:#64748b"><?= strtoupper(substr($p2['username'],0,1)) ?></div>
      <div class="podium-name"><?= htmlspecialchars($p2['username']) ?></div>
      <div class="podium-poin">⭐ <?= number_format($p2['poin_total']) ?></div>
      <div class="podium-block podium-2">🥈</div>
    </div>
    <!-- Rank 1 -->
    <?php $p1 = $lb[0]; ?>
    <div class="podium-slot">
      <div class="podium-avatar" style="background:#d97706;width:52px;height:52px;font-size:22px"><?= strtoupper(substr($p1['username'],0,1)) ?></div>
      <div class="podium-name" style="font-size:13px;color:#fff"><?= htmlspecialchars($p1['username']) ?></div>
      <div class="podium-poin" style="font-size:14px">⭐ <?= number_format($p1['poin_total']) ?></div>
      <div class="podium-block podium-1">🥇</div>
    </div>
    <!-- Rank 3 -->
    <?php $p3 = $lb[2]; ?>
    <div class="podium-slot">
      <div class="podium-avatar" style="background:#92400e"><?= strtoupper(substr($p3['username'],0,1)) ?></div>
      <div class="podium-name"><?= htmlspecialchars($p3['username']) ?></div>
      <div class="podium-poin">⭐ <?= number_format($p3['poin_total']) ?></div>
      <div class="podium-block podium-3">🥉</div>
    </div>
  </div>
  <?php endif; ?>

  <!-- My rank card -->
  <?php if ($my_poin !== null): ?>
  <div class="my-rank-card">
    <div class="my-rank-left">
      <div class="my-rank-num">#<?= $my_rank ?></div>
      <div>
        <div class="my-rank-name">Kamu (<?= htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? 'Kamu') ?>)</div>
        <div class="my-rank-sub"><?= $my_attempt ?> percobaan · <?= $my_skor ?>/<?= count($lb) ? 10 : '?' ?> benar</div>
      </div>
    </div>
    <div>
      <div class="my-rank-poin">⭐ <?= number_format($my_poin) ?></div>
      <div style="font-size:10px;color:rgba(255,255,255,0.35);text-align:right">poin terbaik</div>
    </div>
  </div>
  <?php endif; ?>

  <!-- List semua -->
  <div class="section-title">Peringkat Lengkap</div>
  <div class="lb-list">
    <?php
    $avatarColors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#84cc16','#ec4899','#14b8a6'];
    foreach ($lb as $i => $row):
    $rank      = $i + 1;
    $isMe      = $row['id'] == $user_id;
    $rankCls   = $rank === 1 ? 'r1' : ($rank === 2 ? 'r2' : ($rank === 3 ? 'r3' : 'rother'));
    $rankIcon  = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : '#'.$rank));
    $rowBgCls  = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : 'rank-other'));
    $avatarBg  = $avatarColors[$i % count($avatarColors)];
    $initial   = strtoupper(substr($row['username'], 0, 1));
    $sekolah   = !empty($row['asal_sekolah']) ? htmlspecialchars($row['asal_sekolah']) : 'Sekolah tidak diisi';
    $answered  = $row['skor'] . '/' . $row['attempt'] * 10 . ' soal terjawab';
    ?>
    <div class="lb-row <?= $rowBgCls ?> <?= $isMe ? 'is-me' : '' ?>">
    <div class="lb-rank <?= $rankCls ?>"><?= $rankIcon ?></div>
    <div class="lb-avatar" style="background:<?= $avatarBg ?>"><?= $initial ?></div>
    <div class="lb-info">
        <div class="lb-name">
        <?= htmlspecialchars($row['username']) ?>
        <?= $isMe ? ' <span style="color:#facc15;font-size:10px">(Kamu)</span>' : '' ?>
        </div>
        <div class="lb-school">🏫 <?= $sekolah ?></div>
        <div class="lb-answered">✅ <?= $row['skor'] ?>/10 benar · <?= $row['attempt'] ?>x main</div>
    </div>
    <div>
        <div class="lb-poin">⭐ <?= number_format($row['poin_total']) ?></div>
        <div class="lb-poin-lbl">poin</div>
    </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="text-align:center;margin-top:28px">
    <a href="quiz_detail.php?materi_id=<?= $materi_id ?>" class="retry-btn">🔄 Main Lagi</a>
  </div>

  <?php endif; ?>

</div>
</body>
</html>