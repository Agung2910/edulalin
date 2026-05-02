<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Syarat & Ketentuan — Edu Lalin</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .page-hero {
      background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
      color: white;
      text-align: center;
      padding: 60px 40px;
    }
    .page-hero h1 {
      font-size: 36px;
      font-weight: 700;
      margin-bottom: 12px;
      color: white;
    }
    .page-hero p {
      font-size: 15px;
      opacity: 0.8;
      margin: 0;
    }

    .breadcrumb {
      background: white;
      border-bottom: 1px solid #e5e7eb;
      padding: 12px 40px;
    }
    .breadcrumb-inner {
      max-width: 860px;
      margin: auto;
      font-size: 13px;
      color: #6b7280;
    }
    .breadcrumb-inner a { color: #2563eb; text-decoration: none; }
    .breadcrumb-inner a:hover { text-decoration: underline; }
    .breadcrumb-inner span { margin: 0 6px; }

    .page-wrapper {
      max-width: 860px;
      margin: 48px auto;
      padding: 0 24px 64px;
    }

    .doc-card {
      background: white;
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      padding: 56px 64px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }

    .doc-meta {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: #6b7280;
      margin-bottom: 40px;
      padding-bottom: 24px;
      border-bottom: 1px solid #f3f4f6;
    }
    .doc-meta .doc-badge {
      background: #eff6ff;
      color: #2563eb;
      font-size: 12px;
      font-weight: 600;
      padding: 3px 10px;
      border-radius: 20px;
    }

    .doc-section { margin-bottom: 40px; }
    .doc-section h2 {
      font-size: 18px;
      font-weight: 700;
      color: #111827;
      margin-bottom: 14px;
      padding-bottom: 8px;
      border-bottom: 2px solid #eff6ff;
    }
    .section-num {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #2563eb;
      color: white;
      font-size: 12px;
      font-weight: 700;
      width: 24px;
      height: 24px;
      border-radius: 6px;
      margin-right: 10px;
    }
    .doc-section p {
      font-size: 15px;
      line-height: 1.75;
      color: #374151;
      margin-bottom: 12px;
    }
    .doc-section ul { list-style: none; padding: 0; margin: 12px 0; }
    .doc-section ul li {
      font-size: 15px;
      line-height: 1.75;
      color: #374151;
      padding: 6px 0 6px 24px;
      position: relative;
      margin-bottom: 0;
    }
    .doc-section ul li::before {
      content: '';
      position: absolute;
      left: 8px;
      top: 16px;
      width: 6px;
      height: 6px;
      background: #2563eb;
      border-radius: 50%;
    }

    .term-block {
      background: #f8fafc;
      border-left: 3px solid #2563eb;
      border-radius: 0 8px 8px 0;
      padding: 14px 18px;
      margin-bottom: 12px;
    }
    .term-block strong {
      display: block;
      font-size: 14px;
      color: #1d4ed8;
      margin-bottom: 4px;
    }
    .term-block p { margin: 0; font-size: 14px; color: #4b5563; }

    .doc-contact {
      background: #eff6ff;
      border: 1px solid #bfdbfe;
      border-radius: 10px;
      padding: 20px 24px;
      margin-top: 8px;
    }
    .doc-contact p { margin: 0; font-size: 14px; color: #1e40af; }
    .doc-contact a { color: #2563eb; font-weight: 600; }

    @media (max-width: 768px) {
      .doc-card { padding: 32px 24px; }
      .page-hero h1 { font-size: 26px; }
      .breadcrumb { padding: 12px 20px; }
    }
  </style>
</head>
<body>

<?php include 'header_common.php'; ?>

<div class="page-hero">
  <h1>Syarat &amp; Ketentuan</h1>
  <p>Terakhir diperbarui: 1 Januari 2026</p>
</div>

<div class="page-wrapper">
  <div class="doc-card">
    <div class="doc-section">
      <h2><span class="section-num">1</span> Pendahuluan</h2>
      <p>Selamat datang di <strong>Edu Lalin</strong>, sebuah platform pembelajaran online yang menyediakan berbagai layanan pendidikan seperti kursus online, materi pembelajaran digital, artikel edukasi, video pembelajaran, dan fitur lain yang mendukung proses belajar pengguna.</p>
      <p>Syarat dan Ketentuan ini mengatur penggunaan layanan yang tersedia pada website Edu Lalin. Dengan mengakses atau menggunakan layanan yang disediakan oleh platform ini, Anda dianggap telah membaca, memahami, dan menyetujui seluruh isi Syarat &amp; Ketentuan yang berlaku.</p>
      <p>Jika Anda tidak setuju dengan sebagian atau seluruh ketentuan ini, maka Anda disarankan untuk tidak menggunakan layanan yang tersedia pada platform ini.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">2</span> Definisi</h2>
      <div class="term-block">
        <strong>Platform / Website</strong>
        <p>Merujuk pada situs web Edu Lalin beserta seluruh layanan, fitur, konten, dan teknologi yang disediakan di dalamnya.</p>
      </div>
      <div class="term-block">
        <strong>Pengguna</strong>
        <p>Setiap individu yang mengakses, menggunakan, atau berinteraksi dengan layanan yang tersedia pada platform ini, baik sebagai pengunjung maupun pengguna terdaftar.</p>
      </div>
      <div class="term-block">
        <strong>Akun</strong>
        <p>Identitas pengguna yang dibuat melalui proses pendaftaran pada platform untuk mengakses fitur tertentu.</p>
      </div>
      <div class="term-block">
        <strong>Konten</strong>
        <p>Seluruh materi yang tersedia di dalam platform, termasuk teks, video pembelajaran, modul, gambar, audio, desain grafis, serta materi edukasi lainnya.</p>
      </div>
      <div class="term-block">
        <strong>Layanan</strong>
        <p>Seluruh fitur, fasilitas, dan sistem yang disediakan oleh platform untuk mendukung kegiatan pembelajaran pengguna.</p>
      </div>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">3</span> Persyaratan Pengguna</h2>
      <p>Untuk menggunakan layanan pada platform ini, pengguna diwajibkan memenuhi beberapa persyaratan:</p>
      <ul>
        <li>Memberikan informasi yang benar, akurat, dan terbaru saat melakukan pendaftaran akun.</li>
        <li>Bertanggung jawab penuh atas keamanan akun yang dimiliki, termasuk menjaga kerahasiaan kata sandi.</li>
        <li>Tidak menggunakan identitas palsu, menyamar sebagai pihak lain, atau menggunakan akun milik orang lain tanpa izin.</li>
      </ul>
      <p>Apabila ditemukan adanya penyalahgunaan akun, pihak platform berhak mengambil tindakan termasuk membatasi atau menonaktifkan akun pengguna.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">4</span> Pendaftaran Akun</h2>
      <p>Untuk mengakses beberapa fitur tertentu, pengguna diwajibkan untuk membuat akun. Dalam proses pendaftaran, pengguna harus memberikan informasi yang valid seperti nama dan alamat email.</p>
      <p>Platform tidak bertanggung jawab atas kerugian yang timbul akibat kelalaian pengguna dalam menjaga keamanan akun. Platform berhak menangguhkan atau menghapus akun yang memberikan informasi palsu.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">5</span> Penggunaan Layanan</h2>
      <p>Pengguna diharapkan menggunakan layanan secara bertanggung jawab dan sesuai dengan hukum yang berlaku. Pengguna tidak diperbolehkan:</p>
      <ul>
        <li>Menggunakan layanan untuk aktivitas yang melanggar hukum, termasuk penyebaran konten ilegal atau penipuan.</li>
        <li>Melakukan tindakan yang mengganggu stabilitas atau keamanan sistem platform, seperti peretasan atau menyebarkan virus.</li>
        <li>Melakukan aktivitas yang merugikan pengguna lain.</li>
      </ul>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">6</span> Hak Kekayaan Intelektual</h2>
      <p>Seluruh konten yang tersedia pada platform ini merupakan hak kekayaan intelektual milik platform dan dilindungi oleh undang-undang hak cipta yang berlaku.</p>
      <p>Pengguna tidak diperbolehkan menyalin, mendistribusikan, mempublikasikan ulang, menjual, atau memodifikasi konten tanpa izin tertulis dari pemilik hak cipta.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">7</span> Lisensi Penggunaan Konten</h2>
      <p>Platform memberikan lisensi terbatas, non-eksklusif, dan tidak dapat dipindahtangankan kepada pengguna untuk mengakses dan menggunakan konten yang tersedia hanya untuk keperluan pembelajaran pribadi.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">8</span> Pembayaran dan Layanan Berbayar</h2>
      <p>Beberapa layanan mungkin memerlukan pembayaran sebelum dapat diakses. Informasi mengenai biaya dan metode pembayaran akan ditampilkan secara jelas sebelum transaksi dilakukan.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">9</span> Pembatasan Tanggung Jawab</h2>
      <p>Platform tidak dapat menjamin bahwa layanan akan selalu tersedia tanpa gangguan atau kesalahan teknis. Gangguan dapat terjadi karena pemeliharaan sistem, gangguan jaringan, atau kondisi di luar kendali platform.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">10</span> Penangguhan atau Penghapusan Akun</h2>
      <p>Platform berhak menangguhkan, membatasi, atau menghapus akun pengguna jika ditemukan pelanggaran terhadap Syarat &amp; Ketentuan yang berlaku, termasuk tanpa pemberitahuan sebelumnya apabila pelanggaran dianggap serius.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">11</span> Perubahan Syarat &amp; Ketentuan</h2>
      <p>Platform dapat memperbarui Syarat &amp; Ketentuan ini sewaktu-waktu. Perubahan akan diumumkan melalui website dan mulai berlaku sejak tanggal yang ditetapkan.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">12</span> Hukum yang Berlaku</h2>
      <p>Syarat &amp; Ketentuan ini diatur sesuai dengan hukum yang berlaku di Republik Indonesia. Setiap sengketa akan diselesaikan sesuai dengan mekanisme hukum yang berlaku.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">13</span> Informasi Kontak</h2>
      <p>Jika Anda memiliki pertanyaan terkait Syarat &amp; Ketentuan ini, hubungi kami melalui:</p>
      <div class="doc-contact">
        <p>📧 Email: <a href="mailto:support@edulalin.id">support@edulalin.id</a></p>
      </div>
    </div>

  </div>
</div>

<?php include 'footer_common.php'; ?>

</body>
</html>