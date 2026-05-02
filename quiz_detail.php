<?php
require_once "config.php";
require_login();

$user_id   = $_SESSION['user_id'];
$username  = $_SESSION['nama'] ?? $_SESSION['username'] ?? 'User';
$materi_id = (int)($_GET['materi_id'] ?? 0);

if ($materi_id <= 0) { header("Location: modul.php"); exit; }

// Ambil info modul
$stmtm = $conn->prepare("SELECT id, judul, jenjang, kelas FROM modul WHERE id=?");
$stmtm->bind_param("i", $materi_id);
$stmtm->execute();
$stmtm->bind_result($modul_id_db, $judul_modul, $jenjang_modul, $kelas_modul);
$stmtm->fetch();
$stmtm->close();

// Ambil soal
$stmt = $conn->prepare("
    SELECT id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban_benar
    FROM quiz
    WHERE materi_id = ? AND is_active = 1
    ORDER BY RAND()
    LIMIT 10
");
$stmt->bind_param("i", $materi_id);
$stmt->execute();
$result    = $stmt->get_result();
$questions = [];
while ($row = $result->fetch_assoc()) $questions[] = $row;
$stmt->close();

// Ambil modul_id asli untuk tombol kembali ke materi
$stmtMod = $conn->prepare("SELECT id FROM modul WHERE id = ?");
$stmtMod->bind_param("i", $materi_id);
$stmtMod->execute();
$stmtMod->bind_result($the_modul_id);
$stmtMod->fetch();
$stmtMod->close();
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quiz — <?= htmlspecialchars($judul_modul) ?></title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --col-a: #3b82f6;
  --col-b: #10b981;
  --col-c: #f59e0b;
  --col-d: #ef4444;
  --bg: #0f1117;
  --surface: #1a1d2e;
  --border: rgba(255,255,255,0.08);
  --text: #ffffff;
  --muted: rgba(255,255,255,0.45);
  --timer-default: 20;
}

body {
  font-family: 'Segoe UI', system-ui, sans-serif;
  background: var(--bg);
  color: var(--text);
  height: 100dvh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  user-select: none;
}

/* ─── TOPBAR ─── */
.topbar {
  height: 56px;
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  flex-shrink: 0;
  gap: 12px;
  z-index: 10;
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.topbar-logo {
  font-size: 18px;
  font-weight: 800;
  color: #facc15;
  letter-spacing: -0.5px;
  white-space: nowrap;
}

.topbar-title {
  font-size: 13px;
  color: var(--muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 200px;
}

.topbar-sep { color: var(--border); }

.topbar-center {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 10px;
  justify-content: center;
}

.progress-track {
  flex: 1;
  max-width: 280px;
  height: 6px;
  background: rgba(255,255,255,0.1);
  border-radius: 999px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #facc15;
  border-radius: 999px;
  transition: width 0.4s ease;
  width: 0%;
}

.progress-label {
  font-size: 12px;
  font-weight: 700;
  color: var(--muted);
  white-space: nowrap;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
}

.score-display {
  display: flex;
  align-items: center;
  gap: 6px;
  background: rgba(250,204,21,0.12);
  border: 1px solid rgba(250,204,21,0.25);
  border-radius: 999px;
  padding: 4px 12px;
  font-size: 13px;
  font-weight: 800;
  color: #facc15;
}

.user-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  background: rgba(255,255,255,0.06);
  border-radius: 999px;
  padding: 4px 12px;
  font-size: 12px;
  font-weight: 600;
  color: rgba(255,255,255,0.7);
}

.streak-display {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 13px;
  font-weight: 800;
  color: #f97316;
  opacity: 0;
  transition: opacity 0.3s;
  white-space: nowrap;
}

.streak-display.visible { opacity: 1; }

/* ─── TIMER BAR ─── */
.timer-wrap {
  flex-shrink: 0;
  padding: 0 20px;
  padding-top: 10px;
}

.timer-track {
  height: 8px;
  background: rgba(255,255,255,0.08);
  border-radius: 999px;
  overflow: hidden;
}

.timer-fill {
  height: 100%;
  width: 100%;
  border-radius: 999px;
  background: #22c55e;
  transition: width 1s linear, background 0.5s;
}

.timer-fill.warn  { background: #f59e0b; }
.timer-fill.danger{ background: #ef4444; }

.timer-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 5px;
}

.timer-num {
  font-size: 13px;
  font-weight: 800;
  color: #22c55e;
  transition: color 0.5s;
  min-width: 50px;
}

.timer-num.warn   { color: #f59e0b; }
.timer-num.danger { color: #ef4444; }

.timer-pts {
  font-size: 12px;
  color: var(--muted);
}

/* ─── POWER-UPS ─── */
.powerup-row {
  display: flex;
  gap: 8px;
  justify-content: center;
  padding: 6px 20px 0;
  flex-shrink: 0;
}

.pu-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,0.15);
  background: rgba(255,255,255,0.06);
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  font-family: inherit;
}

.pu-btn:hover:not(:disabled) {
  background: rgba(255,255,255,0.12);
  transform: translateY(-1px);
}

.pu-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
  filter: grayscale(1);
}

.pu-btn.pu-5050 { border-color: rgba(99,102,241,0.4); color: #a5b4fc; }
.pu-btn.pu-time { border-color: rgba(34,197,94,0.4);  color: #86efac; }

/* ─── QUIZ BODY ─── */
.quiz-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 12px 20px 16px;
  gap: 12px;
  overflow: hidden;
  min-height: 0;
}

/* ─── QUESTION BOX ─── */
.question-box {
  flex: 1;
  background: var(--surface);
  border-radius: 18px;
  border: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 28px 40px;
  text-align: center;
  position: relative;
  overflow: hidden;
  min-height: 0;
}

.question-box::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at 50% 0%, rgba(99,102,241,0.1) 0%, transparent 70%);
  pointer-events: none;
}

