<?php
$_adm_current = basename($_SERVER['PHP_SELF']);
$_adm_role    = $_SESSION['role'] ?? 'admin';

$_adm_svg = [
  'dashboard' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
  'modul'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
  'quiz'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
  'users'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
  'deleted'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>',
  'laporan'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
  'settings'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
];

$_adm_nav = [
  ['key'=>'dashboard','label'=>'Dashboard',          'href'=>'index.php'],
  ['key'=>'modul',    'label'=>'Kelola Modul',       'href'=>'modul.php'],
  ['key'=>'quiz',     'label'=>'Kelola Soal',        'href'=>'quiz.php'],
  ['key'=>'users',    'label'=>'Kelola Pengguna',    'href'=>'users.php'],
];
if ($_adm_role === 'admin') {
  $_adm_nav[] = ['key'=>'deleted','label'=>'Pengguna Terhapus','href'=>'deleted_users.php'];
}
$_adm_nav[] = ['key'=>'laporan','label'=>'Laporan & Statistik','href'=>'laporan.php'];

// Determine active key from filename
$_adm_active_map = [
  'index.php'        => 'dashboard',
  'modul.php'        => 'modul',
  'modul_edit.php'   => 'modul',
  'quiz.php'         => 'quiz',
  'users.php'        => 'users',
  'deleted_users.php'=> 'deleted',
  'laporan.php'      => 'laporan',
  'settings.php'     => 'settings',
];
$_adm_active = $_adm_active_map[$_adm_current] ?? '';
?>
<aside class="adm-sb">
  <div class="adm-sb-logo">
    <div class="adm-sb-logo-img">
      <img src="../assets/img/logo-jr.png" alt="Logo"
           onerror="this.style.display='none';this.parentNode.innerHTML='<span>E</span>'">
    </div>
    <div>
      <div class="adm-sb-title">Edu Lalin</div>
      <div class="adm-sb-role"><?= strtoupper(htmlspecialchars($_adm_role)) ?></div>
    </div>
  </div>

  <nav class="adm-sb-nav">
    <?php foreach ($_adm_nav as $n): ?>
    <a href="<?= $n['href'] ?>" class="<?= $_adm_active === $n['key'] ? 'active' : '' ?>">
      <?= $_adm_svg[$n['key']] ?>
      <?= htmlspecialchars($n['label']) ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <?php if ($_adm_role === 'admin'): ?>
  <div class="adm-sb-bot">
    <a href="settings.php" class="<?= $_adm_active === 'settings' ? 'active' : '' ?>">
      <?= $_adm_svg['settings'] ?>
      Pengaturan
    </a>
  </div>
  <?php endif; ?>
</aside>