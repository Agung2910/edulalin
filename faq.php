<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>FAQ - Edu Lalin</title>

<style>
body {
  font-family: system-ui, Arial, sans-serif;
  background: #f8fafc;
  padding: 40px;
}

.page {
  max-width: 800px;
  margin: auto;
  background: #fff;
  padding: 32px;
  border-radius: 14px;
  box-shadow: 0 10px 30px rgba(0,0,0,.08);
}

h1 {
  margin-bottom: 20px;
}

/* FAQ */
.faq-item {
  border-bottom: 1px solid #e5e7eb;
}

.faq-question {
  width: 100%;
  background: none;
  border: none;
  text-align: left;
  font-size: 16px;
  font-weight: 600;
  padding: 14px 0;
  cursor: pointer;
}

.faq-answer {
  max-height: 0;
  overflow: hidden;
  transition: max-height .3s ease;
}

.faq-answer p {
  font-size: 14px;
  color: #475569;
  line-height: 1.6;
  padding-bottom: 12px;
}
</style>
</head>

<body>

<div class="page">
  <h1>Frequently Asked Questions (FAQ)</h1>

  <div class="faq-item">
    <button class="faq-question">Apa itu Edu Lalin?</button>
    <div class="faq-answer">
      <p>Edu Lalin adalah platform pembelajaran keselamatan lalu lintas yang dirancang untuk siswa SD, SMP, SMA/SMK, serta masyarakat umum.</p>
    </div>
  </div>

  <div class="faq-item">
    <button class="faq-question">Apakah Edu Lalin gratis?</button>
    <div class="faq-answer">
      <p>Ya, seluruh materi dan kuis dapat diakses secara gratis untuk tujuan edukasi.</p>
    </div>
  </div>

  <div class="faq-item">
    <button class="faq-question">Apakah saya harus membuat akun?</button>
    <div class="faq-answer">
      <p>Anda dapat melihat sebagian konten tanpa akun, namun untuk mengakses kuis dan menyimpan progres, Anda perlu login.</p>
    </div>
  </div>

  <div class="faq-item">
    <button class="faq-question">Apakah data saya aman?</button>
    <div class="faq-answer">
      <p>Kami menjaga keamanan data pengguna dan tidak membagikannya kepada pihak lain tanpa izin.</p>
    </div>
  </div>

  <div class="faq-item">
    <button class="faq-question">Apakah bisa diakses lewat HP?</button>
    <div class="faq-answer">
      <p>Ya, Edu Lalin dapat diakses melalui perangkat mobile maupun desktop.</p>
    </div>
  </div>

  <div class="faq-item">
    <button class="faq-question">Apakah hasil kuis saya disimpan?</button>
    <div class="faq-answer">
      <p>Ya, hasil kuis akan disimpan dan dapat digunakan untuk melihat perkembangan belajar Anda.</p>
    </div>
  </div>

</div>

<script>
document.querySelectorAll(".faq-question").forEach(btn => {
  btn.addEventListener("click", () => {
    const answer = btn.nextElementSibling;

    // tutup semua
    document.querySelectorAll(".faq-answer").forEach(a => {
      if (a !== answer) a.style.maxHeight = null;
    });

    // toggle
    if (answer.style.maxHeight) {
      answer.style.maxHeight = null;
    } else {
      answer.style.maxHeight = answer.scrollHeight + "px";
    }
  });
});
</script>

</body>
</html>