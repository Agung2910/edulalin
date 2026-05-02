<?php
require_once "../config.php";
require_admin();
$flash='';
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['save_settings'])){
  $set=['site_name'=>trim($_POST['site_name']??'Edu Lalin'),'org_name'=>trim($_POST['org_name']??''),'contact_email'=>trim($_POST['contact_email']??''),'contact_phone'=>trim($_POST['contact_phone']??''),'quiz_duration'=>(int)($_POST['quiz_duration']??0),'quiz_max_questions'=>(int)($_POST['quiz_max_questions']??10),'quiz_passing_grade'=>(int)($_POST['quiz_passing_grade']??60)];
  foreach($set as $k=>$v){$s=$conn->prepare("INSERT INTO settings(`key`,`value`)VALUES(?,?)ON DUPLICATE KEY UPDATE `value`=?");if($s){$s->bind_param("sss",$k,$v,$v);$s->execute();$s->close();}}
  $flash='Pengaturan berhasil disimpan!';
}
if(isset($_GET['backup'])&&$_GET['backup']==='1'){
  $bf='backup_'.date('Y-m-d_H-i-s').'.sql';$bp='../backups/'.$bf;if(!is_dir('../backups'))mkdir('../backups',0755,true);
  $tbs=[];$r=$conn->query("SHOW TABLES");while($row=$r->fetch_array())$tbs[]=$row[0];
  $out="-- Backup ".date('Y-m-d H:i:s')."\n\n";
  foreach($tbs as $t){$out.="DROP TABLE IF EXISTS `$t`;\n";$cr=$conn->query("SHOW CREATE TABLE `$t`")->fetch_assoc();$out.=$cr['Create Table'].";\n\n";$rows=$conn->query("SELECT * FROM `$t`");while($row=$rows->fetch_assoc()){$out.="INSERT INTO `$t` VALUES (";$vs=[];foreach($row as $v)$vs[]=$v===null?"NULL":"'".$conn->real_escape_string($v)."'";$out.=implode(",",$vs).");\n";}$out.="\n";}
  file_put_contents($bp,$out);header('Content-Type:application/octet-stream');header('Content-Disposition:attachment;filename="'.$bf.'"');header('Content-Length:'.filesize($bp));readfile($bp);exit;
}
$cur=[];$r=$conn->query("SELECT `key`,`value` FROM settings");if($r)while($row=$r->fetch_assoc())$cur[$row['key']]=$row['value'];
$def=['site_name'=>'Edu Lalin','org_name'=>'','contact_email'=>'admin@example.com','contact_phone'=>'','quiz_duration'=>'0','quiz_max_questions'=>'10','quiz_passing_grade'=>'60'];
$s=array_merge($def,$cur);
$dbs=['Total User'=>$conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'],'Total Modul'=>$conn->query("SELECT COUNT(*) c FROM modul")->fetch_assoc()['c'],'Total Soal'=>$conn->query("SELECT COUNT(*) c FROM quiz")->fetch_assoc()['c'],'Total Progress'=>$conn->query("SELECT COUNT(*) c FROM progress")->fetch_assoc()['c']];
?><!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><title>Pengaturan - Edu Lalin</title>
<link rel="icon" type="image/png" href="../assets/img/logo-jr.png">
<link rel="stylesheet" href="../assets/css/admin.css"></head>
<body><div class="adm-layout">
<?php include 'sidebar.php'; ?>
<div class="adm-main">
  <div class="adm-topbar">
    <div class="adm-topbar-title">Pengaturan</div>
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
  <div class="adm-ph-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></div>
  <div><h1>Pengaturan Sistem</h1><p>Kelola konfigurasi website dan pengaturan quiz</p></div>
</div>

<?php if($flash):?><div class="adm-alert ok">✅ <?=htmlspecialchars($flash)?></div><?php endif;?>

<form method="post">

<div class="adm-form-card">
  <h3><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg> Informasi Website</h3>
  <p>Pengaturan identitas dan kontak website</p>
  <div class="adm-form-row2" style="margin-bottom:16px">
    <div><label class="adm-label">Nama Website</label><input type="text" name="site_name" class="adm-input" value="<?=htmlspecialchars($s['site_name'])?>" required></div>
    <div><label class="adm-label">Nama Organisasi / Sekolah</label><input type="text" name="org_name" class="adm-input" value="<?=htmlspecialchars($s['org_name'])?>"></div>
  </div>
  <div class="adm-form-row2">
    <div><label class="adm-label">Email Kontak</label><input type="email" name="contact_email" class="adm-input" value="<?=htmlspecialchars($s['contact_email'])?>" placeholder="admin@example.com"><span class="adm-field-hint">Email yang bisa dihubungi pengguna</span></div>
    <div><label class="adm-label">Nomor Telepon</label><input type="text" name="contact_phone" class="adm-input" value="<?=htmlspecialchars($s['contact_phone'])?>" placeholder="08123456789"><span class="adm-field-hint">Nomor yang bisa dihubungi</span></div>
  </div>
</div>

<div class="adm-form-card">
  <h3><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> Pengaturan Quiz</h3>
  <p>Konfigurasi durasi, jumlah soal, dan kriteria kelulusan</p>
  <div class="adm-form-row2" style="margin-bottom:16px">
    <div><label class="adm-label">Durasi Quiz (menit)</label><input type="number" name="quiz_duration" class="adm-input" value="<?=htmlspecialchars($s['quiz_duration'])?>" min="0"><span class="adm-field-hint">0 = tanpa batas waktu</span></div>
    <div><label class="adm-label">Jumlah Soal Maksimal</label><input type="number" name="quiz_max_questions" class="adm-input" value="<?=htmlspecialchars($s['quiz_max_questions'])?>" min="5" max="50"><span class="adm-field-hint">Soal diambil acak dari bank soal</span></div>
  </div>
  <div style="max-width:240px"><label class="adm-label">Nilai Kelulusan (Passing Grade)</label><input type="number" name="quiz_passing_grade" class="adm-input" value="<?=htmlspecialchars($s['quiz_passing_grade'])?>" min="0" max="100"><span class="adm-field-hint">Persentase minimal untuk lulus (0–100)</span></div>
</div>

<div class="adm-form-card">
  <h3><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg> Statistik &amp; Backup Database</h3>
  <p>Informasi data saat ini dan opsi backup</p>
  <div class="adm-stat-boxes">
    <?php foreach($dbs as $l=>$c):?><div class="adm-stat-box"><div class="adm-stat-box-num"><?=$c?></div><div class="adm-stat-box-lbl"><?=$l?></div></div><?php endforeach;?>
  </div>
  <a href="?backup=1" class="btn-backup" onclick="return confirm('Download backup database sekarang?')">&#128229; Download Backup</a>
  <div class="adm-info-box"><strong>&#9888;&#65039; Perhatian:</strong> Backup berisi semua data termasuk info user. Simpan di tempat aman.</div>
</div>

<div style="display:flex;gap:12px;padding-top:4px">
  <button type="submit" name="save_settings" class="btn-save">&#128190; Simpan Pengaturan</button>
  <a href="index.php" class="btn-cancel">Batal</a>
</div>

</form>
<div class="adm-footer">&copy; <?=date('Y')?> Edu Lalin &ndash; Panel Admin</div>
</div></div></div>
</body></html>