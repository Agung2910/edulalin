<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kebijakan Privasi — Edu Lalin</title>
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
      padding: 48px 48px;
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
  <h1>Kebijakan Privasi</h1>
  <p>Terakhir diperbarui: 1 Januari 2026</p>
</div>

<div class="page-wrapper">
  <div class="doc-card">
    <div class="doc-section">
      <h2><span class="section-num">1</span> Pendahuluan</h2>
      <p>Kebijakan Privasi ini menjelaskan bagaimana <strong>Edu Lalin</strong> mengumpulkan, menggunakan, menyimpan, dan melindungi informasi pribadi pengguna yang menggunakan layanan pada website ini.</p>
      <p>Kami berkomitmen untuk menjaga keamanan dan kerahasiaan data pribadi pengguna serta memproses data tersebut sesuai dengan peraturan perlindungan data pribadi yang berlaku.</p>
      <p>Dengan menggunakan layanan pada platform ini, Anda menyetujui praktik pengumpulan dan penggunaan data sebagaimana dijelaskan dalam Kebijakan Privasi ini.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">2</span> Informasi yang Kami Kumpulkan</h2>
      <div class="info-block">
        <strong>Informasi Pribadi</strong>
        <p>Nama lengkap, alamat email, nomor telepon, informasi akun, dan data profil yang diberikan saat pendaftaran.</p>
      </div>
      <div class="info-block">
        <strong>Informasi Teknis</strong>
        <p>Alamat IP, jenis perangkat, sistem operasi, jenis browser, dan lokasi umum berdasarkan alamat IP.</p>
      </div>
      <div class="info-block">
        <strong>Informasi Aktivitas Pengguna</strong>
        <p>Halaman yang dikunjungi, kursus yang diakses, progres pembelajaran, waktu penggunaan, dan interaksi dengan fitur platform.</p>
      </div>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">3</span> Cara Kami Mengumpulkan Informasi</h2>
      <ul>
        <li><strong>Pendaftaran Akun</strong> — Saat pengguna membuat akun, kami meminta informasi dasar seperti nama dan alamat email.</li>
        <li><strong>Penggunaan Layanan</strong> — Kami mengumpulkan informasi ketika pengguna berinteraksi dengan fitur yang tersedia.</li>
        <li><strong>Cookies dan Teknologi Pelacakan</strong> — Website menggunakan cookies untuk memahami perilaku penggunaan dan meningkatkan layanan.</li>
        <li><strong>Komunikasi dengan Platform</strong> — Informasi juga dikumpulkan ketika pengguna menghubungi kami melalui email atau formulir kontak.</li>
      </ul>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">4</span> Tujuan Penggunaan Informasi</h2>
      <p>Informasi yang dikumpulkan digunakan untuk:</p>
      <ul>
        <li>Menyediakan layanan pembelajaran kepada pengguna</li>
        <li>Mengelola dan memelihara akun pengguna</li>
        <li>Meningkatkan kualitas dan kinerja platform</li>
        <li>Mengirimkan pemberitahuan atau informasi terkait layanan</li>
        <li>Menganalisis penggunaan platform untuk pengembangan layanan</li>
      </ul>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">5</span> Cookies dan Teknologi Pelacakan</h2>
      <p>Cookies adalah file kecil yang disimpan pada perangkat pengguna saat mengakses website. Kami menggunakan cookies untuk meningkatkan pengalaman pengguna, menyimpan preferensi, dan menganalisis perilaku penggunaan.</p>
      <p>Pengguna dapat menonaktifkan cookies melalui pengaturan browser, namun hal ini dapat memengaruhi fungsi tertentu pada website.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">6</span> Penyimpanan dan Keamanan Data</h2>
      <p>Kami mengambil langkah-langkah teknis yang wajar untuk melindungi data pengguna dari akses tidak sah, kehilangan, atau penyalahgunaan, mencakup enkripsi, pengamanan server, dan pembatasan akses data.</p>
      <p>Meskipun demikian, tidak ada metode transmisi data melalui internet yang sepenuhnya aman, sehingga kami tidak dapat menjamin keamanan data secara absolut.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">7</span> Pembagian Informasi kepada Pihak Ketiga</h2>
      <p>Kami tidak menjual atau memperdagangkan informasi pribadi pengguna. Data hanya dapat dibagikan kepada pihak ketiga yang membantu menjalankan layanan, seperti:</p>
      <ul>
        <li>Penyedia layanan hosting dan infrastruktur teknologi</li>
        <li>Penyedia layanan analitik</li>
        <li>Penyedia sistem pembayaran</li>
      </ul>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">8</span> Hak Pengguna atas Data Pribadi</h2>
      <p>Pengguna memiliki hak atas data pribadi mereka, antara lain:</p>
      <ul>
        <li>Hak untuk mengakses data pribadi yang kami simpan</li>
        <li>Hak untuk memperbaiki atau memperbarui informasi pribadi</li>
        <li>Hak untuk meminta penghapusan data pribadi</li>
        <li>Hak untuk membatasi pemrosesan data</li>
        <li>Hak untuk menarik persetujuan penggunaan data</li>
      </ul>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">9</span> Retensi Data</h2>
      <p>Kami menyimpan informasi pengguna selama diperlukan untuk menyediakan layanan atau selama akun masih aktif. Setelah data tidak lagi diperlukan, kami akan mengambil langkah yang wajar untuk menghapus atau mengamankannya.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">10</span> Privasi Anak</h2>
      <p>Platform ini tidak secara khusus ditujukan untuk anak di bawah umur tanpa pengawasan orang tua atau wali. Jika kami mengetahui data anak dikumpulkan tanpa persetujuan sah, kami akan segera menghapus data tersebut.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">11</span> Tautan ke Website Pihak Ketiga</h2>
      <p>Platform mungkin berisi tautan ke website pihak ketiga yang tidak dikelola oleh kami. Kami tidak bertanggung jawab atas praktik privasi pada website tersebut dan menyarankan pengguna membaca kebijakan privasi masing-masing website.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">12</span> Perubahan Kebijakan Privasi</h2>
      <p>Kami dapat memperbarui Kebijakan Privasi ini sewaktu-waktu. Setiap perubahan akan diumumkan melalui website dan berlaku sejak tanggal pembaruan yang tercantum.</p>
    </div>

    <div class="doc-section">
      <h2><span class="section-num">13</span> Informasi Kontak</h2>
      <p>Jika Anda memiliki pertanyaan terkait Kebijakan Privasi ini, hubungi kami melalui:</p>
      <div class="doc-contact">
        <p>📧 Email: <a href="mailto:privacy@edulalin.id">privacy@edulalin.id</a></p>
      </div>
    </div>

  </div>
</div>

<?php include 'footer_common.php'; ?>

</body>
</html>