<?php
require_once "../config.php";
require_admin();
$flash='';$flash_type='ok';
if(isset($_GET['restore'])){$id=(int)$_GET['restore'];$s=$conn->prepare("UPDATE users SET deleted_at=NULL,archived_reason=NULL WHERE id=? AND deleted_at IS NOT NULL");$s->bind_param("i",$id);$s->execute();$flash=$s->affected_rows>0?'User berhasil dipulihkan.':'User tidak ditemukan.';if($s->affected_rows<=0)$flash_type='err';$s->close();}
if(isset($_GET['perm'])){$id=(int)$_GET['perm'];$conn->query("DELETE FROM users WHERE id=$id AND deleted_at IS NOT NULL");$flash='User dihapus permanen.';}
$res=$conn->query("SELECT id,nama,email,role,archived_reason,deleted_at FROM users WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
$total=$res?$res->num_rows:0;
?><!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><title>Pengguna Terhapus - Edu Lalin</title>
<link rel="icon" type="image/png" href="../assets/img/logo-jr.png">
<link rel="stylesheet" href="../assets/css/admin.css"></head>
<body><div class="adm-layout">
<?php include 'sidebar.php'; ?>
<div class="adm-main">
  <div class="adm-topbar">
    <div class="adm-topbar-title">Pengguna Terhapus</div>
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
  <div class="adm-ph-icon" style="background:linear-gradient(135deg,#e53935,#c62828)"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></div>
  <div><h1>Pengguna Terhapus</h1><p>Daftar akun yang dinonaktifkan — dapat dipulihkan kapan saja</p></div>
</div>

<?php if($flash):?><div class="adm-alert <?=$flash_type?>"><?=$flash_type==='ok'?'✅':'❌'?> <?=htmlspecialchars($flash)?></div><?php endif;?>

<div class="adm-alert inf">ℹ️ User terarsip <strong>tidak dapat login</strong>, namun semua data tetap tersimpan. Klik <strong>Pulihkan</strong> untuk mengaktifkan kembali.</div>

<div class="adm-card">
  <div class="adm-card-hd">
    <h3><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#e53935" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg> Daftar Pengguna Terarsip</h3>
    <span class="badge b-red"><?=$total?> pengguna</span>
  </div>
  <?php if($total>0):$res->data_seek(0);?>
  <div class="adm-table-wrap"><table class="adm-table">
    <thead><tr><th>Pengguna</th><th>Email</th><th style="text-align:center">Role</th><th>Alasan Arsip</th><th style="text-align:center">Tanggal Arsip</th><th style="text-align:center">Aksi</th></tr></thead>
    <tbody>
    <?php while($u=$res->fetch_assoc()):$rb=$u['role']==='admin'?'b-admin':($u['role']==='guru'?'b-guru':'b-user');?>
    <tr>
      <td><div class="adm-user-info"><div class="adm-avatar" style="background:linear-gradient(135deg,#9ca3af,#6b7280)"><?=strtoupper(substr($u['nama'],0,1))?></div><div><strong><?=htmlspecialchars($u['nama'])?></strong><br><small style="color:var(--muted);font-size:11px">ID: <?=$u['id']?></small></div></div></td>
      <td style="font-size:13px"><?=htmlspecialchars($u['email'])?></td>
      <td style="text-align:center"><span class="badge <?=$rb?>"><?=strtoupper($u['role'])?></span></td>
      <td style="font-size:13px"><?=$u['archived_reason']==='inactive_1_year'?'<span style="color:var(--warning)">Tidak aktif &gt; 1 tahun</span>':'Dinonaktifkan Admin'?></td>
      <td style="text-align:center;font-size:12.5px"><?=date('d M Y',strtotime($u['deleted_at']))?><br><small style="color:var(--muted)"><?=date('H:i',strtotime($u['deleted_at']))?></small></td>
      <td><div class="adm-acts" style="justify-content:center"><a href="?restore=<?=$u['id']?>" class="btn-res" onclick="return confirm('Pulihkan user ini?')">&#128260; Pulihkan</a><a href="?perm=<?=$u['id']?>" class="btn-perm" onclick="return confirm('Hapus permanen? Tidak bisa dipulihkan!')">&#128465; Hapus Permanen</a></div></td>
    </tr>
    <?php endwhile;?>
    </tbody>
  </table></div>
  <?php else:?>
  <div style="padding:60px;text-align:center">
    <div style="font-size:44px;margin-bottom:12px">🗑️</div>
    <div style="font-size:15px;font-weight:600;margin-bottom:6px">Tidak ada pengguna terarsip</div>
    <div style="font-size:13px;color:var(--muted)">Semua akun pengguna saat ini aktif.</div>
  </div>
  <?php endif;?>
</div>
<div class="adm-footer">&copy; <?=date('Y')?> Edu Lalin &ndash; Panel Admin</div>
</div></div></div>
</body></html>