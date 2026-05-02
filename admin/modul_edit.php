<?php
require_once "../config.php";
require_admin();

if (!isset($_GET['id'])) {
    header("Location: modul.php");
    exit;
}

$id    = (int)$_GET['id'];
$flash = "";

$stmt = $conn->prepare("SELECT * FROM modul WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$modul = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$modul) {
    die("Modul tidak ditemukan.");
}

$kategori = $modul['kategori'] ?? 'sekolah';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_modul'])) {
    $judul   = trim($_POST['judul']     ?? '');
    $desk    = trim($_POST['deskripsi'] ?? '');

    if ($kategori === 'sekolah') {
        $jenjang = trim($_POST['jenjang'] ?? '');
        $kelas   = trim($_POST['kelas']   ?? '');
        $tipe    = trim($_POST['tipe']    ?? '');

        if ($judul === '' || $jenjang === '' || $kelas === '') {
            $flash = "Judul, jenjang, dan kelas wajib diisi.";
        } else {
            $stmt = $conn->prepare("
                UPDATE modul 
                SET judul = ?, jenjang = ?, kelas = ?, deskripsi = ?, tipe = ?
                WHERE id = ?
            ");
            $stmt->bind_param("sssssi", $judul, $jenjang, $kelas, $desk, $tipe, $id);
            $stmt->execute();
            $stmt->close();

            if (function_exists('log_activity')) {
                log_activity('modul', 'edit', 'Modul Sekolah diperbarui: ' . $judul . ' (ID: ' . $id . ')');
            }

            header("Location: modul.php");
            exit;
        }

    } else {
        // SIM
        $min_usia = (int)($_POST['min_usia'] ?? 0);

        if ($judul === '') {
            $flash = "Judul wajib diisi.";
        } else {
            $stmt = $conn->prepare("
                UPDATE modul 
                SET judul = ?, deskripsi = ?, min_usia = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssii", $judul, $desk, $min_usia, $id);
            $stmt->execute();
            $stmt->close();

            if (function_exists('log_activity')) {
                log_activity('modul', 'edit', 'Modul SIM diperbarui: ' . $judul . ' (ID: ' . $id . ')');
            }

            header("Location: modul.php");
            exit;
        }
    }
}

$judulVal   = $modul['judul']      ?? '';
$deskVal    = $modul['deskripsi']  ?? '';
$jenjangVal = $modul['jenjang']    ?? '';
$kelasVal   = $modul['kelas']      ?? '';
$tipeVal    = $modul['tipe']       ?? '';
$minUsiaVal = $modul['min_usia']   ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Edit Modul - Edu Lalin</title>
  <link rel="icon" type="image/png" href="../assets/img/logo-jr.png">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="adm-layout">
  <?php include 'sidebar.php'; ?>

  <div class="adm-main">

    <div class="adm-topbar">
      <div class="adm-topbar-title">Edit Modul</div>
      <div class="adm-topbar-right">
        <form method="post" action="../logout.php">
          <button class="adm-btn-out" type="submit">Logout</button>
        </form>
      </div>
    </div>

    <div class="adm-content">

      <div class="adm-ph">
        <div class="adm-ph-icon">
          <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
        </div>
        <div>
          <h1>Edit Modul <?= $kategori === 'sim' ? 'SIM' : 'Sekolah' ?></h1>
          <p>Perbarui informasi modul yang sudah diunggah</p>
        </div>
      </div>

      <?php if ($flash !== ''): ?>
        <div class="adm-alert err"><?= htmlspecialchars($flash) ?></div>
      <?php endif; ?>

      <div class="adm-form-card">
        <h3>📝 Informasi Modul</h3>
        <p>Edit detail modul <?= $kategori === 'sim' ? 'SIM' : 'Sekolah' ?></p>

        <form method="post">
          <div class="adm-form-grid">

            <!-- KOLOM KIRI -->
            <div class="adm-form-col">

              <div>
                <label class="adm-label">Judul Modul</label>
                <input type="text" name="judul" class="adm-input"
                       value="<?= htmlspecialchars($judulVal) ?>">
              </div>

              <div>
                <label class="adm-label">Deskripsi Singkat</label>
                <textarea name="deskripsi" class="adm-input"><?= htmlspecialchars($deskVal) ?></textarea>
                <span class="adm-field-hint">Jelaskan tujuan dan isi modul</span>
              </div>

              <?php if ($kategori === 'sekolah'): ?>

                <div>
                  <label class="adm-label">Jenjang Pendidikan</label>
                  <input type="text" name="jenjang" class="adm-input"
                         value="<?= htmlspecialchars($jenjangVal) ?>">
                  <span class="adm-field-hint">Contoh: SD, SMP, SMA</span>
                </div>

                <div>
                  <label class="adm-label">Kelas</label>
                  <input type="number" name="kelas" class="adm-input"
                         value="<?= htmlspecialchars($kelasVal) ?>">
                  <span class="adm-field-hint">Contoh: 1, 5, 8, 12</span>
                </div>

                <div>
                  <label class="adm-label">
                    Tipe Modul
                    <span class="adm-ro-badge">Readonly</span>
                  </label>
                  <input type="text" name="tipe" class="adm-input"
                         value="<?= htmlspecialchars($tipeVal) ?>" readonly>
                  <span class="adm-field-hint">Tipe tidak dapat diubah</span>
                </div>

              <?php else: ?>

                <div>
                  <label class="adm-label">Minimal Usia</label>
                  <input type="number" name="min_usia" class="adm-input"
                         value="<?= htmlspecialchars($minUsiaVal) ?>" min="0" max="99">
                  <span class="adm-field-hint">Usia minimal untuk mengakses modul ini</span>
                </div>

              <?php endif; ?>

            </div>

            <!-- KOLOM KANAN -->
            <div class="adm-form-col">

              <div class="adm-info-box">
                ℹ️ <strong>Catatan:</strong> File PDF / video tidak dapat diubah di halaman ini.
                Jika ingin mengganti file, hapus modul lalu upload ulang.
              </div>

              <div class="adm-card">
                <div class="adm-card-hd">
                  <h3>📄 File Saat Ini</h3>
                </div>
                <div class="adm-card-body">
                  <?php if (!empty($modul['file_path'])): ?>
                    <p style="font-size:13px;color:#6b7280;margin:0;">
                      <strong>Tipe:</strong> Dokumen (PDF/PPT)<br>
                      <strong>File:</strong>
                      <a href="../<?= htmlspecialchars($modul['file_path']) ?>"
                         target="_blank" style="color:#3b5bdb;font-weight:600;">
                        Lihat File
                      </a>
                    </p>
                  <?php elseif (!empty($modul['video_url'])): ?>
                    <p style="font-size:13px;color:#6b7280;margin:0;">
                      <strong>Tipe:</strong> Video<br>
                      <strong>Link:</strong>
                      <a href="<?= htmlspecialchars($modul['video_url']) ?>"
                         target="_blank" style="color:#3b5bdb;font-weight:600;">
                        Lihat Video
                      </a>
                    </p>
                  <?php else: ?>
                    <p style="font-size:13px;color:#9ca3af;margin:0;">Tidak ada file terlampir</p>
                  <?php endif; ?>
                </div>
              </div>

              <!-- BADGE KATEGORI -->
              <div class="adm-card">
                <div class="adm-card-hd">
                  <h3>🏷️ Kategori Modul</h3>
                </div>
                <div class="adm-card-body">
                  <?php if ($kategori === 'sim'): ?>
                    <span class="badge b-sim">🚗 Modul SIM</span>
                    <p style="font-size:12px;color:#6b7280;margin-top:8px;">
                      Modul ini untuk persiapan Surat Izin Mengemudi
                    </p>
                  <?php else: ?>
                    <span class="badge b-green">🏫 Modul Sekolah</span>
                    <p style="font-size:12px;color:#6b7280;margin-top:8px;">
                      Modul ini untuk pembelajaran di sekolah
                    </p>
                  <?php endif; ?>
                </div>
              </div>

              <button type="submit" name="save_modul" class="btn-save" style="width:100%;">
                💾 Simpan Perubahan
              </button>

              <a href="modul.php" class="btn-cancel" style="display:block;text-align:center;">
                ← Kembali ke Daftar Modul
              </a>

            </div>
          </div>
        </form>
      </div>

      <div class="adm-footer">© <?= date('Y') ?> Edu Lalin – Panel Admin</div>

    </div>
  </div>
</div>

</body>
</html>