.q-num-label {
  position: absolute;
  top: 14px;
  left: 18px;
  font-size: 11px;
  font-weight: 700;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.question-text {
  font-size: clamp(16px, 2vw, 24px);
  font-weight: 600;
  line-height: 1.55;
  color: #fff;
  position: relative;
  z-index: 1;
  transition: opacity 0.2s;
}

/* ─── OPTIONS ─── */
.options-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
  gap: 12px;
  flex-shrink: 0;
  height: 160px;
}

@media (max-width: 768px) {
  .options-grid {
    grid-template-columns: 1fr 1fr;
    height: auto;
    min-height: 200px;
  }

  .opt-btn { min-height: 90px; }
}

.opt-btn {
  width: 100%;
  height: 100%;
  border-radius: 16px;
  border: 2px solid transparent;
  cursor: pointer;
  font-size: clamp(13px, 1.3vw, 17px);
  font-weight: 600;
  font-family: inherit;
  color: #fff;
  line-height: 1.4;
  padding: 12px 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: transform 0.15s, filter 0.15s, opacity 0.3s;
  text-align: center;
  word-break: break-word;
  position: relative;
  overflow: hidden;
}

.opt-btn::after {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(255,255,255,0);
  transition: background 0.15s;
  border-radius: inherit;
}

.opt-btn:hover:not(:disabled)::after { background: rgba(255,255,255,0.1); }
.opt-btn:hover:not(:disabled) { transform: translateY(-3px); }
.opt-btn:active:not(:disabled) { transform: scale(0.97); }

