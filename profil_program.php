<?php
require_once 'config.php'; // PALING PERTAMA, tidak ada apapun sebelumnya
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Profil Program - Edu Lalin</title>

<link rel="icon" type="image/png" href="assets/img/logo-jr.png">
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="profil-page">
    
<?php include 'header_common.php'; ?>

<div class="site-bg">
  <div class="site-paper">
    <div class="container">

      <!-- VISI MISI -->
      <section class="card">
        <h2>Visi & Misi</h2>

        <div class="visi-misi-grid">
          <div>
            <h3 class="section-subtitle">Visi</h3>
            <p class="text-justify">
              Menjadi platform pembelajaran keselamatan berlalu lintas terdepan di Indonesia
              yang mampu membentuk generasi muda yang sadar, tertib, dan bertanggung jawab
              dalam berlalu lintas.
            </p>
          </div>

          <div>
            <h3 class="section-subtitle">Misi</h3>
            <ul class="misi-list">
              <li>Menyediakan materi pembelajaran yang interaktif dan mudah dipahami</li>
              <li>Meningkatkan kesadaran keselamatan berlalu lintas sejak dini</li>
              <li>Mendukung program pendidikan karakter di sekolah</li>
              <li>Mengintegrasikan teknologi dalam pembelajaran</li>
            </ul>
          </div>
        </div>
      </section>

      <!-- TENTANG -->
      <section class="card">
        <h2>Tentang Program</h2>

        <div class="tentang-text">
          <p>
            <strong>Edu Lalin</strong> adalah program pembelajaran keselamatan berlalu lintas yang dikembangkan
            untuk membekali siswa dengan pengetahuan dan kesadaran tentang pentingnya keselamatan di jalan raya.
            Program ini dirancang dengan pendekatan yang menyenangkan dan interaktif, disesuaikan dengan
            tingkat pemahaman setiap jenjang pendidikan.
          </p>

          <p>
            Melalui kombinasi materi multimedia, kuis interaktif, dan studi kasus nyata, siswa tidak hanya
            belajar teori tetapi juga dapat mengaplikasikan pengetahuan mereka dalam kehidupan sehari-hari.
          </p>
        </div>
      </section>

      <!-- FITUR -->
      <section class="card">
        <h2>Fitur Unggulan</h2>

        <div class="fitur-grid">

          <div class="fitur-card">
            <div class="fitur-icon">📚</div>
            <h3>Materi Lengkap</h3>
            <p>Modul pembelajaran tersusun sistematis sesuai jenjang pendidikan</p>
          </div>

          <div class="fitur-card">
            <div class="fitur-icon">🎯</div>
            <h3>Kuis Interaktif</h3>
            <p>Evaluasi pemahaman dengan kuis menarik di setiap level</p>
          </div>

          <div class="fitur-card">
            <div class="fitur-icon">📊</div>
            <h3>Tracking Progress</h3>
            <p>Pantau perkembangan belajar siswa secara real-time</p>
          </div>

          <div class="fitur-card">
            <div class="fitur-icon">🌐</div>
            <h3>100% Online</h3>
            <p>Akses kapan saja dan di mana saja dengan koneksi internet</p>
          </div>

          <div class="fitur-card">
            <div class="fitur-icon">🎓</div>
            <h3>Sertifikat Digital</h3>
            <p>Dapatkan sertifikat setelah menyelesaikan program</p>
          </div>

          <div class="fitur-card">
            <div class="fitur-icon">👥</div>
            <h3>Dukungan Penuh</h3>
            <p>Tim support siap membantu kendala pembelajaran</p>
          </div>

        </div>
      </section>

    </div>
  </div>
</div>

<?php include 'footer_common.php'; ?>

</body>
</html>