<?php
require_once "../config.php";
require_admin_or_guru();

$flash='';$flash_type='ok';

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['add_modul'])){
  if(!is_admin()){$flash='Anda tidak memiliki akses untuk menambah modul!';$flash_type='err';}
  else{
    $tipe=trim($_POST['tipe_modul']??'');
    $jenjang=trim($_POST['jenjang']??'');
    $kelas=trim($_POST['kelas']??'');
    $judul=trim($_POST['judul']??'');
    $desk=trim($_POST['deskripsi']??'');
    $filePath=null;$videoUrl=null;
    $kategori=trim($_POST['kategori_modul']??'');
    $minUsia=!empty($_POST['min_usia'])?(int)$_POST['min_usia']:null;

    // Validasi bertahap — semua pakai if-elseif agar berhenti di error pertama
    if($judul===''){
      $flash='Judul wajib diisi.';$flash_type='err';
    }elseif($tipe===''){
      $flash='Pilih tipe modul.';$flash_type='err';
    }elseif($kategori===''){
      $flash='Kategori modul wajib dipilih.';$flash_type='err';
    }elseif($kategori==='sekolah'&&($jenjang===''||$kelas==='')){
      $flash='Jenjang dan kelas wajib diisi untuk modul sekolah.';$flash_type='err';
    }elseif($kategori==='sim'&&empty($minUsia)){
      $flash='Minimal usia wajib diisi untuk modul SIM.';$flash_type='err';
    }

    // Hanya lanjut kalau belum ada error
    if($flash===''){
      if($tipe==='pdf'||$tipe==='ppt'){
        if(!empty($_FILES['file_modul']['name'])&&$_FILES['file_modul']['error']===UPLOAD_ERR_OK){
          $uploadDir='../uploads/modul/';
          if(!is_dir($uploadDir))mkdir($uploadDir,0777,true);
          $ext=strtolower(pathinfo($_FILES['file_modul']['name'],PATHINFO_EXTENSION));
          if(in_array($ext,['pdf','ppt','pptx'],true)){
            $newName='modul_'.time().'_'.rand(1000,9999).'.'.$ext;
            if(move_uploaded_file($_FILES['file_modul']['tmp_name'],$uploadDir.$newName)){
              $filePath='uploads/modul/'.$newName;
            }else{
              $flash='Gagal mengupload file.';$flash_type='err';
            }
          }else{
            $flash='File harus PDF, PPT, atau PPTX.';$flash_type='err';
          }
        }else{
          $flash='File dokumen wajib untuk tipe PDF/PPT.';$flash_type='err';
        }
      }

      if($flash===''&&$tipe==='video'){
        $videoUrl=trim($_POST['video_url']??'');
        if($videoUrl===''){
          $flash='Link video wajib diisi.';$flash_type='err';
        }elseif(!filter_var($videoUrl,FILTER_VALIDATE_URL)){
          $flash='Format link video tidak valid.';$flash_type='err';
        }
      }
    }

    if($flash===''){
      $tipeDb = in_array($tipe,['pdf','ppt']) ? $tipe : 'video';

      if($kategori==='sim'){
        $jenjang = null;
        $kelas   = null;
        $minUsia = (int)$minUsia;
      } else {
        $minUsia = null;
        $kelas = ($kelas !== '' && $kelas !== null && $kelas !== '0') ? (int)$kelas : null;
      }
      // Sebelum prepare, pastikan null benar-benar null
      $jenjangVal = ($jenjang !== '' && $jenjang !== null) ? $jenjang : null;
      $kelasVal   = ($kelas   !== '' && $kelas   !== null && (int)$kelas > 0) ? (int)$kelas : null;
      $minUsiaVal = ($minUsia !== null && $minUsia > 0) ? (int)$minUsia : null;

      $sql = "INSERT INTO modul (judul, deskripsi, file_path, video_url, tipe, kategori, is_active, jenjang, kelas, min_usia) 
              VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?)";

      $stmt = $conn->prepare($sql);
      $stmt->bind_param(
          "ssssssssi",   // ← jenjang pakai "s", kelas & min_usia pakai "i"... tapi kelas bisa null
          $judul,
          $desk,
          $filePath,
          $videoUrl,
          $tipeDb,
          $kategori,
          $jenjangVal,
          $kelasVal,
          $minUsiaVal
      );

      if (!$stmt->execute()) {
          die("Execute error: " . $stmt->error);
      }

      $stmt->close();

      if(function_exists('log_activity')) {
          log_activity('modul','tambah','Modul baru: '.$judul);
      }

      $flash = 'Modul berhasil ditambahkan!';

      header("Location: modul.php?msg=tambah");
      exit;
    }
  }
}

