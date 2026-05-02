<?php
require_once "../config.php";
require_admin();
$flash='';$flash_type='ok';

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['add_quiz'])){
  $jenjang=trim($_POST['jenjang']??'');$kelas=(int)($_POST['kelas']??0);$pertanyaan=trim($_POST['pertanyaan']??'');
  $a=trim($_POST['opsi_a']??'');$b=trim($_POST['opsi_b']??'');$c=trim($_POST['opsi_c']??'');$d=trim($_POST['opsi_d']??'');
  $jaw=strtoupper(trim($_POST['jawaban_benar']??''));$mid=(int)($_POST['materi_id']??0);
  if(in_array($jenjang,['sd','smp','sma','sim'])&&$kelas>0&&$mid>0&&$pertanyaan!==''&&$a!==''&&$b!==''&&$c!==''&&$d!==''&&in_array($jaw,['A','B','C','D'])){
    $stmt=$conn->prepare("INSERT INTO quiz(materi_id,jenjang,kelas,pertanyaan,opsi_a,opsi_b,opsi_c,opsi_d,jawaban_benar)VALUES(?,?,?,?,?,?,?,?,?)");
    if($stmt){$stmt->bind_param("iissssss",$mid,$jenjang,$kelas,$pertanyaan,$a,$b,$c,$d,$jaw);
      if($stmt->execute())$flash='Soal berhasil ditambahkan!';else{$flash='Gagal: '.$stmt->error;$flash_type='err';}$stmt->close();}
  }else{$flash='Lengkapi semua field dengan benar.';$flash_type='err';}
}

if(isset($_GET['del'])){
  $id=(int)$_GET['del'];
  $conn->query("DELETE FROM quiz WHERE id=$id");
  $flash='Soal berhasil dihapus!';
}

// Ambil semua soal, group by jenjang+kelas
$fj=$_GET['jenjang']??'';
$sql="SELECT q.* FROM quiz q WHERE 1=1".($fj?" AND q.jenjang='$fj'":"")." ORDER BY q.jenjang ASC, q.kelas ASC, q.id ASC";
$qRes=$conn->query($sql);

// Group soal by "jenjang|kelas"
$grouped = [];
if($qRes){
  while($row=$qRes->fetch_assoc()){
    $key = strtoupper($row['jenjang']).'|'.$row['kelas'];
    $grouped[$key][] = $row;
  }
}

$total_soal = array_sum(array_map('count', $grouped));
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Kelola Soal Quiz - Edu Lalin</title>
<link rel="icon" type="image/png" href="../assets/img/logo-jr.png">
<link rel="stylesheet" href="../assets/css/admin.css">
<style>
/* ── ACCORDION ── */
.acc-group {
  border: 1px solid var(--border);
  border-radius: 10px;
  overflow: hidden;
  margin-bottom: 10px;
  background: var(--white);
}

.acc-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
  cursor: pointer;
  user-select: none;
  transition: background 0.15s;
  gap: 12px;
}

