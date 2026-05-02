<?php
require_once "config.php";
require_login();

// ⬇️ Ambil bypass langsung dari DB, jangan andalkan session
$uid  = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("SELECT jenjang, kelas, bypass_access FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $uid);
$stmt->execute();
$dbUser = $stmt->get_result()->fetch_assoc();
$stmt->close();

$_SESSION['jenjang'] = strtoupper($dbUser['jenjang'] ?? '');
$_SESSION['kelas']   = (int)($dbUser['kelas'] ?? 0);

$bypass    = (int)($dbUser['bypass_access'] ?? 0);
$kelasUser = (int)($dbUser['kelas'] ?? 0);
  $jenjangUser = strtolower($dbUser['jenjang'] ?? '');

if (!$bypass && !$jenjangUser) {
    header("Location: blocked.php");
    exit;
}

// ⬇️ Jenjang dari URL (untuk bypass user bisa pilih bebas)
$jenjangParam = isset($_GET['jenjang']) ? strtolower(trim($_GET['jenjang'])) : '';
$namaJenjang  = ['sd' => 'SD', 'smp' => 'SMP', 'sma' => 'SMA'];

// Kalau bypass: pakai jenjang dari URL kalau valid, fallback ke jenjang sendiri
// Kalau tidak bypass: paksa pakai jenjang dari DB
if ($bypass) {
    $jenjang = isset($namaJenjang[$jenjangParam]) ? $jenjangParam : $jenjangUser;
} else {
    $jenjang = $jenjangUser;
}

// ⬇️ Kelas dari URL
$kelas = null;
if (isset($_GET['kelas']) && is_numeric($_GET['kelas'])) {
    $kelas = (int)$_GET['kelas'];
    // Kalau bukan bypass, kelas harus sesuai kelas sendiri
    if (!$bypass && $kelas !== $kelasUser) {
        header("Location: blocked.php");
        exit;
    }
}

// ⬇️ Kelasdaftar per jenjang
$kelasList = [];
if ($jenjang === 'sd')       $kelasList = range(1, 6);
elseif ($jenjang === 'smp')  $kelasList = range(7, 9);
elseif ($jenjang === 'sma')  $kelasList = range(10, 12);

// ⬇️ Cek modul tersedia
$hasModul     = false;
$firstModulId = null;
if ($jenjang && $kelas) {
    $stmt = $conn->prepare("
        SELECT id FROM modul
        WHERE LOWER(jenjang) = ? AND kelas = ? AND is_active = 1
        LIMIT 1
    ");
    $stmt->bind_param("si", $jenjang, $kelas);
    $stmt->execute();
    if ($row = $stmt->get_result()->fetch_assoc()) {
        $hasModul     = true;
        $firstModulId = $row['id'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modul - Edu Lalin</title>
    <link rel="icon" type="image/png" href="assets/img/logo-jr.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include 'header_common.php'; ?>

<div class="site-bg">
  <div class="site-paper">
    <div class="container">
    <?php if (!$jenjang || !isset($namaJenjang[$jenjang])): ?>
      <section class="card jenjang-hero">
        <div class="jenjang-hero-left">
          <h2>Pilih Jenjang</h2>
          <p>
            Pilih jenjang sekolah terlebih dahulu untuk melihat kelas, materi, video, dan quiz
            yang sesuai dengan tingkat belajar siswa.
          </p>

          <div class="jenjang-badges">
            <span class="badge-soft">3 jenjang tersedia</span>
            <span class="badge-soft">Modul & quiz interaktif</span>
          </div>
        </div>

        <div class="jenjang-hero-right">
          <img src="assets/img/ilustrasi-jenjang.png" alt="Ilustrasi siswa belajar">
        </div>
      </section>

      <section class="jenjang-grid-section">
        <div class="jenjang-grid">
          <a href="modul.php?jenjang=sd" class="card jenjang-card">
            <div class="jenjang-icon">📘</div>
            <h3>SD / MI</h3>
            <p>Materi pengenalan rambu, cara menyeberang, dan sikap aman di jalan.</p>
          </a>

          <a href="modul.php?jenjang=smp" class="card jenjang-card">
            <div class="jenjang-icon">📗</div>
            <h3>SMP / MTs</h3>
            <p>Pendalaman aturan lalu lintas, etika berkendara, dan risiko di jalan.</p>
          </a>

          <a href="modul.php?jenjang=sma" class="card jenjang-card">
            <div class="jenjang-icon">📙</div>
            <h3>SMA / SMK / MA</h3>
            <p>Studi kasus nyata, proyek kecil, dan quiz skenario berkendara.</p>
          </a>
        </div>
      </section>

    <?php elseif ($jenjang && !$kelas): ?>
      <div class="card">
        <h2 class="kelas-title">
          <span class="kelas-icon"><?php echo strtoupper($jenjang); ?></span>
          <span>Pilih Kelas - <?php echo $namaJenjang[$jenjang]; ?></span>
        </h2>

        <div class="kelas-grid">
          <?php foreach ($kelasList as $k): ?>
          <?php
          $stmt = $conn->prepare("
              SELECT id FROM modul 
              WHERE LOWER(jenjang) = LOWER(?) AND kelas = ? AND is_active = 1
              LIMIT 1
          ");
          $stmt->bind_param("si", $jenjang, $k);
          $stmt->execute();
          $res = $stmt->get_result();
          $row = $res->fetch_assoc();
          $modulId = $row['id'] ?? 0;
          $stmt->close();
          ?>
          <a href="materi.php?modul_id=<?php echo $modulId; ?>"
            class="kelas-card-big">
            <div class="kelas-card-number"><?php echo $k; ?></div>
            <div class="kelas-card-info">
              <h3>Kelas <?php echo $k; ?></h3>
              <p>Masuk ke materi kelas ini.</p>
            </div>
            <div class="kelas-card-arrow">→</div>
          </a>
          <?php endforeach; ?>
        </div>

        <div class="alert" style="margin-top:18px;">
          💡 Tip: Pilih kelas sesuai tingkat belajar Anda untuk mengakses materi dan quiz yang relevan.
        </div>
      </div>

    <?php else: ?>
      <div class="card">
        <div class="card-header-flex" style="margin-bottom:12px;">
          <div>
            <h2>
              Modul Kelas <?php echo $kelas; ?> - <?php echo $namaJenjang[$jenjang]; ?>
            </h2>
          </div>
          <span class="badge-soft badge-green">
            <?php echo strtoupper($jenjang); ?> · Kelas <?php echo $kelas; ?>
          </span>
        </div>

        <p class="card-subtitle">
          Pilih materi pembelajaran atau kerjakan quiz untuk melanjutkan proses belajar.
        </p>

        <?php if (!$hasModul): ?>
          <div class="empty-modul-box" style="text-align:center; padding:40px 20px; background:#f8fafb; border-radius:12px; margin-top:20px;">
            <div style="font-size:48px; margin-bottom:12px;">📚</div>
            <p style="font-size:18px; font-weight:700; margin-bottom:8px; color:#0f172a;">
              Belum ada modul untuk kelas ini
            </p>
            <p style="font-size:14px; color:#64748b; margin-bottom:0;">
              Admin dapat menambahkan modul baru dari menu Kelola Modul.
            </p>
          </div>
        <?php else: ?>
          <div class="modul-grid" style="margin-top:20px;">
            <div class="modul-card">
              <div style="display:flex; gap:16px; flex-wrap:wrap;">
                <a href="materi.php?modul_id=<?php echo $firstModulId; ?>" class="btnx btn-primaryx">
                  📚 Lihat Semua Materi
                </a>
                <a href="quiz_list.php?modul_id=<?php echo $firstModulId; ?>" class="btnx btn-successx">
                  📝 Kerjakan Quiz
                </a>
              </div>
            </div>
          </div>

          <div class="alert" style="margin-top:16px;">
            💡 Tips: Pelajari materi terlebih dahulu sebelum mengerjakan quiz untuk hasil yang maksimal.
          </div>
        <?php endif; ?>
      </div>

<?php endif; ?>

    </div>
  </div>
</div>

<?php include 'footer_common.php'; ?>
</body>
</html>