if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['del'])){
  if(!is_admin()){$flash='Tidak ada akses!';$flash_type='err';}
  else{
    $id=(int)$_GET['del'];
    $q=$conn->prepare("SELECT judul,file_path FROM modul WHERE id=?");
    $q->bind_param("i",$id);$q->execute();$q->bind_result($jH,$fH);$q->fetch();$q->close();
    if(!empty($fH)){$f='../'.$fH;if(is_file($f))@unlink($f);}
    $conn->query("DELETE FROM modul WHERE id=".$id);
    if(function_exists('log_activity'))log_activity('modul','hapus','Modul dihapus: '.($jH?:'ID '.$id));
    $flash='Modul berhasil dihapus!';
    
    header("Location: modul.php?msg=hapus");
    exit;
  }
}

// Filter
$filter_tipe=$_GET['tipe']??'semua';
$filter_kat=$_GET['kat']??'semua';

$wheres=[];
if($filter_tipe==='pdf')   $wheres[]="tipe='pdf'";
elseif($filter_tipe==='ppt')   $wheres[]="tipe='ppt'";
elseif($filter_tipe==='video') $wheres[]="tipe='video'";

if($filter_kat==='sekolah') $wheres[]="kategori='sekolah'";
elseif($filter_kat==='sim') $wheres[]="kategori='sim'";

$wt=count($wheres)?'WHERE '.implode(' AND ',$wheres):'';
$res=$conn->query("SELECT * FROM modul $wt ORDER BY id DESC");

if(isset($_GET['msg'])){
  if($_GET['msg']=='tambah'){
    $flash = 'Modul berhasil ditambahkan!';
  } elseif($_GET['msg']=='hapus'){
    $flash = 'Modul berhasil dihapus!';
  }
}

?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><title>Kelola Modul - Edu Lalin</title>
<link rel="icon" type="image/png" href="../assets/img/logo-jr.png">
<link rel="stylesheet" href="../assets/css/admin.css"></head>
<body><div class="adm-layout">
<?php include 'sidebar.php'; ?>
<div class="adm-main">
  <div class="adm-topbar">
    <div class="adm-topbar-title">Kelola Modul</div>
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
  <div class="adm-ph-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></div>
  <div><h1>Kelola Modul Pembelajaran</h1><p>Tambah, edit, dan kelola modul dalam format PDF, PPT, atau Video</p></div>
</div>

<?php if($flash): ?><div class="adm-alert <?=$flash_type?>"><?=$flash_type==='ok'?'✅':'❌'?> <?=htmlspecialchars($flash)?></div><?php endif; ?>

<?php if(is_admin()): ?>
<div class="adm-form-card">
  <h3><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg> Tambah Modul Baru</h3>
  <p>Upload modul pembelajaran dengan file PDF, PPT, atau link video</p>
  <form method="post" enctype="multipart/form-data" class="adm-form-grid" id="formModul">
    <div class="adm-form-col">
      <div>
        <label class="adm-label">Kategori Modul</label>
        <select name="kategori_modul" id="kat" class="adm-input" required>
          <option value="">Pilih kategori</option>
          <option value="sekolah">Modul Sekolah</option>
          <option value="sim">Modul SIM</option>
        </select>
      </div>
      <div id="g_jenjang">
        <label class="adm-label">Jenjang Pendidikan</label>
        <select name="jenjang" id="inp_jenjang" class="adm-input" required>
          <option value="">Pilih jenjang</option>
          <option value="sd">SD</option>
          <option value="smp">SMP</option>
          <option value="sma">SMA</option>
        </select>
      </div>
      <div id="g_kelas">
        <label class="adm-label">Kelas</label>
        <input type="text" name="kelas" id="inp_kelas" class="adm-input" placeholder="1, 5, 8, 12">
      </div>
      <div id="g_usia" style="display:none">
        <label class="adm-label">Minimal Usia</label>
        <input type="number" name="min_usia" class="adm-input" min="1" placeholder="17">
        <span class="adm-field-hint">Khusus modul SIM</span>
      </div>
      <div>
        <label class="adm-label">Tipe Modul</label>
        <select name="tipe_modul" id="tipe" class="adm-input" required>
          <option value="">-- Pilih tipe --</option>
          <option value="pdf">📄 File PDF</option>
          <option value="ppt">📊 PPT</option>
          <option value="video">🎥 Video</option>
        </select>
      </div>
      <div>
        <label class="adm-label">Judul Modul</label>
        <input type="text" name="judul" class="adm-input" placeholder="Judul modul..." required>
      </div>
    </div>
    <div class="adm-form-col">
      <div id="g_desk" style="display:none">
        <label class="adm-label">Deskripsi</label>
        <textarea name="deskripsi" rows="4" class="adm-input" placeholder="Ringkasan tujuan pembelajaran..."></textarea>
      </div>
      <div id="g_doc" style="display:none">
        <label class="adm-label">File Modul <span class="adm-hint">Maks 50MB</span></label>
        <input type="file" name="file_modul" accept=".pdf,.ppt,.pptx" class="adm-input">
        <div class="adm-info-box" style="margin-top:8px"><strong>ℹ️</strong> PDF, PPT, atau PPTX</div>
      </div>
      <div id="g_vid" style="display:none">
        <label class="adm-label">Link Video <span class="adm-hint">YouTube, Drive, dll</span></label>
        <input type="text" name="video_url" class="adm-input" placeholder="https://youtube.com/...">
        <div class="adm-info-box" style="margin-top:8px"><strong>ℹ️</strong> YouTube, Google Drive, Vimeo</div>
      </div>
      <button type="submit" name="add_modul" class="btn-save" style="margin-top:8px">💾 Simpan Modul</button>
    </div>
  </form>
