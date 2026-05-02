<?php
require_once "../config.php";
require_admin();

$stats=[
  'total_users'   => $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'],
  'total_modul'   => $conn->query("SELECT COUNT(*) c FROM modul WHERE is_active=1")->fetch_assoc()['c'],
  'total_quiz'    => $conn->query("SELECT COUNT(*) c FROM quiz")->fetch_assoc()['c'],
  'total_attempt' => $conn->query("SELECT COUNT(*) c FROM progress")->fetch_assoc()['c'],
];

// 5 modul terpopuler
$pop=[];
$r=$conn->query("SELECT m.judul,m.jenjang,m.kelas,COUNT(p.id) as tot FROM modul m LEFT JOIN progress p ON m.id=p.modul_id WHERE m.is_active=1 GROUP BY m.id ORDER BY tot DESC LIMIT 5");
while($row=$r->fetch_assoc()) $pop[]=$row;

// User teraktif per jenjang — untuk tab chart
$akt_by_jenjang = [];
foreach(['sd','smp','sma','sim'] as $j) {
  // Pakai subquery biar HAVING bisa filter tanpa alias issue
  $r=$conn->query("
    SELECT nama, baca, quiz_done FROM (
      SELECT u.nama,
        COUNT(DISTINCT CASE WHEN p.is_read=1 THEN p.modul_id END) AS baca,
        COUNT(DISTINCT CASE WHEN p.attempt>0 THEN p.modul_id END) AS quiz_done
      FROM users u
      LEFT JOIN progress p ON u.id=p.user_id
      WHERE u.jenjang='$j'
      GROUP BY u.id
    ) AS sub
    WHERE (baca + quiz_done) > 0
    ORDER BY (baca + quiz_done) DESC
    LIMIT 7
  ");
  $rows=[];
  if($r) while($row=$r->fetch_assoc()) $rows[]=$row;
  $akt_by_jenjang[strtoupper($j)] = $rows;
}
$akt_js = [];
foreach($akt_by_jenjang as $j=>$rows){
  $akt_js[$j]=['names'=>array_map(fn($u)=>$u['nama'],$rows),'baca'=>array_map(fn($u)=>(int)$u['baca'],$rows),'quiz'=>array_map(fn($u)=>(int)$u['quiz_done'],$rows)];
}

// Progress Per Jenjang (chart bar)
$jc=[];
$r=$conn->query("SELECT u.jenjang,
  ROUND(AVG(
    (SELECT COUNT(*) FROM progress WHERE user_id=u.id AND is_read=1) /
    GREATEST((SELECT COUNT(*) FROM modul WHERE jenjang=u.jenjang AND kelas=u.kelas AND is_active=1),1)
    * 100
  ),1) as ac
  FROM users u WHERE u.jenjang IS NOT NULL GROUP BY u.jenjang");
while($row=$r->fetch_assoc()) $jc[strtoupper($row['jenjang'])]=$row['ac'];

// Filter progress tabel
$pf_jenjang = $_GET['pf_jenjang'] ?? '';
$pf_sekolah = $_GET['pf_sekolah'] ?? '';

// Ambil daftar sekolah untuk dropdown (filter by jenjang kalau dipilih)
$sekolah_list = [];
$sq = "SELECT DISTINCT asal_sekolah FROM users WHERE asal_sekolah IS NOT NULL AND asal_sekolah!=''";
if($pf_jenjang) $sq .= " AND jenjang='".mysqli_real_escape_string($conn,$pf_jenjang)."'";
$sq .= " ORDER BY asal_sekolah";
$sr = $conn->query($sq);
if($sr) while($row=$sr->fetch_assoc()) $sekolah_list[] = $row['asal_sekolah'];

// Query progress dengan filter
$top_where = "1=1";
if($pf_jenjang) $top_where .= " AND u.jenjang='".mysqli_real_escape_string($conn,$pf_jenjang)."'";
if($pf_sekolah) $top_where .= " AND u.asal_sekolah='".mysqli_real_escape_string($conn,$pf_sekolah)."'";

$top=[];
$r=$conn->query("
  SELECT
    u.nama, u.jenjang, u.kelas, u.asal_sekolah,
    COUNT(DISTINCT CASE WHEN p.is_read=1 THEN p.modul_id END) AS baca,
    COUNT(DISTINCT CASE WHEN p.attempt>0 THEN p.modul_id END) AS quiz_done,
    ROUND(AVG(CASE WHEN p.skor IS NOT NULL THEN p.skor END),1) AS rata_skor,
    (SELECT COUNT(*) FROM modul WHERE jenjang=u.jenjang AND kelas=u.kelas AND is_active=1) AS total_modul,
    (SELECT COUNT(DISTINCT m2.id) FROM modul m2 WHERE m2.jenjang=u.jenjang AND m2.kelas=u.kelas AND m2.is_active=1 AND EXISTS(SELECT 1 FROM quiz WHERE materi_id=m2.id)) AS total_quiz
  FROM users u
  LEFT JOIN progress p ON u.id=p.user_id
  WHERE $top_where
  GROUP BY u.id
  ORDER BY (COUNT(DISTINCT CASE WHEN p.is_read=1 THEN p.modul_id END) + COUNT(DISTINCT CASE WHEN p.attempt>0 THEN p.modul_id END)) DESC
  LIMIT 20
");
while($row=$r->fetch_assoc()) $top[]=$row;


?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan & Statistik - Edu Lalin</title>
<link rel="icon" type="image/png" href="../assets/img/logo-jr.png">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="adm-layout">
<?php include 'sidebar.php'; ?>
<div class="adm-main">

  <div class="adm-topbar">
    <div class="adm-topbar-title">Laporan &amp; Statistik</div>
    <div class="adm-topbar-right">
      <button class="adm-icon-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></button>
      <button class="adm-icon-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></button>
      <div class="adm-user">
        <div class="adm-user-av"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
        <span class="adm-user-name"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
        <span class="adm-user-caret">&#9662;</span>
      </div>
      <form method="post" action="../logout.php" style="margin:0"><button class="adm-btn-out" type="submit">Keluar</button></form>
    </div>
  </div>

  <div class="adm-content">

    <div class="adm-ph">
      <div class="adm-ph-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div>
      <div><h1>Laporan &amp; Statistik</h1><p>Ringkasan performa pengguna dan aktivitas pembelajaran platform Edu Lalin</p></div>
    </div>

    <!-- STAT CARDS -->
    <div class="adm-stats">
      <div class="adm-stat"><div class="adm-stat-icon blue"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3b5bdb" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div><div><div class="adm-stat-label">Total Pengguna</div><div class="adm-stat-value"><?=number_format($stats['total_users'])?></div></div></div>
      <div class="adm-stat"><div class="adm-stat-icon green"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0d9a6a" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></div><div><div class="adm-stat-label">Modul Aktif</div><div class="adm-stat-value"><?=number_format($stats['total_modul'])?></div></div></div>
      <div class="adm-stat"><div class="adm-stat-icon yellow"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#c68c00" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div><div><div class="adm-stat-label">Total Quiz</div><div class="adm-stat-value"><?=number_format($stats['total_quiz'])?></div></div></div>
      <div class="adm-stat"><div class="adm-stat-icon red"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#e53935" stroke-width="2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2z"/></svg></div><div><div class="adm-stat-label">Total Attempt</div><div class="adm-stat-value"><?=number_format($stats['total_attempt'])?></div></div></div>
    </div>

    <!-- ROW 1: User Teraktif Chart + Progress Per Jenjang -->
    <div class="adm-2col" style="margin-bottom:20px">

      <!-- Chart User Teraktif — vertikal + tab jenjang -->
      <div class="adm-card">
        <div class="adm-card-hd">
          <h3>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#3b5bdb" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            User Teraktif
          </h3>
          <div style="display:flex;gap:12px;align-items:center">
            <span style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--muted)"><span style="width:10px;height:10px;border-radius:2px;background:#3b5bdb;display:inline-block"></span> Modul Dibaca</span>
            <span style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--muted)"><span style="width:10px;height:10px;border-radius:2px;background:#10b981;display:inline-block"></span> Quiz Dikerjakan</span>
          </div>
        </div>
        <!-- Tab jenjang -->
        <div style="display:flex;gap:6px;padding:11px 18px;border-bottom:1px solid var(--border);flex-wrap:wrap;">
          <?php foreach(['SD','SMP','SMA','SIM'] as $tj): ?>
          <button
            class="akt-tab <?= $tj==='SD'?'akt-tab-active':'' ?>"
            onclick="switchAkt('<?= $tj ?>',this)"
            style="padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;cursor:pointer;border:1.5px solid var(--border);background:<?= $tj==='SD'?'#3b5bdb':'transparent' ?>;color:<?= $tj==='SD'?'#fff':'var(--muted)' ?>;font-family:inherit;transition:all .15s">
            <?= $tj ?>
            <span style="font-size:11px;opacity:.8">(<?= count($akt_by_jenjang[$tj]) ?>)</span>
          </button>
          <?php endforeach; ?>
        </div>
        <div class="adm-card-body">
          <div style="position:relative;width:100%;height:260px">
            <canvas id="aktChart"></canvas>
          </div>
          <div id="aktEmpty" style="display:none;padding:28px;text-align:center;color:var(--muted);font-size:13px">Belum ada aktivitas di jenjang ini.</div>
          <!-- Summary -->
          <div id="aktSummary" style="display:flex;gap:20px;padding-top:14px;margin-top:14px;border-top:1px solid var(--border)">
            <div style="font-size:12px;color:var(--muted)">Total modul dibaca<div id="sumBaca" style="font-size:20px;font-weight:600;color:var(--text);margin-top:2px">0</div></div>
            <div style="font-size:12px;color:var(--muted);padding-left:20px;border-left:1px solid var(--border)">Total quiz dikerjakan<div id="sumQuiz" style="font-size:20px;font-weight:600;color:var(--text);margin-top:2px">0</div></div>
          </div>
        </div>
      </div>

      <!-- Progress Per Jenjang -->
      <div class="adm-card">
        <div class="adm-card-hd">
          <h3>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#3b5bdb" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Progress Per Jenjang
          </h3>
          <span class="adm-card-sub">Rata-rata modul dibaca</span>
        </div>
        <div class="adm-card-body">
          <div class="adm-chart-wrap">
            <canvas id="jChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- 5 Modul Terpopuler -->
    <div class="adm-card" style="margin-bottom:20px">
      <div class="adm-card-hd">
        <h3>
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          5 Modul Terpopuler
        </h3>
        <span class="adm-card-sub">Berdasarkan jumlah attempt</span>
      </div>
      <?php if(!empty($pop)): ?>
      <div class="adm-table-wrap">
        <table class="adm-table">
          <thead><tr>
            <th style="text-align:center;width:60px">Rank</th>
            <th>Judul</th>
            <th style="text-align:center">Jenjang</th>
            <th style="text-align:center">Kelas</th>
            <th style="text-align:center">Attempt</th>
          </tr></thead>
          <tbody>
          <?php foreach($pop as $i=>$m):
            $ju=strtoupper($m['jenjang']??'');
            $bc=$ju==='SMA'?'b-sma':($ju==='SMP'?'b-smp':($ju==='SD'?'b-sd':'b-sim'));
          ?>
          <tr>
            <td class="adm-td-id" style="text-align:center">#<?=$i+1?></td>
            <td style="font-weight:500"><?=htmlspecialchars($m['judul'])?></td>
            <td style="text-align:center"><span class="badge <?=$bc?>"><?=$ju?></span></td>
            <td style="text-align:center"><?=$m['kelas']?:'–'?></td>
            <td style="text-align:center;font-weight:700;color:var(--success)"><?=$m['tot']?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div style="padding:40px;text-align:center;color:var(--muted)">Belum ada data.</div>
      <?php endif; ?>
    </div>

    <!-- Progress Pengguna Teraktif -->
    <div class="adm-card">
      <div class="adm-card-hd">
        <h3>
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#3b5bdb" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          Progress Pengguna Teraktif
        </h3>
        <span class="adm-card-sub">Modul dibaca ÷ total modul &amp; quiz dikerjakan ÷ total quiz</span>
      </div>

      <!-- Filter bar -->
      <form method="get" action="laporan.php" style="padding:12px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <span style="font-size:13px;font-weight:600;color:var(--muted)">Filter:</span>
        <select name="pf_jenjang" onchange="this.form.submit()" style="padding:6px 10px;border-radius:8px;border:1.5px solid var(--border);font-size:13px;font-family:inherit;background:#fff;height:34px">
          <option value="">Semua Jenjang</option>
          <?php foreach(['sd','smp','sma','sim'] as $opt): ?>
          <option value="<?=$opt?>" <?=$pf_jenjang===$opt?'selected':''?>><?=strtoupper($opt)?></option>
          <?php endforeach; ?>
        </select>
        <select name="pf_sekolah" onchange="this.form.submit()" style="padding:6px 10px;border-radius:8px;border:1.5px solid var(--border);font-size:13px;font-family:inherit;background:#fff;height:34px;min-width:160px" <?=empty($pf_jenjang)?'disabled':''?>>
          <option value="">Semua Sekolah</option>
          <?php foreach($sekolah_list as $sk): ?>
          <option value="<?=htmlspecialchars($sk)?>" <?=$pf_sekolah===$sk?'selected':''?>><?=htmlspecialchars($sk)?></option>
          <?php endforeach; ?>
        </select>
        <?php if($pf_jenjang||$pf_sekolah): ?>
        <a href="laporan.php" style="font-size:13px;color:var(--muted);text-decoration:none;padding:6px 12px;border:1.5px solid var(--border);border-radius:8px;background:#fff">Reset</a>
        <?php endif; ?>
        <?php if($pf_jenjang||$pf_sekolah): ?>
        <span style="font-size:12px;color:var(--muted);margin-left:4px"><?=count($top)?> pengguna ditemukan</span>
        <?php endif; ?>
      </form>

      <?php if(!empty($top)): ?>
      <div class="adm-table-wrap">
        <table class="adm-table">
          <thead><tr>
            <th style="text-align:center;width:50px">Rank</th>
            <th>Nama</th>
            <th style="text-align:center">Jenjang</th>
            <th>Sekolah</th>
            <th style="text-align:center">Modul Dibaca</th>
            <th style="text-align:center">Quiz Dikerjakan</th>
            <th style="text-align:center">Skor</th>
            <th style="min-width:160px">Progress</th>
          </tr></thead>
          <tbody>
          <?php foreach($top as $i=>$u):
            $tm = max((int)$u['total_modul'], 1);
            $tq = max((int)$u['total_quiz'], 1);
            $baca = (int)$u['baca'];
            $quiz = (int)$u['quiz_done'];

            // Progress = rata-rata dari 2 komponen:
            // (modul_dibaca / total_modul) + (quiz_dikerjakan / total_quiz) / 2
            $pct_modul = min(round($baca / $tm * 100), 100);
            $pct_quiz  = min(round($quiz / $tq * 100), 100);
            $pct = (int)$u['total_modul'] > 0 && (int)$u['total_quiz'] > 0
              ? round(($pct_modul + $pct_quiz) / 2)
              : max($pct_modul, $pct_quiz);

            $bar_color = $pct>=80?'#10b981':($pct>=40?'#f59e0b':'#ef4444');
          ?>
          <tr>
            <td class="adm-td-id" style="text-align:center">#<?=$i+1?></td>
            <td style="font-weight:600"><?=htmlspecialchars($u['nama'])?></td>
            <td style="text-align:center">
              <?php $ju=strtoupper($u['jenjang']??'');$bc=$ju==='SMA'?'b-sma':($ju==='SMP'?'b-smp':($ju==='SD'?'b-sd':'b-sim'));?>
              <span class="badge <?=$bc?>"><?=$ju?></span>
            </td>
            <td style="font-size:12px;color:var(--muted)"><?=htmlspecialchars($u['asal_sekolah']??'–')?></td>
            <td style="text-align:center">
              <span style="font-weight:600"><?=$baca?></span>
              <span style="color:var(--muted);font-size:12px"> / <?=(int)$u['total_modul']?></span>
            </td>
            <td style="text-align:center">
              <span style="font-weight:600"><?=$quiz?></span>
              <span style="color:var(--muted);font-size:12px"> / <?=(int)$u['total_quiz']?></span>
            </td>
            <td style="text-align:center;color:var(--success);font-weight:600">
              <?= $u['rata_skor'] ?? '–' ?>
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <div class="adm-bar-wrap" style="flex:1;margin-bottom:0">
                  <div class="adm-bar" style="width:<?=$pct?>%;background:<?=$bar_color?>"></div>
                </div>
                <span style="font-size:12px;font-weight:600;color:<?=$bar_color?>;min-width:36px;text-align:right"><?=$pct?>%</span>
              </div>
              <div style="font-size:11px;color:var(--muted);margin-top:4px">
                Modul <?=$pct_modul?>% · Quiz <?=$pct_quiz?>%
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div style="padding:40px;text-align:center;color:var(--muted)">Belum ada data progress pengguna.</div>
      <?php endif; ?>
    </div>

    <div class="adm-footer">&copy; <?=date('Y')?> Edu Lalin &ndash; Panel Admin</div>
  </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
var AKT_DATA = <?= json_encode($akt_js) ?>;
var aktChart = null;

function switchAkt(jenjang, btn) {
  document.querySelectorAll('.akt-tab').forEach(function(t){
    t.style.background = 'transparent';
    t.style.color = 'var(--muted)';
    t.style.borderColor = 'var(--border)';
    t.classList.remove('akt-tab-active');
  });
  btn.style.background = '#3b5bdb';
  btn.style.color = '#fff';
  btn.style.borderColor = '#3b5bdb';
  btn.classList.add('akt-tab-active');
  buildAktChart(jenjang);
}

function buildAktChart(jenjang) {
  var d = AKT_DATA[jenjang] || {names:[],baca:[],quiz:[]};
  var ctx = document.getElementById('aktChart');
  var empty = document.getElementById('aktEmpty');
  var summary = document.getElementById('aktSummary');
  if (!ctx) return;
  if (aktChart) { aktChart.destroy(); aktChart = null; }
  if (!d.names.length) {
    ctx.style.display = 'none';
    if (empty) empty.style.display = 'block';
    if (summary) summary.style.display = 'none';
    return;
  }
  ctx.style.display = 'block';
  if (empty) empty.style.display = 'none';
  if (summary) summary.style.display = 'flex';
  aktChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: d.names,
      datasets: [
        { label:'Modul Dibaca',    data:d.baca, backgroundColor:'rgba(59,91,219,0.85)', borderWidth:0, borderRadius:5 },
        { label:'Quiz Dikerjakan', data:d.quiz, backgroundColor:'rgba(16,185,129,0.85)', borderWidth:0, borderRadius:5 }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      scales: {
        x: { grid:{display:false}, ticks:{font:{size:12}} },
        y: { beginAtZero:true, grid:{color:'#f0f1f5'}, ticks:{stepSize:1,font:{size:11}} }
      },
      plugins: {
        legend: { display:false },
        tooltip: { backgroundColor:'#1a1d2e', padding:10, cornerRadius:8,
          callbacks: { label: function(c){ return c.dataset.label+': '+c.raw; } }
        }
      },
      barPercentage: 0.75, categoryPercentage: 0.7
    }
  });
  var tb = d.baca.reduce(function(a,b){return a+b;}, 0);
  var tq = d.quiz.reduce(function(a,b){return a+b;}, 0);
  document.getElementById('sumBaca').textContent = tb;
  document.getElementById('sumQuiz').textContent = tq;
}