.opt-letter {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: rgba(255,255,255,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 800;
  flex-shrink: 0;
}

.opt-a { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.opt-b { background: linear-gradient(135deg, #10b981, #059669); }
.opt-c { background: linear-gradient(135deg, #f59e0b, #d97706); }
.opt-d { background: linear-gradient(135deg, #ef4444, #dc2626); }

/* State setelah jawab */
.opt-btn.correct {
  outline: 3px solid #22c55e;
  outline-offset: 2px;
  filter: brightness(1.2);
}

.opt-btn.wrong {
  outline: 3px solid #dc2626;
  outline-offset: 2px;
  filter: brightness(0.65);
}

.opt-btn.hidden-opt {
  opacity: 0.15;
  pointer-events: none;
}

.opt-btn:disabled { cursor: not-allowed; }

/* ─── ANIMASI SOAL ─── */
@keyframes slideInRight {
  from { transform: translateX(60px); opacity: 0; }
  to   { transform: translateX(0);    opacity: 1; }
}

@keyframes slideOutLeft {
  from { transform: translateX(0);     opacity: 1; }
  to   { transform: translateX(-60px); opacity: 0; }
}

@keyframes shakeX {
  0%,100% { transform: translateX(0); }
  20%      { transform: translateX(-8px); }
  40%      { transform: translateX(8px); }
  60%      { transform: translateX(-6px); }
  80%      { transform: translateX(6px); }
}

@keyframes bounceIn {
  0%   { transform: scale(0.3); opacity: 0; }
  50%  { transform: scale(1.1); }
  70%  { transform: scale(0.95); }
  100% { transform: scale(1);   opacity: 1; }
}

.slide-in  { animation: slideInRight 0.35s ease both; }
.shake     { animation: shakeX 0.45s ease; }

/* ─── POIN POP ─── */
.pts-pop {
  position: fixed;
  font-size: 22px;
  font-weight: 900;
  color: #facc15;
  pointer-events: none;
  z-index: 999;
  text-shadow: 0 2px 10px rgba(0,0,0,0.5);
  animation: ptsFly 0.9s ease forwards;
}

@keyframes ptsFly {
  0%   { transform: translateY(0)    scale(1);    opacity: 1; }
  100% { transform: translateY(-80px) scale(1.3); opacity: 0; }
}

/* ─── STREAK POP ─── */
.streak-pop {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 48px;
  font-weight: 900;
  z-index: 999;
  pointer-events: none;
  animation: bounceIn 0.5s ease both, fadeOut 0.4s ease 0.9s both;
  text-shadow: 0 4px 20px rgba(0,0,0,0.6);
}

@keyframes fadeOut {
  to { opacity: 0; transform: translate(-50%,-50%) scale(0.8); }
}

/* ─── TIMEOUT FLASH ─── */
.timeout-flash {
  position: fixed;
  inset: 0;
  background: rgba(239,68,68,0.15);
  pointer-events: none;
  z-index: 50;
  animation: flashFade 0.6s ease forwards;
}

@keyframes flashFade {
  0%   { opacity: 1; }
  100% { opacity: 0; }
}

/* ─── RESULT SCREEN ─── */
#resultScreen {
  display: none;
  position: fixed;
  inset: 0;
  align-items: center;
  justify-content: center;
  z-index: 200;
  overflow: hidden;
}

.result-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.7);
  backdrop-filter: blur(6px);
}

.result-card {
  background: var(--surface);
  border: 1px solid rgba(255,255,255,0.1);
  padding: 36px 32px;
  border-radius: 24px;
  width: 480px;
  max-width: 92vw;
  max-height: 90dvh;
  overflow-y: auto;
  text-align: center;
  position: relative;
  z-index: 2;
  animation: popIn 0.45s cubic-bezier(0.34,1.56,0.64,1) both;
}

.result-card::-webkit-scrollbar { width: 4px; }
.result-card::-webkit-scrollbar-track { background: transparent; }
.result-card::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }

@keyframes popIn {
  from { transform: scale(0.8) translateY(30px); opacity: 0; }
  to   { transform: scale(1)   translateY(0);    opacity: 1; }
}

.result-jenjang {
  display: inline-block;
  padding: 4px 14px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.06em;
  margin-bottom: 16px;
  color: #fff;
}

.score-ring {
  width: 140px;
  height: 140px;
  border-radius: 50%;
  border: 9px solid;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin: 0 auto 18px;
  position: relative;
}

.score-pct  { font-size: 36px; font-weight: 900; line-height: 1; }
.score-sub  { font-size: 11px; opacity: 0.5; margin-top: 2px; }
.rank-title { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
.rank-msg   { font-size: 13px; color: var(--muted); margin-bottom: 18px; line-height: 1.5; }

.result-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
  margin-bottom: 14px;
}

.rstat {
  background: rgba(255,255,255,0.05);
  border-radius: 12px;
  padding: 10px 6px;
}

.rstat-val { font-size: 20px; font-weight: 800; }
.rstat-lbl { font-size: 10px; opacity: 0.5; margin-top: 2px; }

.result-poin {
  background: rgba(250,204,21,0.1);
  border: 1px solid rgba(250,204,21,0.25);
  border-radius: 12px;
  padding: 12px 16px;
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.result-poin-num {
  font-size: 28px;
  font-weight: 900;
  color: #facc15;
}

.result-poin-lbl {
  font-size: 12px;
  color: var(--muted);
  text-align: left;
  line-height: 1.4;
}

.result-achievement {
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(255,255,255,0.04);
  border-radius: 12px;
  padding: 12px 14px;
  margin-bottom: 18px;
  text-align: left;
  border: 1px solid rgba(255,255,255,0.07);
}

.result-achievement.pass { border-color: rgba(34,197,94,0.25); background: rgba(34,197,94,0.06); }
.result-achievement.fail { border-color: rgba(245,158,11,0.25); background: rgba(245,158,11,0.06); }
.ra-icon  { font-size: 24px; flex-shrink: 0; }
.ra-title { font-size: 13px; font-weight: 700; }
.ra-desc  { font-size: 11px; opacity: 0.6; margin-top: 2px; line-height: 1.4; }

.saved-note {
  font-size: 11px;
  color: var(--muted);
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
}

.result-btns {
  display: flex;
  gap: 8px;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 12px;
}

.rbtn {
  padding: 10px 20px;
  border-radius: 10px;
  border: none;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: transform 0.15s, filter 0.15s;
  font-family: inherit;
  color: #fff;
}

.rbtn:hover { transform: translateY(-2px); filter: brightness(1.1); }
.rbtn-retry  { background: #3b82f6; }
.rbtn-review { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); }
.rbtn-back   {
  display: block;
  text-align: center;
  font-size: 12px;
  color: var(--muted);
  text-decoration: none;
  transition: color 0.2s;
}
.rbtn-back:hover { color: #fff; }

/* ─── CONFETTI ─── */
.confetti-piece {
  position: absolute;
  border-radius: 2px;
  animation: cfall linear infinite;
  z-index: 1;
}

@keyframes cfall {
  0%   { transform: translateY(-80px) rotate(0deg);   opacity: 1; }
  100% { transform: translateY(105vh) rotate(720deg); opacity: 0; }
}

/* ─── EMPTY ─── */
.empty-screen {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  gap: 14px;
  font-size: 18px;
}

/* ─── MULTIPLIER BADGE ─── */
.multi-badge {
  position: fixed;
  top: 70px;
  right: 20px;
  background: linear-gradient(135deg, #f97316, #dc2626);
  color: #fff;
  font-size: 11px;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 999px;
  z-index: 50;
  opacity: 0;
  transition: opacity 0.3s;
  letter-spacing: 0.04em;
}

.multi-badge.visible { opacity: 1; }
</style>
</head>
<body>

<?php if (count($questions) === 0): ?>
<div class="empty-screen">
  <div style="font-size:52px">📭</div>
  <div style="font-weight:600">Belum ada soal untuk modul ini.</div>
  <a href="modul.php" style="color:#3b82f6;font-size:14px;margin-top:4px">← Kembali ke Modul</a>
</div>

<?php else: ?>

<!-- TOPBAR -->
<div class="topbar">
  <div class="topbar-left">
    <div class="topbar-logo">EduLalin</div>
    <span class="topbar-sep">·</span>
    <div class="topbar-title"><?= htmlspecialchars($judul_modul) ?></div>
  </div>

  <div class="topbar-center">
    <div class="progress-track">
      <div class="progress-fill" id="progressFill"></div>
    </div>
    <span class="progress-label"><span id="qNum">1</span>/<?= count($questions) ?></span>
  </div>

  <div class="topbar-right">
    <div class="streak-display" id="streakDisplay">🔥 <span id="streakNum">0</span></div>
    <div class="score-display">⭐ <span id="liveScore">0</span></div>
    <div class="user-badge">👤 <?= htmlspecialchars($username) ?></div>
  </div>
</div>

<!-- TIMER -->
<div class="timer-wrap">
  <div class="timer-track">
    <div class="timer-fill" id="timerFill"></div>
  </div>
  <div class="timer-row">
    <span class="timer-num" id="timerNum">20</span>
    <span class="timer-pts" id="timerPts">Jawab cepat = poin lebih besar!</span>
  </div>
</div>

<!-- POWER-UPS -->
<div class="powerup-row">
  <button class="pu-btn pu-5050" id="btn5050" onclick="use5050()">
    ✂️ 50/50 <span style="opacity:0.5;font-size:10px">(1x)</span>
  </button>
  <button class="pu-btn pu-time" id="btnTime" onclick="useAddTime()">
    ⏱️ +10 Detik <span style="opacity:0.5;font-size:10px">(1x)</span>
  </button>
</div>

<!-- QUIZ BODY -->
<div class="quiz-body" id="quizScreen">
  <!-- Question -->
  <div class="question-box" id="questionBox">
    <span class="q-num-label" id="qLabel">Pertanyaan 1</span>
    <div class="question-text" id="qText"></div>
  </div>

  <!-- Options -->
  <div class="options-grid" id="optionsGrid">
    <button class="opt-btn opt-a" data-opt="A" id="optA">
      <span class="opt-letter">A</span>
      <span id="txtA"></span>
    </button>
    <button class="opt-btn opt-b" data-opt="B" id="optB">
      <span class="opt-letter">B</span>
      <span id="txtB"></span>
    </button>
    <button class="opt-btn opt-c" data-opt="C" id="optC">
      <span class="opt-letter">C</span>
      <span id="txtC"></span>
    </button>
    <button class="opt-btn opt-d" data-opt="D" id="optD">
      <span class="opt-letter">D</span>
      <span id="txtD"></span>
    </button>
  </div>
</div>

<!-- MULTIPLIER BADGE -->
<div class="multi-badge" id="multiBadge">🔥 2x COMBO!</div>

<!-- RESULT SCREEN -->
<div id="resultScreen">
  <div class="result-overlay"></div>
  <div class="result-card" id="resultCard"></div>
</div>

<script>
// ── DATA ──
const questions = <?= json_encode($questions, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>;
const materi_id  = <?= $materi_id ?>;
const modul_id   = <?= $the_modul_id ?>;
const jenjang    = <?= json_encode(strtoupper($jenjang_modul)) ?>;
const kelas      = <?= json_encode($kelas_modul) ?>;
const total      = questions.length;
const TIMER_SEC  = 20;

// ── STATE ──
let idx         = 0;
let totalPoin   = 0;
let benar       = 0;
let salah       = 0;
let streak      = 0;
let maxStreak   = 0;
let timeLeft    = TIMER_SEC;
let timerInt    = null;
let answered    = false;
let avgTime     = [];
let detail      = []; // untuk review & save
let pu5050Used  = false;
let puTimeUsed  = false;

// ── HELPERS ──
function $(id) { return document.getElementById(id); }

function calcPoin(timeUsed, multiplier) {
  const ratio = 1 - (timeUsed / TIMER_SEC);
  const base  = Math.round(100 + (900 * ratio));
  return Math.round(Math.min(1000, Math.max(100, base)) * multiplier);
}

function getMultiplier() {
  if (streak >= 10) return 2.0;
  if (streak >= 5)  return 1.5;
  if (streak >= 3)  return 1.25;
  return 1.0;
}

function getMultiplierLabel() {
  if (streak >= 10) return '🔥 2x COMBO!';
  if (streak >= 5)  return '🔥 1.5x COMBO!';
  if (streak >= 3)  return '🔥 1.25x COMBO!';
  return '';
}

// ── TIMER ──
function startTimer() {
  clearInterval(timerInt);
  timeLeft = TIMER_SEC;
  updateTimerUI();

  timerInt = setInterval(() => {
    timeLeft--;
    updateTimerUI();

    // Potensial poin real-time
    const pct = timeLeft / TIMER_SEC;
    $('timerPts').textContent = '⭐ Potensi poin: ' + Math.round(100 + 900 * pct);

    if (timeLeft <= 0) {
      clearInterval(timerInt);
      onTimeout();
    }
  }, 1000);
}

function updateTimerUI() {
  const pct = (timeLeft / TIMER_SEC) * 100;
  $('timerFill').style.width = pct + '%';
  $('timerNum').textContent  = timeLeft;

  const fill = $('timerFill');
  const num  = $('timerNum');

  fill.className = 'timer-fill';
  num.className  = 'timer-num';

  if (timeLeft <= 5) {
    fill.classList.add('danger');
    num.classList.add('danger');
  } else if (timeLeft <= 10) {
    fill.classList.add('warn');
    num.classList.add('warn');
  }
}

function onTimeout() {
  if (answered) return;
  answered = true;

  // Flash merah
  const flash = document.createElement('div');
  flash.className = 'timeout-flash';
  document.body.appendChild(flash);
  setTimeout(() => flash.remove(), 700);

  // Tampilkan jawaban benar
  const correct = questions[idx].jawaban_benar;
  document.querySelectorAll('.opt-btn').forEach(b => {
    b.disabled = true;
    if (b.dataset.opt === correct) b.classList.add('correct');
  });

  // Streak reset
  streak = 0;
  updateStreakUI();

  // Catat detail
  detail.push({
    soal: questions[idx].pertanyaan,
    opsi_a: questions[idx].opsi_a,
    opsi_b: questions[idx].opsi_b,
    opsi_c: questions[idx].opsi_c,
    opsi_d: questions[idx].opsi_d,
    jawaban: null,
    benar: correct,
    poin: 0,
    timeout: true
  });
  salah++;
  avgTime.push(TIMER_SEC);

  setTimeout(nextQ, 1200);
}

// ── LOAD SOAL ──
function loadQ() {
  answered = false;
  const q = questions[idx];

  $('qNum').textContent   = idx + 1;
  $('qLabel').textContent = 'Pertanyaan ' + (idx + 1) + ' dari ' + total;
  $('qText').textContent  = q.pertanyaan;
  $('txtA').textContent   = q.opsi_a;
  $('txtB').textContent   = q.opsi_b;
  $('txtC').textContent   = q.opsi_c;
  $('txtD').textContent   = q.opsi_d;

  $('progressFill').style.width = ((idx + 1) / total * 100) + '%';
  $('timerPts').textContent     = 'Jawab cepat = poin lebih besar!';

  // Reset tombol
  document.querySelectorAll('.opt-btn').forEach(b => {
    b.classList.remove('correct', 'wrong', 'hidden-opt');
    b.disabled  = false;
    b.style.opacity = '';
  });

  // Animasi masuk
  $('questionBox').classList.remove('slide-in');
  $('optionsGrid').classList.remove('slide-in');
  void $('questionBox').offsetWidth;
  $('questionBox').classList.add('slide-in');
  $('optionsGrid').classList.add('slide-in');

  startTimer();
}

// ── JAWAB ──
document.querySelectorAll('.opt-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    if (answered) return;
    answered = true;
    clearInterval(timerInt);

    const timeUsed  = TIMER_SEC - timeLeft;
    const sel       = this.dataset.opt;
    const correct   = questions[idx].jawaban_benar;
    const isCorrect = sel === correct;
    const multi     = getMultiplier();
    const poin      = isCorrect ? calcPoin(timeUsed, multi) : 0;

    // Tampilkan state
    document.querySelectorAll('.opt-btn').forEach(b => {
      b.disabled = true;
      if (b.dataset.opt === correct)               b.classList.add('correct');
      if (b.dataset.opt === sel && !isCorrect)     b.classList.add('wrong');
    });

    if (isCorrect) {
      benar++;
      streak++;
      if (streak > maxStreak) maxStreak = streak;
      totalPoin += poin;
      $('liveScore').textContent = totalPoin;

      // Pop poin
      showPtsPop('+' + poin, this);

      // Streak pop khusus
      if (streak === 3 || streak === 5 || streak === 10) {
        showStreakPop();
      }

      // Confetti kecil
      spawnMiniConfetti();
    } else {
      salah++;
      streak = 0;
      $('questionBox').classList.add('shake');
      setTimeout(() => $('questionBox').classList.remove('shake'), 500);
    }

    updateStreakUI();
    avgTime.push(timeUsed);

    // Catat detail
    detail.push({
      soal: questions[idx].pertanyaan,
      opsi_a: questions[idx].opsi_a,
      opsi_b: questions[idx].opsi_b,
      opsi_c: questions[idx].opsi_c,
      opsi_d: questions[idx].opsi_d,
      jawaban: sel,
      benar: correct,
      poin: poin,
      timeout: false,
      is_correct: isCorrect
    });

    setTimeout(nextQ, 1100);
  });
});

function nextQ() {
  idx++;
  if (idx < total) loadQ();
  else endQuiz();
}

// ── UI HELPERS ──
function updateStreakUI() {
  const sd = $('streakDisplay');
  $('streakNum').textContent = streak;
  sd.classList.toggle('visible', streak >= 2);

  const mb = $('multiBadge');
  const lbl = getMultiplierLabel();
  if (lbl) {
    mb.textContent = lbl;
    mb.classList.add('visible');
  } else {
    mb.classList.remove('visible');
  }
}

function showPtsPop(text, btn) {
  const r   = btn.getBoundingClientRect();
  const pop = document.createElement('div');
  pop.className    = 'pts-pop';
  pop.textContent  = text;
  pop.style.left   = (r.left + r.width / 2 - 20) + 'px';
  pop.style.top    = (r.top - 10) + 'px';
  document.body.appendChild(pop);
  setTimeout(() => pop.remove(), 950);
}

function showStreakPop() {
  const pop = document.createElement('div');
  pop.className   = 'streak-pop';
  pop.textContent = streak >= 5 ? '🔥🔥🔥 x' + streak + ' COMBO!!'
                  : streak >= 3  ? '🔥🔥 x' + streak + ' Combo!'
                  :                '🔥 x' + streak + ' Combo!';
  document.body.appendChild(pop);
  setTimeout(() => pop.remove(), 1400);
}

function spawnMiniConfetti() {
  const colors = ['#facc15','#3b82f6','#10b981','#f472b6','#a78bfa'];
  for (let i = 0; i < 12; i++) {
    const c   = document.createElement('div');
    const col = colors[Math.floor(Math.random() * colors.length)];
    const sz  = 5 + Math.random() * 6;
    c.style.cssText = `
      position:fixed;
      left:${20 + Math.random()*60}%;
      top:${60 + Math.random()*20}%;
      width:${sz}px;height:${sz}px;
      background:${col};border-radius:2px;
      pointer-events:none;z-index:999;
      animation:cfall ${0.6+Math.random()*0.6}s ease forwards;
    `;
    document.body.appendChild(c);
    setTimeout(() => c.remove(), 1200);
  }
}

// ── POWER-UPS ──
function use5050() {
  if (pu5050Used || answered) return;
  pu5050Used = true;
  $('btn5050').disabled = true;

  const correct = questions[idx].jawaban_benar;
  const opts    = ['A','B','C','D'].filter(o => o !== correct);
  // Sembunyikan 2 salah secara random
  const toHide  = opts.sort(() => Math.random() - 0.5).slice(0, 2);
  toHide.forEach(o => {
    const btn = document.querySelector(`.opt-btn[data-opt="${o}"]`);
    if (btn) btn.classList.add('hidden-opt');
  });
}

function useAddTime() {
  if (puTimeUsed || answered) return;
  puTimeUsed = true;
  $('btnTime').disabled = true;
  timeLeft = Math.min(timeLeft + 10, TIMER_SEC + 10);
  updateTimerUI();
}

// ── RANK ──
function getRank(pct) {
  if (pct >= 90) return { rank:'🏆 Master!',        msg:'Luar biasa! Kamu menguasai materi ini!',         col:'#fbbf24' };
  if (pct >= 80) return { rank:'🌟 Excellent!',     msg:'Kerja bagus! Kamu memahami materi dengan baik!',  col:'#10b981' };
  if (pct >= 70) return { rank:'✅ Good Job!',       msg:'Bagus! Kamu lulus dengan nilai yang memuaskan!', col:'#3b82f6' };
  if (pct >= 60) return { rank:'📚 Keep Learning!', msg:'Hampir! Coba pelajari lagi materinya ya.',       col:'#f59e0b' };
  return           { rank:'💪 Try Again!',          msg:'Jangan menyerah! Belajar lagi dan coba lagi.',  col:'#ef4444' };
}

// ── END QUIZ ──
function endQuiz() {
  clearInterval(timerInt);

  const pct    = Math.round((benar / total) * 100);
  const lulus  = pct >= 70;
  const { rank, msg, col } = getRank(pct);
  const avgSec = avgTime.length
    ? (avgTime.reduce((a,b) => a+b, 0) / avgTime.length).toFixed(1)
    : 0;

  // Simpan ke server
  fetch('quiz_save.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: [
      'materi_id=' + materi_id,
      'skor='      + benar,
      'total='     + total,
      'poin='      + totalPoin,
      'detail='    + encodeURIComponent(JSON.stringify(detail))
    ].join('&')
  });

  // Simpan detail ke sessionStorage untuk halaman review
  sessionStorage.setItem('quiz_review', JSON.stringify({
    judul: <?= json_encode($judul_modul) ?>,
    detail: detail,
    poin: totalPoin,
    benar: benar,
    salah: salah,
    total: total,
    pct: pct
  }));

  // Render result card
  $('resultCard').innerHTML = `
    <div class="result-jenjang" style="background:${col}">${jenjang} · Kelas ${kelas}</div>

    <div class="score-ring" style="border-color:${col};color:${col}">
      <div class="score-pct">${pct}%</div>
      <div class="score-sub">Skor kamu</div>
    </div>

    <div class="rank-title">${rank}</div>
    <div class="rank-msg">${msg}</div>

    <div class="result-stats">
      <div class="rstat">
        <div class="rstat-val" style="color:#22c55e">${benar}</div>
        <div class="rstat-lbl">Benar</div>
      </div>
      <div class="rstat">
        <div class="rstat-val" style="color:#ef4444">${salah}</div>
        <div class="rstat-lbl">Salah</div>
      </div>
      <div class="rstat">
        <div class="rstat-val" style="color:#f97316">${maxStreak}</div>
        <div class="rstat-lbl">Max Streak</div>
      </div>
      <div class="rstat">
        <div class="rstat-val" style="color:#a78bfa">${avgSec}s</div>
        <div class="rstat-lbl">Rata-rata</div>
      </div>
    </div>

    <div class="result-poin">
      <div style="font-size:28px">⭐</div>
      <div>
        <div class="result-poin-num" id="animPoin">0</div>
        <div class="result-poin-lbl">Total Poin yang dikumpulkan</div>
      </div>
    </div>

    <div class="result-achievement ${lulus ? 'pass' : 'fail'}">
      <div class="ra-icon">${lulus ? '🎯' : '📖'}</div>
      <div>
        <div class="ra-title">${lulus ? 'Selamat, kamu lulus!' : 'Perlu belajar lagi'}</div>
        <div class="ra-desc">${lulus
          ? 'Kamu berhasil melewati passing grade 70%! Hasil tersimpan otomatis.'
          : 'Passing grade minimal 70%. Pelajari materi lagi dan coba ulangi kuis ya!'}</div>
      </div>
    </div>

    <div class="saved-note">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
      Hasil tersimpan otomatis
    </div>

    <div class="result-btns">
      <a href="quiz_detail.php?materi_id=${materi_id}" class="rbtn rbtn-retry">🔄 Coba Lagi</a>
      <a href="quiz_review.php" class="rbtn rbtn-review">📋 Review Jawaban</a>
      <a href="leaderboard.php?materi_id=${materi_id}" class="rbtn" style="background:rgba(250,204,21,0.15);border:1px solid rgba(250,204,21,0.3);color:#facc15;">🏆 Leaderboard</a>
    </div>
    <a href="materi.php?modul_id=${modul_id}" class="rbtn-back">← Kembali ke Materi</a>
  `;

  // Tampilkan result screen
  $('quizScreen').style.display = 'none';
  const rs = $('resultScreen');
  rs.style.display    = 'flex';
  rs.style.background = `linear-gradient(135deg,${col}22,#0f1117)`;

  // Animasi poin naik
  animateCount('animPoin', 0, totalPoin, 1200);

  // Confetti kalau lulus
  if (lulus) {
    const colors = [col, '#facc15', '#f472b6', '#a78bfa', '#34d399', '#60a5fa'];
    let html = '';
    for (let i = 0; i < 60; i++) {
      const c   = colors[i % colors.length];
      const l   = Math.random() * 100;
      const del = Math.random() * 3;
      const dur = 2 + Math.random() * 3;
      const s   = 6 + Math.random() * 8;
      html += `<div class="confetti-piece" style="left:${l}%;background:${c};width:${s}px;height:${s}px;animation-duration:${dur}s;animation-delay:${del}s"></div>`;
    }
    rs.insertAdjacentHTML('afterbegin', html);
  }
}

function animateCount(id, from, to, duration) {
  const el    = $(id);
  const start = performance.now();
  function step(now) {
    const p   = Math.min((now - start) / duration, 1);
    const val = Math.round(from + (to - from) * easeOut(p));
    el.textContent = val.toLocaleString();
    if (p < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

function easeOut(t) { return 1 - Math.pow(1 - t, 3); }

// ── START ──
loadQ();
</script>

<?php endif; ?>
</body>
</html>