</div>
<?php else: ?>
<div class="adm-alert inf">ℹ️ <strong>Mode Read-Only:</strong> Anda hanya dapat melihat data modul.</div>
<?php endif; ?>

<div class="adm-card">
  <div class="adm-card-hd">
    <h3><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#3b5bdb" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg> Daftar Modul</h3>
    <span class="badge b-user">Total: <?=$res?$res->num_rows:0?> modul</span>
  </div>

  <!-- FIX: Filter sekarang ada kategori (sim/sekolah) + tipe -->
  <div style="padding:12px 18px;border-bottom:1px solid var(--border);">
    <form method="get" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
      <span class="adm-filter-lbl">Filter:</span>

      <select name="kat" class="adm-input" style="width:auto;height:36px" onchange="this.form.submit()">
        <option value="semua" <?=$filter_kat==='semua'?'selected':''?>>Semua Kategori</option>
        <option value="sekolah" <?=$filter_kat==='sekolah'?'selected':''?>>🏫 Sekolah</option>
        <option value="sim"     <?=$filter_kat==='sim'?'selected':''?>>🚗 SIM</option>
      </select>

      <select name="tipe" class="adm-input" style="width:auto;height:36px" onchange="this.form.submit()">
        <option value="semua" <?=$filter_tipe==='semua'?'selected':''?>>Semua Tipe</option>
        <option value="pdf"   <?=$filter_tipe==='pdf'?'selected':''?>>📄 PDF</option>
        <option value="ppt"   <?=$filter_tipe==='ppt'?'selected':''?>>📊 PPT</option>
        <option value="video" <?=$filter_tipe==='video'?'selected':''?>>🎥 Video</option>
      </select>

      <?php if($filter_tipe!=='semua'||$filter_kat!=='semua'): ?>
        <a href="modul.php" class="btn-reset" style="height:36px;display:flex;align-items:center;font-size:13px">✕ Reset</a>
      <?php endif; ?>
    </form>
  </div>

  <?php if($res&&$res->num_rows>0): ?>
  <div class="adm-table-wrap"><table class="adm-table">
    <thead><tr>
      <th style="text-align:center">ID</th>
      <th>Judul</th>
      <th style="text-align:center">Kategori</th>
      <th style="text-align:center">Jenjang</th>
      <th style="text-align:center">Kelas</th>
      <th>Deskripsi</th>
      <th style="text-align:center">Tipe</th>
      <th style="text-align:center">File/Link</th>
      <th style="text-align:center">Status</th>
      <?php if(is_admin()):?><th style="text-align:center">Aksi</th><?php endif;?>
    </tr></thead>
    <tbody>
    <?php while($m=$res->fetch_assoc()):
      $ju=strtoupper($m['jenjang']??'');
      $bc=$ju==='SMA'?'b-sma':($ju==='SMP'?'b-smp':($ju==='SD'?'b-sd':''));
      $isSimKat=($m['kategori']??'')==='sim';
    ?>
    <tr>
      <td class="adm-td-id" style="text-align:center">#<?=(int)$m['id']?></td>
      <td style="font-weight:600"><?=htmlspecialchars($m['judul'])?></td>
      <td style="text-align:center">
        <?php if($isSimKat): ?>
          <span class="badge b-sim">🚗 SIM</span>
        <?php else: ?>
          <span class="badge b-smp">🏫 Sekolah</span>
        <?php endif; ?>
      </td>
      <td style="text-align:center">
        <?php if($isSimKat): ?>
          <span style="font-size:12px;color:var(--muted)">Usia ≥ <?=(int)($m['min_usia']??0)?></span>
        <?php else: ?>
          <span class="badge <?=$bc?>"><?=$ju?:'-'?></span>
        <?php endif; ?>
      </td>
      <td style="text-align:center;font-weight:600"><?=$m['kelas']?'Kelas '.htmlspecialchars($m['kelas']):'–'?></td>
      <td style="max-width:200px;font-size:13px;color:var(--muted)"><?=nl2br(htmlspecialchars(substr($m['deskripsi']??'',0,80)))?><?=strlen($m['deskripsi']??'')>80?'...':''?></td>
      <td style="text-align:center">
        <?php if($m['tipe']==='pdf')echo'<span class="badge b-pdf">PDF</span>';
        elseif($m['tipe']==='ppt')echo'<span class="badge b-ppt">PPT</span>';
        else echo'<span class="badge b-video">Video</span>';?>
      </td>
      <td style="text-align:center">
        <?php if(!empty($m['file_path'])):?>
          <a href="../<?=htmlspecialchars($m['file_path'])?>" target="_blank" style="color:var(--accent);font-weight:600;font-size:13px">Lihat File</a>
        <?php elseif(!empty($m['video_url'])):?>
          <a href="<?=htmlspecialchars($m['video_url'])?>" target="_blank" style="color:var(--accent);font-weight:600;font-size:13px">Lihat Video</a>
        <?php else:?>–<?php endif;?>
      </td>
      <td style="text-align:center"><?=!empty($m['is_active'])?'<span class="badge b-green">✓ Aktif</span>':'<span class="badge">Nonaktif</span>'?></td>
      <?php if(is_admin()):?>
      <td><div class="adm-acts" style="justify-content:center">
        <a href="modul_edit.php?id=<?=(int)$m['id']?>" class="btn-edit">Edit</a>
        <a href="modul.php?del=<?=(int)$m['id']?>" class="btn-del" onclick="return confirm('Hapus modul ini?')">Hapus</a>
      </div></td>
      <?php endif;?>
    </tr>
    <?php endwhile;?>
    </tbody>
  </table></div>
  <?php else:?><div style="padding:48px;text-align:center;color:var(--muted)">Belum ada modul<?=($filter_tipe!=='semua'||$filter_kat!=='semua')?' dengan filter ini':''?>.</div><?php endif;?>
