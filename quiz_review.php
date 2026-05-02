<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Review Jawaban</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Segoe UI', system-ui, sans-serif;
  background: #0f1117;
  color: #fff;
  min-height: 100dvh;
}

.topbar {
  height: 56px;
  background: #1a1d2e;
  border-bottom: 1px solid rgba(255,255,255,0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  position: sticky;
  top: 0;
  z-index: 10;
}

.topbar-left { display: flex; align-items: center; gap: 10px; }
.topbar-logo { font-size: 16px; font-weight: 800; color: #facc15; }
.topbar-title { font-size: 13px; color: rgba(255,255,255,0.5); }

.back-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 8px;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  color: rgba(255,255,255,0.7);
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.back-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }

.container {
  max-width: 720px;
  margin: 0 auto;
  padding: 28px 20px 60px;
}

.summary-bar {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
  margin-bottom: 28px;
}

.sum-card {
  background: #1a1d2e;
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 14px;
  padding: 14px 10px;
  text-align: center;
}

.sum-val { font-size: 24px; font-weight: 800; }
.sum-lbl { font-size: 11px; color: rgba(255,255,255,0.45); margin-top: 3px; }

.section-title {
  font-size: 14px;
  font-weight: 700;
  color: rgba(255,255,255,0.5);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 14px;
}

.review-item {
  background: #1a1d2e;
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 12px;
  position: relative;
  overflow: hidden;
}

.review-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  border-radius: 4px 0 0 4px;
}

.review-item.correct::before { background: #22c55e; }
.review-item.wrong::before   { background: #ef4444; }
.review-item.timeout::before { background: #f59e0b; }

.review-num {
  font-size: 11px;
  font-weight: 700;
  color: rgba(255,255,255,0.35);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.review-badge {
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 10px;
  font-weight: 800;
}

.badge-correct { background: rgba(34,197,94,0.2);  color: #4ade80; }
.badge-wrong   { background: rgba(239,68,68,0.2);  color: #f87171; }
.badge-timeout { background: rgba(245,158,11,0.2); color: #fbbf24; }

.review-soal {
  font-size: 15px;
  font-weight: 600;
  line-height: 1.5;
  margin-bottom: 14px;
  color: #fff;
}

.review-opts {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

@media (max-width: 480px) {
  .review-opts { grid-template-columns: 1fr; }
  .summary-bar { grid-template-columns: repeat(2, 1fr); }
}

.ropt {
  padding: 10px 12px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px solid rgba(255,255,255,0.06);
  background: rgba(255,255,255,0.04);
  color: rgba(255,255,255,0.6);
}

.ropt-letter {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: rgba(255,255,255,0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 800;
  flex-shrink: 0;
}

.ropt.is-correct {
  background: rgba(34,197,94,0.12);
  border-color: rgba(34,197,94,0.3);
  color: #4ade80;
}

.ropt.is-correct .ropt-letter { background: #22c55e; color: #fff; }

.ropt.is-wrong {
  background: rgba(239,68,68,0.12);
  border-color: rgba(239,68,68,0.3);
  color: #f87171;
}

.ropt.is-wrong .ropt-letter { background: #ef4444; color: #fff; }

.review-poin {
  margin-top: 12px;
  font-size: 12px;
  color: rgba(255,255,255,0.4);
  display: flex;
  align-items: center;
  gap: 5px;
}

.review-poin span { color: #facc15; font-weight: 700; }

.empty-review {
  text-align: center;
  padding: 80px 20px;
  color: rgba(255,255,255,0.4);
}

.empty-review a {
  display: inline-block;
  margin-top: 16px;
  color: #3b82f6;
  text-decoration: none;
  font-size: 14px;
}
</style>
</head>
<body>

<div class="topbar">
  <div class="topbar-left">
    <div class="topbar-logo">EduLalin</div>
    <div class="topbar-title" id="reviewTitle">Review Jawaban</div>
  </div>
  <button class="back-btn" onclick="history.back()">← Kembali</button>
</div>

<div class="container" id="reviewContainer">
  <div class="empty-review" id="emptyMsg" style="display:none">
    <div style="font-size:48px;margin-bottom:14px">📭</div>
    <div>Data review tidak ditemukan.</div>
    <a href="modul.php">← Kembali ke Modul</a>
  </div>
</div>

<script>
const data = JSON.parse(sessionStorage.getItem('quiz_review') || 'null');
const container = document.getElementById('reviewContainer');

if (!data) {
  document.getElementById('emptyMsg').style.display = 'block';
} else {
  document.getElementById('reviewTitle').textContent = 'Review — ' + data.judul;

  // Summary bar
  const sumBar = document.createElement('div');
  sumBar.className = 'summary-bar';
  sumBar.innerHTML = `
    <div class="sum-card">
      <div class="sum-val" style="color:#facc15">${data.poin.toLocaleString()}</div>
      <div class="sum-lbl">Total Poin</div>
    </div>
    <div class="sum-card">
      <div class="sum-val" style="color:#22c55e">${data.benar}</div>
      <div class="sum-lbl">Benar</div>
    </div>
    <div class="sum-card">
      <div class="sum-val" style="color:#ef4444">${data.salah}</div>
      <div class="sum-lbl">Salah</div>
    </div>
    <div class="sum-card">
      <div class="sum-val" style="color:#a78bfa">${data.pct}%</div>
      <div class="sum-lbl">Skor</div>
    </div>
  `;
  container.appendChild(sumBar);

  // Section title
  const secTitle = document.createElement('div');
  secTitle.className   = 'section-title';
  secTitle.textContent = 'Detail per Soal';
  container.appendChild(secTitle);

  // Render tiap soal
  const optsMap = { A: 'opsi_a', B: 'opsi_b', C: 'opsi_c', D: 'opsi_d' };

  data.detail.forEach((item, i) => {
    const isTimeout = item.timeout;
    const isCorrect = item.is_correct;
    const stateClass = isTimeout ? 'timeout' : (isCorrect ? 'correct' : 'wrong');

    const div = document.createElement('div');
    div.className = 'review-item ' + stateClass;

    const badgeClass = isTimeout ? 'badge-timeout' : (isCorrect ? 'badge-correct' : 'badge-wrong');
    const badgeText  = isTimeout ? '⏱ Waktu Habis' : (isCorrect ? '✓ Benar' : '✗ Salah');

    // Render 4 opsi
    let optsHTML = '';
    ['A','B','C','D'].forEach(letter => {
      const key  = optsMap[letter];
      const text = item[key] || '';
      let cls    = 'ropt';
      if (letter === item.benar)  cls += ' is-correct';
      if (letter === item.jawaban && !isCorrect) cls += ' is-wrong';
      optsHTML += `
        <div class="${cls}">
          <div class="ropt-letter">${letter}</div>
          <span>${escHtml(text)}</span>
        </div>
      `;
    });

    div.innerHTML = `
      <div class="review-num">
        Soal ${i + 1}
        <span class="review-badge ${badgeClass}">${badgeText}</span>
      </div>
      <div class="review-soal">${escHtml(item.soal)}</div>
      <div class="review-opts">${optsHTML}</div>
      <div class="review-poin">
        ⭐ Poin didapat: <span>${item.poin || 0}</span>
      </div>
    `;

    container.appendChild(div);
  });
}

function escHtml(str) {
  return String(str)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;');
}
</script>
</body>
</html>