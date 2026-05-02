<?php
require_once "config.php";

$showBirthday = false;
$birthdayName = '';

if (isset($_SESSION['user_id'])) {

    $user_id = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT nama, tanggal_lahir FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?? [];; 
    $stmt->close();

    $nama = $row['nama'] ?? '';
    $tanggal_lahir = $row['tanggal_lahir'] ?? '';

    if ($tanggal_lahir) {
      $today = new DateTime('today');
      $birth = new DateTime($tanggal_lahir);
      $birthThisYear = new DateTime(
          $today->format('Y') . '-' .
          $birth->format('m') . '-' .
          $birth->format('d')
      );

      $interval = $today->diff($birthThisYear);
      $diff = (int)$interval->format('%r%a');

      if ($diff >= -7 && $diff <= 0) {
      $stmt2 = $conn->prepare("
          SELECT id FROM birthday_popup_log 
          WHERE user_id = ? AND tanggal = CURDATE()
      ");
      $stmt2->bind_param("i", $user_id);
      $stmt2->execute();
      $alreadyClosed = $stmt2->get_result()->num_rows > 0;
      $stmt2->close();

      if (!$alreadyClosed) {
          $showBirthday = true;
          $birthdayName = $nama;
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Beranda - Edu Lalin</title>
  <link rel="icon" type="image/png" href="assets/img/logo.png">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/chatbot.css">
</head>
<body>

<?php include 'header_common.php'; ?>

<section class="hero-bg">
  <div class="hero-inner">
    <div class="hero-content">
      <div class="hero-text">
        <p class="hero-tagline">Platform Belajar Keselamatan</p>
        <h1>Belajar Lalu Lintas & Keselamatan Secara Interaktif</h1>
      </div>
    </div>
    
    <div class="hero-card-grid">
      <div class="feature-card" style="background-image: url('assets/img/.png');">
        <h3>Materi Pembelajaran</h3>
        <p></p>
        <a href="modul.php" class="btn btn-primary">Mulai Materi Sekarang</a>
      </div>

      <div class="feature-card" style="background-image: url('assets/img/.png');">
        <h3>Kuis Interaktif</h3>
        <p></p>
        <a href="quiz_list.php" class="btn btn-primary">Coba Kuis Sekarang</a>
      </div>    

      <div class="feature-card" style="background-image: url('assets/img/.png');">
        <h3>Modul SIM</h3>
        <p></p>
        <a href="sim.php" class="btn btn-primary">Mulai Latihan SIM</a>
      </div>
    </div>
</section>

<div class="site-bg">
  <div class="site-paper">
    <div class="full-width-section">
      <section class="card sponsor-strip">
        <div class="sponsor-header">
          <span class="sponsor-label">Didukung oleh</span>
        </div>
        <div class="sponsor-marquee">
          <div class="sponsor-track">
            <img src="assets/img/1.png" alt="Partne">
            <img src="assets/img/2.png" alt="Partner">
            <img src="assets/img/3.jpg" alt="Partner">
            <img src="assets/img/4.png" alt="Partner">
            <img src="assets/img/5.png" alt="Partner">
            <img src="assets/img/6.png" alt="Partner">
            <img src="assets/img/7.jpg" alt="Partner">
            <img src="assets/img/8.png" alt="Partner">
            <img src="assets/img/9.png" alt="Partner">
            <img src="assets/img/10.png" alt="Partner">
            <img src="assets/img/11.jpg" alt="Partner">
            <img src="assets/img/12.png" alt="Partner">
          </div>
          <div class="sponsor-track" aria-hidden="true">
            <img src="assets/img/1.png" alt="Partner">
            <img src="assets/img/2.png" alt="Partner">
            <img src="assets/img/3.jpg" alt="Partner">
            <img src="assets/img/4.png" alt="Partner">
            <img src="assets/img/5.png" alt="Partner">
            <img src="assets/img/6.png" alt="Partner">
            <img src="assets/img/7.jpg" alt="Partner">
            <img src="assets/img/8.png" alt="Partner">
            <img src="assets/img/9.png" alt="Partner">
            <img src="assets/img/10.png" alt="Partner">
            <img src="assets/img/11.jpg" alt="Partner">
            <img src="assets/img/12.png" alt="Partner">
          </div>
        </div>
      </section>
      <div class="promo-slider">
        <button class="promo-nav prev">&#10094;</button>
        <div class="promo-viewport">
          <div class="promo-track">
            <div class="promo-slide">
              <img src="assets/img/promo_1.png" alt="">
            </div>
            <div class="promo-slide">
              <img src="assets/img/promo_2.png" alt="">
            </div>
            <div class="promo-slide">
              <img src="assets/img/promo_3.png" alt="">
            </div>
          </div>
        </div>
        <button class="promo-nav next">&#10095;</button>
      </div>
    </div>
    <div class="container">
      <section class="jenjang-section">
        <h2>Modul Siswa</h2>
        
        <div class="jenjang-grid">
          <a href="modul.php?jenjang=sd" class="jenjang-card">
            <img src="assets/img/thumbnail_sd_fix.png" alt="" class="jenjang-thumb">
            <h3>Sekolah Dasar</h3>
          </a>

          <a href="modul.php?jenjang=smp" class="jenjang-card">
            <img src="assets/img/thumbnail_smp.png" alt="" class="jenjang-thumb">
            <h3>Sekolah Menengah Pertama</h3>
          </a>

          <a href="modul.php?jenjang=sma" class="jenjang-card">
            <img src="assets/img/thumbnail_sma_fix.png" alt="" class="jenjang-thumb">
            <h3>Sekolah Menengah Atas</h3>
          </a>
        </div>
      </section>
    </div>
  </div>
</div>

<div class="chatbot-container">
  <button class="chatbot-toggle" id="chatbotToggle" aria-label="Customer Service">
    <svg class="chat-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
    </svg>
    <svg class="close-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <line x1="18" y1="6" x2="6" y2="18"></line>
      <line x1="6" y1="6" x2="18" y2="18"></line>
    </svg>
  </button>

<div class="chatbot-window" id="chatbotWindow">
  <div class="chatbot-header">
    <div class="chatbot-header-info">
      <div class="chatbot-avatar">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
        </svg>
      </div>
      <div>
        <h3 class="chatbot-title">Customer Service</h3>
          <p class="chatbot-status">
            <span class="status-dot"></span>
            Online
          </p>
      </div>
    </div>
    <button class="chatbot-minimize" id="chatbotMinimize" aria-label="Minimize">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="5" y1="12" x2="19" y2="12"></line>
      </svg>
    </button>
  </div>
  <div class="chatbot-body" id="chatbotBody">
    <div class="chat-message bot-message">
      <div class="message-avatar">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
        </svg>
      </div>
      <div class="message-content">
          <p>Halo 👋 Selamat datang di <strong>Edu Lalin</strong>.</p>
          <p>Kami siap membantu terkait materi, kuis, atau kendala teknis.</p>
        <span class="message-time">Baru saja</span>
      </div>
    </div>

    <div class="quick-replies">
      <button class="quick-reply-btn" data-reply="Apa itu Edu Lalin?">📘 Tentang Edu Lalin</button>
      <button class="quick-reply-btn" data-reply="Cara daftar akun">📝 Cara daftar</button>
      <button class="quick-reply-btn" data-reply="Modul berdasarkan jenjang">🏫 Modul & jenjang</button>
      <button class="quick-reply-btn" data-reply="Kuis tidak bisa dibuka">❗ Masalah kuis</button>
      <button class="quick-reply-btn" data-reply="Lupa password">🔐 Lupa password</button>
      <button class="quick-reply-btn" data-reply="Hubungi admin">📞 Hubungi admin</button>
    </div>
  </div>
  <div class="chatbot-footer">
    <form class="chatbot-form" id="chatbotForm">
      <input
          type="text"
          class="chatbot-input"
          id="chatbotInput"
          placeholder="Ketik pesan Anda..."
          autocomplete="off"
        />
      <button type="submit" class="chatbot-send" aria-label="Send">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
        </svg>
      </button>
    </form>
    <p class="chatbot-footer-text">Powered by Edu Lalin</p>
  </div>
</div>
</div>

<script>
(() => {
  const track = document.querySelector('.promo-track');
  const slides = [...document.querySelectorAll('.promo-slide')];
  const prev = document.querySelector('.promo-nav.prev');
  const next = document.querySelector('.promo-nav.next');

  const gap = 20;  
  const slideWidth = slides[0].offsetWidth + gap;

  slides.forEach(slide => {
    track.appendChild(slide.cloneNode(true));
    track.insertBefore(slide.cloneNode(true), track.firstChild);
  });

  let index = slides.length; 

  function update(animate = true) {
  track.style.transition = animate ? 'transform 0.6s ease' : 'none';

  const slideWidth = slides[0].offsetWidth + gap;
  const center =
    (track.parentElement.offsetWidth - slides[0].offsetWidth) / 2;

  track.style.transform =
    `translateX(${center - index * slideWidth}px)`;

  document.querySelectorAll('.promo-slide').forEach(s => {
    s.classList.remove('dim');
  });

  const allSlides = document.querySelectorAll('.promo-slide');
  allSlides[index - 1]?.classList.add('dim');
  allSlides[index + 1]?.classList.add('dim');
}

  next.onclick = () => {
    index++;
    update();

    if (index === slides.length * 2) {
      setTimeout(() => {
        index = slides.length;
        update(false);
      }, 600);
    }
  };

  prev.onclick = () => {
    index--;
    update();

    if (index === slides.length - 1) {
      setTimeout(() => {
        index = slides.length * 2 - 1;
        update(false);
      }, 600);
    }
  };

  window.addEventListener('resize', () => update(false));
  update(false);
})();
</script>

<script src="assets/js/chatbot.js"></script>

<div class="site-bg" style="margin-top:0;">
  <div class="site-paper">
    <div class="container" style="margin-top:0; margin-bottom:0;">
      <section class="faq-section" style="padding-top:16px; padding-bottom:16px;">
        <h2>Pertanyaan yang Paling Sering Ditanyakan</h2>
        <div class="faq-list">
          <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
              Apa itu Edu Lalin?
              <span class="faq-icon">&#10094;</span>
            </button>
            <div class="faq-answer">
              <p>Edu Lalin adalah platform belajar keselamatan lalu lintas interaktif yang menyediakan materi, video, dan kuis untuk siswa SD, SMP, SMA, serta persiapan ujian SIM.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
              Bagaimana cara mendaftar akun Edu Lalin?
              <span class="faq-icon">&#10094;</span>
            </button>
            <div class="faq-answer">
              <p>Klik tombol <strong>Daftar</strong> di halaman utama, isi data diri seperti nama, email, jenjang, kelas, dan password, lalu verifikasi akun melalui email yang dikirimkan.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
              Apakah materi dan kuis disesuaikan dengan kelas saya?
              <span class="faq-icon">&#10094;</span>
            </button>
            <div class="faq-answer">
              <p>Ya! Setiap materi dan kuis di Edu Lalin disesuaikan berdasarkan jenjang dan kelas yang kamu daftarkan, sehingga konten yang ditampilkan relevan dengan tingkat belajarmu.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
              Berapa nilai minimal untuk lulus kuis?
              <span class="faq-icon">&#10094;</span>
            </button>
            <div class="faq-answer">
              <p>Passing grade di Edu Lalin adalah <strong>70%</strong>. Jika belum mencapai nilai tersebut, kamu bisa mengulang kuis setelah mempelajari materi kembali.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
              Apakah Edu Lalin tersedia untuk persiapan ujian SIM?
              <span class="faq-icon">&#10094;</span>
            </button>
            <div class="faq-answer">
              <p>Ya! Edu Lalin menyediakan <strong>Modul SIM</strong> khusus yang berisi materi keselamatan berkendara dan simulasi soal ujian SIM berdasarkan usia pengguna.</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>

<?php if ($showBirthday): ?>
<div id="birthdayModal" class="birthday-modal">
  <div class="birthday-box">
    <h2>🎉 Selamat Ulang Tahun!</h2>
    <p>Selamat ulang tahun, <strong><?= htmlspecialchars($birthdayName) ?></strong>! 🎂</p>
    <p>Semoga sehat selalu dan semakin semangat belajar 🚀</p>
    <button id="closeBirthday">Tutup</button>
  </div>
</div>
<?php endif; ?>
<?php include 'footer_common.php'; ?>
<script>
const modal = document.getElementById('birthdayModal');
const btn   = document.getElementById('closeBirthday');

if (btn) {
  btn.addEventListener('click', function() {

    fetch('birthday_close.php', {
      method: 'POST'
    }).then(() => {
      modal.style.display = 'none';
    });

  });
}
</script>

<script>
function toggleFaq(btn) {
  const item = btn.closest('.faq-item');
  const isOpen = item.classList.contains('open');
  document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
  if (!isOpen) item.classList.add('open');
}
</script>

</body>
</html>