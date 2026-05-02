<?php
require_once "../config.php";
require_admin();
$flash='';$flash_type='ok';

if(isset($_POST['update_role'])){
  if($_SESSION['role']!=='admin'){$flash='Tidak punya izin.';$flash_type='err';goto skip;}
  $uid=(int)$_POST['user_id'];$nr=$_POST['role'];
  if(in_array($nr,['user','guru','admin'])&&$uid!==(int)$_SESSION['user_id']){
    $s=$conn->prepare("UPDATE users SET role=? WHERE id=?");$s->bind_param("si",$nr,$uid);$s->execute();$s->close();$flash='Role berhasil diupdate.';
  }else{$flash='Tidak bisa mengubah role sendiri.';$flash_type='err';}
}
skip:
if(isset($_GET['archive'])){
  $id=(int)$_GET['archive'];
  if($id!==(int)($_SESSION['user_id']??0)){
    $s=$conn->prepare("UPDATE users SET deleted_at=NOW() WHERE id=?");$s->bind_param("i",$id);$s->execute();$s->close();
    if(function_exists('log_activity'))log_activity('user','archive','User ID '.$id.' diarsipkan');
    $flash='User berhasil diarsipkan.';
  }else{$flash='Tidak dapat mengarsipkan akun sendiri.';$flash_type='err';}
}
if(isset($_GET['delete'])&&$_SESSION['role']==='admin'){
  $id=(int)$_GET['delete'];
  if($id!==(int)($_SESSION['user_id']??0)){
    $s=$conn->prepare("SELECT nama,email FROM users WHERE id=?");$s->bind_param("i",$id);$s->execute();$s->bind_result($nm,$em);$s->fetch();$s->close();
    $conn->query("DELETE FROM user_progress WHERE user_id=$id");
    $conn->query("DELETE FROM quiz_attempts WHERE user_id=$id");
    $s=$conn->prepare("DELETE FROM users WHERE id=?");$s->bind_param("i",$id);$s->execute();$s->close();
    if(function_exists('log_activity'))log_activity('user','delete_permanent','User DIHAPUS: '.$nm.' ('.$em.')');
    $flash='User berhasil dihapus permanen.';
  }else{$flash='Tidak dapat menghapus akun sendiri.';$flash_type='err';}
}

$fj=$_GET['jenjang']??'';$fs=$_GET['sekolah']??'';
$where=['deleted_at IS NULL'];$params=[];$types='';
if(!empty($fj)&&in_array($fj,['SD','SMP','SMA','SMK'])){$where[]="jenjang=?";$params[]=$fj;$types.='s';}
if(!empty($fs)){$where[]="asal_sekolah=?";$params[]=$fs;$types.='s';}
$wc="WHERE ".implode(" AND ",$where);
$s=$conn->prepare("SELECT id,nama,email,role,asal_sekolah,kelas,jenjang,created_at,last_login,is_verified,deleted_at FROM users $wc ORDER BY last_login DESC, CASE WHEN role='admin' THEN 0 WHEN role='guru' THEN 1 ELSE 2 END, CAST(kelas AS UNSIGNED) ASC, nama ASC");
if(!empty($params))$s->bind_param($types,...$params);
$s->execute();$res=$s->get_result();

$schools=[];
if(!empty($fj)){$ss=$conn->prepare("SELECT DISTINCT asal_sekolah FROM users WHERE jenjang=? AND deleted_at IS NULL AND asal_sekolah IS NOT NULL AND asal_sekolah!='' ORDER BY asal_sekolah");$ss->bind_param("s",$fj);$ss->execute();$schools=$ss->get_result()->fetch_all(MYSQLI_ASSOC);}

