<?php
require_once "../config.php";
require_admin();
global $conn;

$res=$conn->query("SELECT COUNT(*) AS jml FROM users");$row=$res->fetch_assoc();$total_pengguna=(int)$row['jml'];
$res=$conn->query("SELECT COUNT(*) AS jml FROM modul WHERE is_active=1");$row=$res->fetch_assoc();$total_modul=(int)$row['jml'];
$total_soal=0;if($conn->query("SHOW TABLES LIKE 'quiz'")->num_rows>0){$res=$conn->query("SELECT COUNT(*) AS jml FROM quiz");$row=$res->fetch_assoc();$total_soal=(int)$row['jml'];}
$res=$conn->query("SELECT COUNT(*) AS jml FROM progress WHERE status='selesai'");$row=$res->fetch_assoc();$total_quiz_selesai=(int)$row['jml'];

$resAkt=$conn->query("SELECT waktu,tipe,aksi,keterangan FROM activity_log ORDER BY waktu DESC LIMIT 5");
$activities=[];if($resAkt)while($r=$resAkt->fetch_assoc())$activities[]=$r;

$resMod=$conn->query("SELECT id,judul,jenjang,kelas FROM modul WHERE is_active=1 ORDER BY id DESC LIMIT 5");
$modul_terbaru=[];if($resMod)while($r=$resMod->fetch_assoc())$modul_terbaru[]=$r;

$resTop=$conn->query("SELECT m.judul,m.jenjang,m.kelas,COUNT(p.id) AS total FROM modul m LEFT JOIN progress p ON p.modul_id=m.id WHERE m.is_active=1 GROUP BY m.id ORDER BY total DESC LIMIT 3");
$top_modul=[];if($resTop)while($r=$resTop->fetch_assoc())$top_modul[]=$r;