window.addEventListener('DOMContentLoaded', function() {
  buildAktChart('SD');

  // Chart Progress Per Jenjang
  var jCtx = document.getElementById('jChart');
  if (jCtx) {
    var jd = <?= json_encode(['labels'=>['SD','SMP','SMA','SIM'],'values'=>[$jc['SD']??0,$jc['SMP']??0,$jc['SMA']??0,$jc['SIM']??0]]) ?>;
    new Chart(jCtx, {
      type: 'bar',
      data: {
        labels: jd.labels,
        datasets: [{
          data: jd.values,
          backgroundColor: ['rgba(59,91,219,0.8)','rgba(16,185,129,0.8)','rgba(245,158,11,0.8)','rgba(194,24,91,0.8)'],
          borderColor: ['#3b5bdb','#10b981','#f59e0b','#c2185b'],
          borderWidth: 2,
          borderRadius: 8
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true, max: 100,
            grid: { color: '#f0f1f5' },
            ticks: { callback: function(v){ return v+'%'; }, font: { size: 11 } }
          },
          x: { grid: { display: false }, ticks: { font: { size: 12 } } }
        },
        plugins: {
          legend: { display: false },
          tooltip: { backgroundColor: '#1a1d2e', padding: 10, cornerRadius: 8,
            callbacks: { label: function(ctx){ return ctx.raw + '%'; } }
          }
        }
      }
    });
  }
});
</script>
</body>
</html>