$tu=$conn->query("SELECT COUNT(*) c FROM users WHERE deleted_at IS NULL")->fetch_assoc()['c'];
$ta=$conn->query("SELECT COUNT(*) c FROM users WHERE role='admin' AND deleted_at IS NULL")->fetch_assoc()['c'];
$tg=$conn->query("SELECT COUNT(*) c FROM users WHERE role='guru' AND deleted_at IS NULL")->fetch_assoc()['c'];
$tv=$conn->query("SELECT COUNT(*) c FROM users WHERE is_verified=1 AND deleted_at IS NULL")->fetch_assoc()['c'];
$to=$conn->query("SELECT COUNT(*) c FROM users WHERE last_login>=NOW()-INTERVAL 5 MINUTE AND deleted_at IS NULL")->fetch_assoc()['c'];
?><!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><title>Kelola Pengguna - Edu Lalin</title>
<link rel="icon" type="image/png" href="../assets/img/logo-jr.png">
<link rel="stylesheet" href="../assets/css/admin.css"></head>
<body><div class="adm-layout">
<?php include 'sidebar.php'; ?>
<div class="adm-main">
  <div class="adm-topbar">
    <div class="adm-topbar-title">Kelola Pengguna</div>
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
  <div class="adm-ph-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
  <div><h1>Kelola Pengguna</h1><p>Manajemen user, akses, role, dan monitoring aktivitas pengguna</p></div>
</div>

<?php if($flash):?><div class="adm-alert <?=$flash_type?>"><?=$flash_type==='ok'?'✅':'❌'?> <?=htmlspecialchars($flash)?></div><?php endif;?>

<div class="adm-stats-5">
  <?php foreach([['Total User',$tu,'#3b5bdb'],['Admin',$ta,'#e53935'],['Guru',$tg,'#f59e0b'],['Terverifikasi',$tv,'#10b981'],['Online',$to,'#10b981']] as [$l,$v,$c]):?>
  <div class="adm-mini-stat"><div class="adm-mini-val" style="color:<?=$c?>"><?=$v?></div><div class="adm-mini-lbl"><?=$l?></div></div>
  <?php endforeach;?>
</div>

<form method="get" action="users.php">
<div class="adm-filter">
  <span class="adm-filter-lbl">Jenjang:</span>
  <select name="jenjang" class="adm-input" style="width:auto;height:36px" onchange="this.form.submit()"><option value="">Semua</option><?php foreach(['SD','SMP','SMA','SMK'] as $j):?><option value="<?=$j?>" <?=$fj===$j?'selected':''?>><?=$j?></option><?php endforeach;?></select>
  <span class="adm-filter-lbl">Sekolah:</span>
  <select name="sekolah" class="adm-input" style="width:auto;height:36px" <?=empty($fj)?'disabled':''?>><option value="">Semua</option><?php foreach($schools as $sc):?><option value="<?=htmlspecialchars($sc['asal_sekolah'])?>" <?=$fs===$sc['asal_sekolah']?'selected':''?>><?=htmlspecialchars($sc['asal_sekolah'])?></option><?php endforeach;?></select>
  <button class="btn-filter" type="submit">Terapkan</button>
  <a href="users.php" class="btn-reset">Reset</a>
  <a href="download_users.php?jenjang=<?=urlencode($fj)?>&sekolah=<?=urlencode($fs)?>" class="btn-dl">&#11015; Download Excel</a>
</div>
</form>