$chart_data=[];$chart_labels=[];
for($i=6;$i>=0;$i--){
  $date=date('Y-m-d',strtotime("-$i days"));$chart_labels[]=date('j M',strtotime("-$i days"));
  $res=$conn->query("SELECT COUNT(DISTINCT user_id) AS jml FROM activity_log WHERE DATE(waktu)='$date'");
  $r=$res?$res->fetch_assoc():['jml'=>0];$chart_data[]=(int)$r['jml'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Dashboard Admin - Edu Lalin</title>
<link rel="icon" type="image/png" href="../assets/img/logo-jr.png">
<link rel="stylesheet" href="../assets/css/admin.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="adm-layout">

<?php include 'sidebar.php'; ?>

<div class="adm-main">

  <div class="adm-topbar">
    <div class="adm-topbar-title">Dashboard Admin</div>
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
      <div class="adm-ph-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></div>
      <div><h1>Dashboard Admin</h1><p>Ringkasan sistem dan aktivitas terkini platform Edu Lalin</p></div>
    </div>

    <div class="adm-stats">
      <div class="adm-stat"><div class="adm-stat-icon blue"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3b5bdb" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div><div><div class="adm-stat-label">Total Pengguna</div><div class="adm-stat-value"><?= number_format($total_pengguna) ?></div></div></div>
      <div class="adm-stat"><div class="adm-stat-icon green"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0d9a6a" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></div><div><div class="adm-stat-label">Modul Aktif</div><div class="adm-stat-value"><?= number_format($total_modul) ?></div></div></div>
      <div class="adm-stat"><div class="adm-stat-icon yellow"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#c68c00" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div><div><div class="adm-stat-label">Total Quiz</div><div class="adm-stat-value"><?= number_format($total_soal) ?></div></div></div>
      <div class="adm-stat"><div class="adm-stat-icon red"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#e53935" stroke-width="2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2z"/></svg></div><div><div class="adm-stat-label">Quiz Selesai</div><div class="adm-stat-value"><?= number_format($total_quiz_selesai) ?></div></div></div>
    </div>

    <div class="adm-2col">
      <div class="adm-card">
        <div class="adm-card-hd">
          <h3><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3b5bdb" stroke-width="2"><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg> Pengguna Aktif (7 hari)</h3>
          <div class="adm-chart-pill"><div class="adm-chart-dot"></div> Pengguna Aktif</div>
        </div>
        <div class="adm-card-body"><div class="adm-chart-wrap"><canvas id="usersChart"></canvas></div></div>
      </div>

      <div class="adm-card" style="display:flex;flex-direction:column;">
        <div class="adm-card-hd"><h3><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg> Aktivitas Terbaru</h3></div>
        <ul class="adm-act-list" style="flex:1;">
          <?php if(!empty($activities)):foreach($activities as $act):?>
          <li class="adm-act-item">
            <div class="adm-act-bar"></div>
            <div>
              <div class="adm-act-title"><strong>[<?= htmlspecialchars($act['tipe']).' '.htmlspecialchars($act['aksi']) ?>]</strong> <?= htmlspecialchars($act['keterangan']) ?></div>
              <div class="adm-act-time"><?= date('d/m/Y H:i',strtotime($act['waktu'])) ?></div>
            </div>
          </li>
          <?php endforeach;else:?>
          <li class="adm-act-item" style="color:var(--muted);font-size:13px;">Belum ada aktivitas tercatat.</li>
          <?php endif;?>
        </ul>
        <a href="aktivitas.php" class="adm-see-all">Lihat Semua Aktivitas &#8594;</a>
      </div>
    </div>

    <div class="adm-2col-eq">
      <div class="adm-card">
        <div class="adm-card-hd">
          <h3><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3b5bdb" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg> Modul Terbaru</h3>
          <span class="adm-card-sub">5 modul terakhir</span>
        </div>
        <?php if(!empty($modul_terbaru)):?>
        <div class="adm-table-wrap"><table class="adm-table">
          <thead><tr><th style="text-align:center">ID</th><th>Judul</th><th style="text-align:center">Jenjang</th><th style="text-align:center">Kelas</th></tr></thead>
          <tbody>
          <?php foreach($modul_terbaru as $m):$ju = strtoupper($m['jenjang'] ?? '');$bc=$ju==='SMA'?'b-sma':($ju==='SMP'?'b-smp':($ju==='SD'?'b-sd':($ju==='SIM'?'b-sim':'')));?>
          <tr>
            <td class="adm-td-id" style="text-align:center">#<?=(int)$m['id']?></td>
            <td><?=htmlspecialchars($m['judul'])?></td>
            <td style="text-align:center"><span class="badge <?=$bc?>"><?=$ju?></span></td>
            <td style="text-align:center;font-weight:600"><?=$m['kelas']?'Kelas '.htmlspecialchars($m['kelas']):'–'?></td>
          </tr>
          <?php endforeach;?>
          </tbody>
        </table></div>
        <?php else:?><div style="padding:36px;text-align:center;color:var(--muted);font-size:13px">Belum ada modul.</div><?php endif;?>
        <div style="padding:11px 16px;border-top:1px solid var(--border)">
          <a href="modul.php" style="font-size:13px;color:var(--accent);text-decoration:none;font-weight:600">Lihat Semua Modul &#8594;</a>
        </div>
      </div>

      <div class="adm-card" style="display:flex;flex-direction:column;">
        <div class="adm-card-hd"><h3><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> Top Modul Terpopuler</h3></div>
        <ul class="adm-top-list" style="flex:1;">
          <?php if(!empty($top_modul)):foreach($top_modul as $tm):?>
          <li class="adm-top-item">
            <div class="adm-top-dot"></div>
            <div><div class="adm-top-title"><?=htmlspecialchars($tm['judul'])?></div><div class="adm-top-meta"><?=strtoupper($tm['jenjang'])?>: <?=(int)$tm['total']?></div></div>
          </li>
          <?php endforeach;else:?>
          <li class="adm-top-item" style="color:var(--muted);font-size:13px">Belum ada data.</li>
          <?php endif;?>
        </ul>
      </div>
    </div>

    <div class="adm-footer">&copy; <?=date('Y')?> Edu Lalin &ndash; Panel Admin</div>
  </div>
</div>
</div>

<script>
window.addEventListener('DOMContentLoaded',function(){
  var ctx=document.getElementById('usersChart');if(!ctx)return;
  new Chart(ctx,{type:'line',data:{labels:<?=json_encode($chart_labels)?>,datasets:[{label:'Pengguna aktif',data:<?=json_encode($chart_data)?>,borderColor:'#3b5bdb',backgroundColor:'rgba(59,91,219,0.08)',tension:0.4,fill:true,pointRadius:5,pointBackgroundColor:'#3b5bdb',pointBorderColor:'#fff',pointBorderWidth:2,pointHoverRadius:7}]},options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true,grid:{color:'#f0f1f5'},ticks:{stepSize:5,font:{size:11}}},x:{grid:{display:false},ticks:{font:{size:11}}}},plugins:{legend:{display:false},tooltip:{backgroundColor:'#1a1d2e',padding:10,cornerRadius:8}}}});
});
</script>
</body>
</html>