.acc-header:hover { background: #f8f9fc; }
.acc-header.open  { background: #f3f6ff; border-bottom: 1px solid var(--border); }

.acc-header-left  { display: flex; align-items: center; gap: 12px; }
.acc-header-right { display: flex; align-items: center; gap: 10px; }

.acc-title {
  font-size: 14px; font-weight: 700; color: var(--text);
}

.acc-count {
  font-size: 12px; color: var(--muted); font-weight: 500;
}

.acc-chevron {
  width: 20px; height: 20px;
  display: flex; align-items: center; justify-content: center;
  color: var(--muted);
  transition: transform 0.2s ease;
  flex-shrink: 0;
}

.acc-header.open .acc-chevron { transform: rotate(180deg); }

.acc-body {
  display: none;
  overflow: hidden;
}

.acc-body.open { display: block; }

/* soal number badge */
.soal-no {
  display: inline-flex;
  align-items: center; justify-content: center;
  width: 26px; height: 26px;
  border-radius: 50%;
  background: var(--accent-light);
  color: var(--accent);
  font-size: 12px; font-weight: 700;
  flex-shrink: 0;
}

.soal-row {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 14px 18px;
  border-bottom: 1px solid #f0f1f5;
  transition: background 0.12s;
}

.soal-row:last-child { border-bottom: none; }
.soal-row:hover { background: #fafbff; }

.soal-pertanyaan {
  flex: 1;
  font-size: 13.5px;
  color: var(--text);
  line-height: 1.5;
}

.soal-opsi-wrap {
  display: flex;
  gap: 6px;
  margin-top: 8px;
  flex-wrap: wrap;
}

.soal-opsi {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  background: #f3f4f8;
  color: var(--muted);
  border: 1px solid var(--border);
}

.soal-opsi.correct {
  background: #dcfce7;
  color: #15803d;
  border-color: #86efac;
  font-weight: 600;
}

.soal-opsi-letter {
  font-weight: 700;
  font-size: 11px;
}

.soal-actions { flex-shrink: 0; display: flex; align-items: flex-start; padding-top: 2px; }

/* jenjang color dot */
.acc-dot {
  width: 10px; height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}
.dot-sd  { background: #c68c00; }
.dot-smp { background: #0d9a6a; }
.dot-sma { background: #3b5bdb; }
.dot-sim { background: #c2185b; }

/* filter tab pills */
.filter-pills {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}

.filter-pill {
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  border: 1.5px solid var(--border);
  background: var(--white);
  color: var(--muted);
  transition: all 0.15s;
}

.filter-pill:hover { border-color: var(--accent); color: var(--accent); }
.filter-pill.active { background: var(--accent); color: #fff; border-color: var(--accent); }

.empty-group {
  padding: 48px;
  text-align: center;
  color: var(--muted);
  font-size: 14px;
}

.empty-group svg { margin-bottom: 12px; opacity: 0.35; }
</style>
</head>
<body>
<div class="adm-layout">
<?php include 'sidebar.php'; ?>
<div class="adm-main">

  <div class="adm-topbar">
    <div class="adm-topbar-title">Kelola Soal Quiz</div>
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
      <div class="adm-ph-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
      <div><h1>Kelola Soal Quiz</h1><p>Buat dan kelola bank soal pilihan ganda — dikelompokkan per kelas</p></div>
    </div>

    <?php if($flash):?>
    <div class="adm-alert <?=$flash_type?>"><?=$flash_type==='ok'?'✅':'❌'?> <?=htmlspecialchars($flash)?></div>
    <?php endif;?>

    <!-- FORM TAMBAH SOAL -->
    <div class="adm-form-card">
      <h3>
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        Tambah Soal Baru
      </h3>
      <p>Soal pilihan ganda berdasarkan Jenjang dan Kelas</p>
      <form method="post" class="adm-form-grid">
        <div class="adm-form-col">
          <div>
            <label class="adm-label">Jenjang</label>
            <select name="jenjang" class="adm-input" required id="jenjangSel">
              <option value="">-- Pilih Jenjang --</option>
              <option value="sd">SD</option><option value="smp">SMP</option>
              <option value="sma">SMA</option><option value="sim">SIM</option>
            </select>
          </div>
          <div>
            <label class="adm-label">Tingkat Kelas</label>
            <select name="kelas" class="adm-input" required id="kelasSel"><option value="">-- Pilih jenjang dulu --</option></select>
            <span class="adm-field-hint">Muncul setelah pilih jenjang</span>
          </div>
          <div>
            <label class="adm-label">Judul Modul</label>
            <select name="materi_id" class="adm-input" required id="modulSel"><option value="">-- Pilih Modul --</option></select>
            <span class="adm-field-hint">Muncul sesuai jenjang &amp; kelas</span>
          </div>
          <div>
            <label class="adm-label">Pertanyaan</label>
            <textarea name="pertanyaan" rows="5" class="adm-input" placeholder="Tulis pertanyaan..." required></textarea>
          </div>
        </div>
        <div class="adm-form-col">
          <div><label class="adm-label">Opsi A</label><input type="text" name="opsi_a" class="adm-input" placeholder="Jawaban A" required></div>
          <div><label class="adm-label">Opsi B</label><input type="text" name="opsi_b" class="adm-input" placeholder="Jawaban B" required></div>
          <div><label class="adm-label">Opsi C</label><input type="text" name="opsi_c" class="adm-input" placeholder="Jawaban C" required></div>
          <div><label class="adm-label">Opsi D</label><input type="text" name="opsi_d" class="adm-input" placeholder="Jawaban D" required></div>
          <div>
            <label class="adm-label">Jawaban Benar</label>
            <select name="jawaban_benar" class="adm-input" required>
              <option value="">-- Pilih --</option>
              <option value="A">A</option><option value="B">B</option>
              <option value="C">C</option><option value="D">D</option>
            </select>
          </div>
          <button type="submit" name="add_quiz" class="btn-save">💾 Simpan Soal</button>
        </div>
      </form>
    </div>

    <!-- DAFTAR SOAL ACCORDION -->
    <div class="adm-card">
      <div class="adm-card-hd">
        <h3>
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#3b5bdb" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
          Daftar Soal Quiz
        </h3>
        <span class="badge b-user"><?= $total_soal ?> soal · <?= count($grouped) ?> kelompok</span>
      </div>

      <!-- Filter pills -->
      <div style="padding:14px 18px;border-bottom:1px solid var(--border);">
        <div class="filter-pills">
          <a href="quiz.php" class="filter-pill <?= $fj===''?'active':'' ?>">Semua</a>
          <a href="quiz.php?jenjang=sd"  class="filter-pill <?= $fj==='sd'?'active':'' ?>">SD</a>
          <a href="quiz.php?jenjang=smp" class="filter-pill <?= $fj==='smp'?'active':'' ?>">SMP</a>
          <a href="quiz.php?jenjang=sma" class="filter-pill <?= $fj==='sma'?'active':'' ?>">SMA</a>
          <a href="quiz.php?jenjang=sim" class="filter-pill <?= $fj==='sim'?'active':'' ?>">SIM</a>
        </div>
      </div>

      <div style="padding:16px 18px;">
        <?php if(!empty($grouped)): ?>
          <?php
          $dot_map = ['SD'=>'dot-sd','SMP'=>'dot-smp','SMA'=>'dot-sma','SIM'=>'dot-sim'];
          $badge_map = ['SD'=>'b-sd','SMP'=>'b-smp','SMA'=>'b-sma','SIM'=>'b-sim'];
          $i = 0;
          foreach($grouped as $key => $soals):
            [$jenjang_up, $kelas_num] = explode('|', $key);
            $dot   = $dot_map[$jenjang_up] ?? 'dot-sma';
            $badge = $badge_map[$jenjang_up] ?? '';
            $label = $jenjang_up === 'SIM'
              ? 'SIM (Program SIM)'
              : $jenjang_up.' Kelas '.$kelas_num;
            $jml   = count($soals);
            $i++;
          ?>
          <div class="acc-group">
            <div class="acc-header <?= $i===1?'open':'' ?>" onclick="toggleAcc(this)">
              <div class="acc-header-left">
                <div class="acc-dot <?= $dot ?>"></div>
                <span class="acc-title"><?= $label ?></span>
                <span class="acc-count"><?= $jml ?> soal</span>
              </div>
              <div class="acc-header-right">
                <span class="badge <?= $badge ?>"><?= $jenjang_up ?></span>
                <div class="acc-chevron">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>

            <div class="acc-body <?= $i===1?'open':'' ?>">
              <?php foreach($soals as $no => $q): ?>
              <div class="soal-row">
                <div class="soal-no"><?= $no+1 ?></div>
                <div class="soal-pertanyaan">
                  <div style="margin-bottom:8px"><?= nl2br(htmlspecialchars($q['pertanyaan'])) ?></div>
                  <div class="soal-opsi-wrap">
                    <?php foreach(['A','B','C','D'] as $opt):
                      $opsi_key = 'opsi_'.strtolower($opt);
                      $is_correct = $q['jawaban_benar'] === $opt;
                    ?>
                    <div class="soal-opsi <?= $is_correct?'correct':'' ?>">
                      <span class="soal-opsi-letter"><?= $opt ?>.</span>
                      <?= htmlspecialchars($q[$opsi_key]) ?>
                      <?php if($is_correct): ?>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                      <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <div class="soal-actions">
                  <a href="quiz.php?del=<?= $q['id'] ?>&jenjang=<?= $fj ?>"
                     class="btn-del"
                     onclick="return confirm('Hapus soal ini?')">Hapus</a>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endforeach; ?>

        <?php else: ?>
          <div class="empty-group">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div>Belum ada soal<?= $fj ? ' untuk jenjang '.strtoupper($fj) : '' ?>.</div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="adm-footer">&copy; <?= date('Y') ?> Edu Lalin &ndash; Panel Admin</div>
  </div>
</div>
</div>

<script>
function toggleAcc(header) {
  var body = header.nextElementSibling;
  var isOpen = header.classList.contains('open');
  header.classList.toggle('open', !isOpen);
  body.classList.toggle('open', !isOpen);
}

var jSel=document.getElementById('jenjangSel'),kSel=document.getElementById('kelasSel'),mSel=document.getElementById('modulSel');
jSel.addEventListener('change',function(){
  var j=this.value;
  kSel.innerHTML='<option value="">-- Pilih Kelas --</option>';
  mSel.innerHTML='<option value="">-- Pilih Modul --</option>';
  var kls=[];
  if(j==='sd')kls=[1,2,3,4,5,6];
  else if(j==='smp')kls=[7,8,9];
  else if(j==='sma')kls=[10,11,12];
  else if(j==='sim'){
    kSel.innerHTML='<option value="1" selected>-</option>';
    fetch('get_modul.php?jenjang=sim&kelas=0').then(r=>r.json()).then(d=>{
      mSel.innerHTML='<option value="">-- Pilih Modul SIM --</option>';
      d.forEach(m=>{var o=document.createElement('option');o.value=m.id;o.textContent=m.judul;mSel.appendChild(o);});
    });
    return;
  }
  kls.forEach(k=>{var o=document.createElement('option');o.value=k;o.textContent='Kelas '+k;kSel.appendChild(o);});
});
kSel.addEventListener('change',function(){
  var j=jSel.value,k=this.value;
  mSel.innerHTML='<option value="">Loading...</option>';
  if(!j||!k){mSel.innerHTML='<option value="">-- Pilih Modul --</option>';return;}
  fetch('get_modul.php?jenjang='+j+'&kelas='+k)
    .then(r=>r.json())
    .then(d=>{
      mSel.innerHTML='<option value="">-- Pilih Modul --</option>';
      if(!d.length){mSel.innerHTML='<option value="">Tidak ada modul</option>';return;}
      d.forEach(m=>{var o=document.createElement('option');o.value=m.id;o.textContent=m.judul;mSel.appendChild(o);});
    }).catch(()=>{mSel.innerHTML='<option value="">Error</option>';});
});
</script>
</body>
</html>