<div class="adm-card" style="overflow:hidden;min-width:0">
  <div class="adm-card-hd">
    <h3><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#3b5bdb" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg> Daftar Pengguna<?=$fj?' – '.$fj:''?></h3>
    <span style="font-size:13px;color:var(--muted)"><?=$res->num_rows?> pengguna<?=$fj?' (dari '.$tu.' total)':''?></span>
  </div>
  <?php if($res&&$res->num_rows>0):?>
  <div style="overflow-x:auto;max-width:100%">
  <table class="adm-table" style="table-layout:fixed;min-width:1060px;width:100%">
    <colgroup>
      <col style="width:140px"> <!-- Pengguna -->
      <col style="width:185px"> <!-- Email -->
      <col style="width:150px"> <!-- Sekolah -->
      <col style="width:70px">  <!-- Jenjang -->
      <col style="width:105px"> <!-- Role -->
      <col style="width:78px">  <!-- Daftar -->
      <col style="width:105px"> <!-- Aktivitas -->
      <col style="width:55px">  <!-- Status -->
      <col style="width:147px"> <!-- Aksi -->
    </colgroup>
    <thead><tr>
      <th>Pengguna</th>
      <th>Email</th>
      <th>Sekolah</th>
      <th style="text-align:center">Jenjang</th>
      <th style="text-align:center">Role</th>
      <th style="text-align:center">Daftar</th>
      <th style="text-align:center">Aktivitas</th>
      <th style="text-align:center">Status</th>
      <th style="text-align:center">Aksi</th>
    </tr></thead>
    <tbody>
    <?php while($u=$res->fetch_assoc()):$jl=strtolower($u['jenjang']??'');$bjc=$jl==='sma'?'b-sma':($jl==='smp'?'b-smp':($jl==='sd'?'b-sd':($jl==='smk'?'b-smk':'')));?>
    <tr>
      <td style="overflow:hidden;font-weight:600;font-size:13.5px"><?=htmlspecialchars($u['nama'])?><?php if($u['is_verified']):?> <span class="adm-verified">✓</span><?php endif;?></td>
      <td style="font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?=htmlspecialchars($u['email'])?>"><?=htmlspecialchars($u['email'])?></td>
      <td style="font-size:13px;overflow:hidden" title="<?=htmlspecialchars($u['asal_sekolah']??'')?>">
        <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($u['asal_sekolah']??'–')?></div>
        <?php if($u['kelas']):?><div style="font-size:11px;color:var(--muted);margin-top:2px">Kelas <?=$u['kelas']?></div><?php endif;?>
      </td>
      <td style="text-align:center"><?=$u['jenjang']?'<span class="badge b-jenjang '.$bjc.'">'.strtoupper($u['jenjang']).'</span>':'–'?></td>
      <td style="text-align:center"><?php if($u['id']!=$_SESSION['user_id']):?><form method="post" style="margin:0"><input type="hidden" name="user_id" value="<?=$u['id']?>"><input type="hidden" name="update_role" value="1"><select name="role" class="adm-role-sel" onchange="this.form.submit()"><option value="user" <?=$u['role']==='user'?'selected':''?>>User</option><option value="guru" <?=$u['role']==='guru'?'selected':''?>>Guru</option><option value="admin" <?=$u['role']==='admin'?'selected':''?>>Admin</option></select></form><?php else:?><span class="badge b-admin">ADMIN</span><?php endif;?></td>
      <td style="text-align:center;font-size:12px"><?=date('d M Y',strtotime($u['created_at']))?></td>
      <td style="text-align:center;font-size:12px"><?php if($u['last_login']){$diff=time()-strtotime($u['last_login']);if($diff<300)echo'<span class="b-online">&#9679; Online</span>';elseif($diff<3600)echo floor($diff/60).' mnt lalu';else echo date('d M Y H:i',strtotime($u['last_login']));}else echo'<span style="color:var(--muted)">Belum login</span>';?></td>
      <td style="text-align:center"><?=$u['deleted_at']===null?'<span class="b-active">Aktif</span>':'<span class="b-archived">Terarsip</span>'?></td>
      <td><div class="adm-acts" style="justify-content:center">
        <button class="btn-detail" onclick="showDetail(<?=$u['id']?>)">&#128203;</button>
        <?php if($u['id']!=$_SESSION['user_id']):?>
        <a class="btn-arc" href="users.php?archive=<?=$u['id']?>" onclick="return confirm('Arsipkan user ini?')">Arsip</a>
        <?php if($_SESSION['role']==='admin'):?><a class="btn-perm" href="users.php?delete=<?=$u['id']?>" onclick="return confirm('Hapus PERMANEN? Tidak bisa dipulihkan!')">Hapus</a><?php endif;?>
        <?php endif;?>
      </div></td>
    </tr>
    <?php endwhile;?>
    </tbody>
  </table></div></div>
  <?php else:?><div style="padding:48px;text-align:center;color:var(--muted)"><?=$fj?'Tidak ada pengguna di jenjang '.$fj:'Belum ada pengguna terdaftar.'?></div><?php endif;?>
</div>

<div id="userModal" style="display:none" class="adm-modal-bg">
  <div class="adm-modal"><h3>Detail Pengguna</h3><div id="modalContent">Loading...</div><button class="adm-modal-close" onclick="closeModal()">Tutup</button></div>
</div>

<div class="adm-footer">&copy; <?=date('Y')?> Edu Lalin &ndash; Panel Admin</div>
</div></div></div>
<script>
function showDetail(id){fetch('user_detail.php?id='+id).then(r=>r.text()).then(h=>{document.getElementById('modalContent').innerHTML=h;document.getElementById('userModal').style.display='flex';});}
function closeModal(){document.getElementById('userModal').style.display='none';}
</script>
</body></html>