</div>

<div class="adm-footer">&copy; <?=date('Y')?> Edu Lalin &ndash; Panel Admin</div>
</div></div></div>

<script>
var kat  = document.getElementById('kat');
var tipe = document.getElementById('tipe');
var gJ   = document.getElementById('g_jenjang');
var gK   = document.getElementById('g_kelas');
var gU   = document.getElementById('g_usia');
var gDoc = document.getElementById('g_doc');
var gVid = document.getElementById('g_vid');
var gDesk= document.getElementById('g_desk');
var inpJ = document.getElementById('inp_jenjang');
var inpK = document.getElementById('inp_kelas');

if(kat) kat.addEventListener('change', function(){
  if(this.value === 'sim'){
    gU.style.display = 'block';
    gJ.style.display = 'none';
    gK.style.display = 'none';

    // kosongin value
    inpJ.value = '';
    inpK.value = '';

    // 🔥 PENTING: matiin required
    inpJ.removeAttribute('required');
    inpK.removeAttribute('required');

  } else {
    gU.style.display = 'none';
    gJ.style.display = 'block';
    gK.style.display = 'block';

    // 🔥 balikin required
    inpJ.setAttribute('required', true);
    inpK.setAttribute('required', true);
  }
});

if(tipe) tipe.addEventListener('change', function(){
  gDoc.style.display  = 'none';
  gVid.style.display  = 'none';
  gDesk.style.display = 'none';
  if(this.value==='pdf'||this.value==='ppt'){
    gDoc.style.display  = 'block';
    gDesk.style.display = 'block';
  }
  if(this.value==='video') gVid.style.display = 'block';
});
</script>
</body>
</html>