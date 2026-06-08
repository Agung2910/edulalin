<?php
require_once "config.php";
require_login();

$user_id = (int)($_SESSION['user_id'] ?? 0);
$page = 'pengaturan';
$msg = '';
$msg_type = '';

function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function table_exists(mysqli $conn, string $table): bool {
    $safe = $conn->real_escape_string($table);
    $res = $conn->query("SHOW TABLES LIKE '{$safe}'");
    return $res && $res->num_rows > 0;
}

function column_exists(mysqli $conn, string $table, string $column): bool {
    $safeTable = $conn->real_escape_string($table);
    $safeCol   = $conn->real_escape_string($column);
    $res = $conn->query("SHOW COLUMNS FROM `{$safeTable}` LIKE '{$safeCol}'");
    return $res && $res->num_rows > 0;
}

function get_client_ip(): string {
    foreach (['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            return trim($ip);
        }
    }
    return '-';
}

function fmt_dt($dt): string {
    if (!$dt) return '-';
    $ts = strtotime($dt);
    if (!$ts) return '-';
    return date('d M Y, H:i', $ts);
}

function clean_social_username(string $value): string {
    $value = trim($value);
    $value = ltrim($value, '@');
    $value = preg_replace('/\s+/', '', $value);
    return $value ?: '';
}

function set_flash(string $type, string $text): void {
    $_SESSION['settings_flash'] = ['type' => $type, 'text' => $text];
}

function current_page(string $hash = ''): string {
    $path = basename($_SERVER['PHP_SELF'] ?? 'pengaturan.php');
    return $path . $hash;
}

function go_settings(string $hash = ''): void {
    header('Location: ' . current_page($hash));
    exit;
}

if (!empty($_SESSION['settings_flash'])) {
    $msg_type = $_SESSION['settings_flash']['type'];
    $msg = $_SESSION['settings_flash']['text'];
    unset($_SESSION['settings_flash']);
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$has_no_telp        = column_exists($conn, 'users', 'no_telp');
$has_instagram      = column_exists($conn, 'users', 'instagram');
$has_tiktok         = column_exists($conn, 'users', 'tiktok');
$has_mode_gelap     = column_exists($conn, 'users', 'mode_gelap');
$has_bahasa         = column_exists($conn, 'users', 'bahasa');
$has_ukuran_teks    = column_exists($conn, 'users', 'ukuran_teks');
$has_email_verified = column_exists($conn, 'users', 'email_verified_at');
$has_deleted_at     = column_exists($conn, 'users', 'deleted_at');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'profil') {
        $nama         = trim($_POST['nama'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $no_telp      = trim($_POST['no_telp'] ?? '');
        $asal_sekolah = trim($_POST['asal_sekolah'] ?? '');
        $jenjang      = trim($_POST['jenjang'] ?? '');
        $kelas        = trim($_POST['kelas'] ?? '');
        $instagram    = clean_social_username($_POST['instagram'] ?? '');
        $tiktok       = clean_social_username($_POST['tiktok'] ?? '');

        if ($nama === '' || $email === '') {
            set_flash('error', 'Nama lengkap dan email wajib diisi.');
            go_settings();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('error', 'Format email tidak valid.');
            go_settings();
        }

        // Cek email duplikat
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $existsEmail = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existsEmail) {
            set_flash('error', 'Email sudah digunakan akun lain.');
            go_settings();
        }

        $fields = ['nama=?', 'email=?', 'asal_sekolah=?', 'jenjang=?', 'kelas=?'];
        $types  = 'sssss';
        $values = [$nama, $email, $asal_sekolah, $jenjang, $kelas];

        if ($has_no_telp) {
            $fields[] = 'no_telp=?';
            $types .= 's';
            $values[] = $no_telp;
        }

        if ($has_instagram) {
            $fields[] = 'instagram=?';
            $types .= 's';
            $values[] = $instagram;
        }
        if ($has_tiktok) {
            $fields[] = 'tiktok=?';
            $types .= 's';
            $values[] = $tiktok;
        }

        $types .= 'i';
        $values[] = $user_id;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $ok = $stmt->execute();
        $stmt->close();

        set_flash($ok ? 'success' : 'error', $ok ? 'Profil berhasil disimpan.' : 'Gagal menyimpan profil.');
        go_settings('#akun');
    }

    if ($action === 'password') {
        $password_lama = $_POST['password_lama'] ?? '';
        $password_baru = $_POST['password_baru'] ?? '';
        $password_konf = $_POST['password_konfirmasi'] ?? '';

        if ($password_lama === '' || $password_baru === '' || $password_konf === '') {
            set_flash('error', 'Semua field password wajib diisi.');
            go_settings('#password');
        }

        if (!password_verify($password_lama, $user['password'] ?? '')) {
            set_flash('error', 'Password lama salah.');
            go_settings('#password');
        }

        if (strlen($password_baru) < 6) {
            set_flash('error', 'Password baru minimal 6 karakter.');
            go_settings('#password');
        }

        if ($password_baru !== $password_konf) {
            set_flash('error', 'Konfirmasi password baru tidak sama.');
            go_settings('#password');
        }

        $hash = password_hash($password_baru, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hash, $user_id);
        $ok = $stmt->execute();
        $stmt->close();

        set_flash($ok ? 'success' : 'error', $ok ? 'Password berhasil diubah.' : 'Gagal mengubah password.');
        go_settings('#password');
    }

    if ($action === 'tampilan') {
        $mode_gelap  = isset($_POST['mode_gelap']) ? 1 : 0;
        $bahasa      = trim($_POST['bahasa'] ?? 'id');
        $ukuran_teks = trim($_POST['ukuran_teks'] ?? 'normal');

        $fields = [];
        $types = '';
        $values = [];

        if ($has_mode_gelap) {
            $fields[] = 'mode_gelap=?';
            $types .= 'i';
            $values[] = $mode_gelap;
        }
        if ($has_bahasa) {
            $fields[] = 'bahasa=?';
            $types .= 's';
            $values[] = $bahasa;
        }
        if ($has_ukuran_teks) {
            $fields[] = 'ukuran_teks=?';
            $types .= 's';
            $values[] = $ukuran_teks;
        }

        $types .= 'i';
        $values[] = $user_id;

        $stmt = $conn->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id=?");
        $stmt->bind_param($types, ...$values);
        $ok = $stmt->execute();
        $stmt->close();
    }

    if ($action === 'logout_all') {
        // Kalau sistem lu belum punya tabel session/token, fallback-nya logout sesi sekarang.
        session_unset();
        session_destroy();
        header("Location: login.php?msg=logout_all");
        exit;
    }

    if ($action === 'hapus_akun') {
        $confirm = trim($_POST['konfirmasi_hapus'] ?? '');
        if ($confirm !== 'HAPUS') {
            set_flash('error', 'Ketik HAPUS untuk konfirmasi hapus akun.');
            go_settings('#privasi');
        }

        if ($has_deleted_at) {
            $stmt = $conn->prepare("UPDATE users SET deleted_at = NOW(), email = CONCAT(email, '.deleted.', id) WHERE id=?");
            $stmt->bind_param("i", $user_id);
            $ok = $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
            $stmt->bind_param("i", $user_id);
            $ok = $stmt->execute();
            $stmt->close();
        }

        if ($ok) {
            session_unset();
            session_destroy();
            header("Location: login.php?msg=account_deleted");
            exit;
        }

        set_flash('error', 'Gagal menghapus akun.');
        go_settings('#privasi');
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$login_activities = [];
if (table_exists($conn, 'login_activity')) {
    $stmt = $conn->prepare("SELECT device, ip_address, lokasi, waktu FROM login_activity WHERE user_id=? ORDER BY waktu DESC LIMIT 5");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $login_activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} elseif (table_exists($conn, 'activity_log')) {
    // Fallback kalau cuma ada activity_log
    $stmt = $conn->prepare("SELECT waktu, keterangan FROM activity_log WHERE user_id=? AND (aksi LIKE '%login%' OR keterangan LIKE '%login%') ORDER BY waktu DESC LIMIT 5");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($rows as $r) {
        $login_activities[] = [
            'device' => $_SERVER['HTTP_USER_AGENT'] ?? 'Browser tidak diketahui',
            'ip_address' => get_client_ip(),
            'lokasi' => '-',
            'waktu' => $r['waktu'] ?? null,
            'keterangan' => $r['keterangan'] ?? ''
        ];
    }
}

if (empty($login_activities)) {
    $login_activities[] = [
        'device' => $_SERVER['HTTP_USER_AGENT'] ?? 'Browser tidak diketahui',
        'ip_address' => get_client_ip(),
        'lokasi' => '-',
        'waktu' => date('Y-m-d H:i:s'),
        'keterangan' => 'Sesi aktif saat ini'
    ];
}

$nama = $user['nama'] ?? '';
$email = $user['email'] ?? '';
$no_telp = $has_no_telp ? ($user['no_telp'] ?? '') : '';
$asal_sekolah = $user['asal_sekolah'] ?? '';
$jenjang = $user['jenjang'] ?? '';
$kelas = $user['kelas'] ?? '';
$instagram = $has_instagram ? ($user['instagram'] ?? '') : '';
$tiktok = $has_tiktok ? ($user['tiktok'] ?? '') : '';
$mode_gelap = $has_mode_gelap ? (int)($user['mode_gelap'] ?? 0) : 0;
$bahasa = $has_bahasa ? ($user['bahasa'] ?? 'id') : 'id';
$ukuran_teks = $has_ukuran_teks ? ($user['ukuran_teks'] ?? 'normal') : 'normal';
?>
<?php include 'header_common.php'; ?>

<title>Pengaturan Akun</title>
<link rel="icon" type="image/png" href="assets/img/logo.png">
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

<style>
#setting-root {
    font-family: 'Plus Jakarta Sans', sans-serif !important;
    background: #f1f5f9 !important;
    min-height: 80vh;
    padding: 28px 32px !important;
    color: #0f172a;
    box-sizing: border-box;
}
#setting-root *, #setting-root *::before, #setting-root *::after { box-sizing: border-box !important; }
#setting-root .st-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:22px; flex-wrap:wrap; }
#setting-root .st-head h1 { margin:0; font-size:24px; font-weight:800; }
#setting-root .st-head p { margin:5px 0 0; color:#64748b; font-size:13px; }
#setting-root .st-grid { display:grid; grid-template-columns: 260px 1fr; gap:20px; align-items:start; }
#setting-root .st-nav, #setting-root .st-card { background:#fff; border-radius:16px; box-shadow:0 2px 12px rgba(15,23,42,.07); }
#setting-root .st-nav { padding:12px; position:sticky; top:18px; }
#setting-root .st-nav a { display:flex; align-items:center; gap:10px; padding:12px 13px; border-radius:12px; color:#475569; text-decoration:none; font-size:13px; font-weight:700; }
#setting-root .st-nav a:hover { background:#f1f5f9; color:#2563eb; }
#setting-root .st-nav .material-icons-round { font-size:18px; }
#setting-root .st-main { display:flex; flex-direction:column; gap:18px; }
#setting-root .st-card { padding:22px; }
#setting-root .st-card-hd { display:flex; justify-content:space-between; align-items:center; gap:12px; border-bottom:1px solid #e2e8f0; padding-bottom:15px; margin-bottom:18px; }
#setting-root .st-card-title { display:flex; align-items:center; gap:10px; font-size:16px; font-weight:800; }
#setting-root .st-card-title .material-icons-round { color:#2563eb; }
#setting-root .st-muted { color:#64748b; font-size:12px; line-height:1.55; }
#setting-root .st-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
#setting-root .st-field { display:flex; flex-direction:column; gap:7px; }
#setting-root label { font-size:12px; color:#334155; font-weight:700; }
#setting-root input, #setting-root select { width:100%; border:1.5px solid #e2e8f0; border-radius:12px; padding:11px 12px; font-family:inherit; font-size:13px; outline:none; background:#fff; color:#0f172a; }
#setting-root input:focus, #setting-root select:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12); }
#setting-root .st-full { grid-column: 1 / -1; }
#setting-root .st-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:18px; flex-wrap:wrap; }
#setting-root .st-btn { border:none; border-radius:12px; padding:11px 16px; font-family:inherit; font-size:13px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:7px; text-decoration:none; }
#setting-root .st-btn-primary { background:#2563eb; color:#fff; }
#setting-root .st-btn-primary:hover { background:#1d4ed8; }
#setting-root .st-btn-soft { background:#eff6ff; color:#2563eb; }
#setting-root .st-btn-danger { background:#dc2626; color:#fff; }
#setting-root .st-btn-danger:hover { background:#b91c1c; }
#setting-root .st-alert { padding:13px 14px; border-radius:13px; margin-bottom:18px; font-size:13px; font-weight:700; display:flex; align-items:center; gap:8px; }
#setting-root .st-alert.success { background:#dcfce7; color:#15803d; }
#setting-root .st-alert.error { background:#fee2e2; color:#b91c1c; }
#setting-root .st-switch-row { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:14px; border:1.5px solid #e2e8f0; border-radius:14px; }
#setting-root .st-switch-title { font-size:13px; font-weight:800; }
#setting-root .st-check { width:auto !important; transform:scale(1.2); }
#setting-root .st-login-list { display:flex; flex-direction:column; gap:11px; }
#setting-root .st-login-item { display:grid; grid-template-columns: 38px 1fr auto; gap:12px; align-items:center; padding:14px; border:1.5px solid #e2e8f0; border-radius:14px; }
#setting-root .st-login-ico { width:38px; height:38px; border-radius:12px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; }
#setting-root .st-login-ico .material-icons-round { font-size:20px; }
#setting-root .st-login-title { font-size:13px; font-weight:800; word-break:break-word; }
#setting-root .st-login-meta { font-size:12px; color:#64748b; margin-top:3px; }
#setting-root .st-badge { display:inline-flex; align-items:center; border-radius:999px; padding:5px 9px; font-size:11px; font-weight:800; background:#f1f5f9; color:#475569; white-space:nowrap; }
#setting-root .st-danger-box { border:1.5px solid #fecaca; background:#fff1f2; border-radius:14px; padding:15px; }
#setting-root .st-danger-box h4 { margin:0 0 5px; color:#b91c1c; font-size:14px; }
#setting-root .st-danger-box p { margin:0 0 14px; color:#7f1d1d; font-size:12px; line-height:1.55; }
#setting-root .st-note { margin-top:10px; font-size:11px; color:#94a3b8; }

#setting-root.is-dark { background:#0f172a !important; color:#e5e7eb; }
#setting-root.is-dark .st-nav,
#setting-root.is-dark .st-card { background:#111827; box-shadow:0 2px 14px rgba(0,0,0,.25); }
#setting-root.is-dark .st-head p,
#setting-root.is-dark .st-muted,
#setting-root.is-dark .st-login-meta,
#setting-root.is-dark .st-note { color:#94a3b8; }
#setting-root.is-dark .st-card-hd { border-bottom-color:#334155; }
#setting-root.is-dark input,
#setting-root.is-dark select { background:#0b1220; border-color:#334155; color:#e5e7eb; }
#setting-root.is-dark input:disabled,
#setting-root.is-dark select:disabled { opacity:.55; }
#setting-root.is-dark label,
#setting-root.is-dark .st-switch-title,
#setting-root.is-dark .st-login-title { color:#e5e7eb; }
#setting-root.is-dark .st-nav a { color:#cbd5e1; }
#setting-root.is-dark .st-nav a:hover { background:#1e293b; color:#93c5fd; }
#setting-root.is-dark .st-switch-row,
#setting-root.is-dark .st-login-item { border-color:#334155; background:#0b1220; }
#setting-root.is-dark .st-badge { background:#1e293b; color:#cbd5e1; }
#setting-root.is-dark .st-danger-box { background:#2a1114; border-color:#7f1d1d; }
#setting-root.is-dark .st-danger-box h4 { color:#fecaca; }
#setting-root.is-dark .st-danger-box p { color:#fecaca; }
#setting-root.ukuran-kecil { font-size: 13px; }
#setting-root.ukuran-besar { font-size: 16px; }
#setting-root.ukuran-besar input,
#setting-root.ukuran-besar select,
#setting-root.ukuran-besar .st-btn { font-size:14px; }

@media (max-width: 900px) {
    #setting-root { padding:18px !important; }
    #setting-root .st-grid { grid-template-columns:1fr; }
    #setting-root .st-nav { position:relative; top:0; display:grid; grid-template-columns:1fr 1fr; }
}
@media (max-width: 640px) {
    #setting-root .st-form-grid { grid-template-columns:1fr; }
    #setting-root .st-nav { grid-template-columns:1fr; }
    #setting-root .st-login-item { grid-template-columns:38px 1fr; }
    #setting-root .st-login-item .st-badge { grid-column:2; width:fit-content; }
}
</style>

<div id="setting-root" class="<?= $mode_gelap ? 'is-dark' : '' ?> ukuran-<?= e($ukuran_teks) ?>">
    <div class="st-head">
        <div>
            <h1>Pengaturan</h1>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="st-alert <?= e($msg_type) ?>">
            <span class="material-icons-round"><?= $msg_type === 'success' ? 'check_circle' : 'error' ?></span>
            <?= e($msg) ?>
        </div>
    <?php endif; ?>

    <div class="st-grid">
        <aside class="st-nav">
            <a href="#akun"><span class="material-icons-round">person</span> Pengaturan Akun</a>
            <a href="#password"><span class="material-icons-round">lock</span> Ubah Password</a>
            <a href="#privasi"><span class="material-icons-round">security</span> Privasi & Keamanan</a>
        </aside>

        <main class="st-main">
            <!-- PENGATURAN AKUN -->
            <section class="st-card" id="akun">
                <div class="st-card-hd">
                    <div>
                        <div class="st-card-title"><span class="material-icons-round">account_circle</span> Informasi Profil</div>
                    </div>
                </div>

                <form method="post" action="<?= e(current_page()) ?>">
                    <input type="hidden" name="action" value="profil">
                    <div class="st-form-grid">
                        <div class="st-field">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama" value="<?= e($nama) ?>" required>
                        </div>
                        <div class="st-field">
                            <label>Email</label>
                            <input type="email" name="email" value="<?= e($email) ?>" required>
                        </div>
                        <div class="st-field">
                            <label>No. Telepon</label>
                            <input type="text" name="no_telp" value="<?= e($no_telp) ?>" <?= $has_no_telp ? '' : 'disabled' ?> placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="st-field">
                            <label>Asal Sekolah</label>
                            <input type="text" name="asal_sekolah" value="<?= e($asal_sekolah) ?>">
                        </div>
                        <div class="st-field">
                            <label>Jenjang Pendidikan</label>
                            <select name="jenjang">
                                <option value="">Pilih jenjang</option>
                                <?php foreach (['SD','SMP','SMA','SMK'] as $j): ?>
                                    <option value="<?= $j ?>" <?= strtoupper($jenjang) === $j ? 'selected' : '' ?>><?= $j ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="st-field">
                            <label>Kelas</label>
                            <select name="kelas">
                                <option value="">Pilih kelas</option>
                                <?php for ($i=1; $i<=12; $i++): ?>
                                    <option value="<?= $i ?>" <?= (string)$kelas === (string)$i ? 'selected' : '' ?>>Kelas <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="st-field">
                            <label>Instagram</label>
                            <input type="text" name="instagram" value="<?= e($instagram) ?>" <?= $has_instagram ? '' : 'disabled' ?> placeholder="username">
                            <?php if (!$has_instagram): ?><div class="st-note">Tambahkan kolom <b>instagram</b> di tabel users kalau mau field ini aktif.</div><?php endif; ?>
                        </div>
                        <div class="st-field">
                            <label>TikTok</label>
                            <input type="text" name="tiktok" value="<?= e($tiktok) ?>" <?= $has_tiktok ? '' : 'disabled' ?> placeholder="username">
                            <?php if (!$has_tiktok): ?><div class="st-note">Tambahkan kolom <b>tiktok</b> di tabel users kalau mau field ini aktif.</div><?php endif; ?>
                        </div>
                    </div>
                    <div class="st-actions">
                        <button type="submit" class="st-btn st-btn-primary"><span class="material-icons-round">save</span> Simpan Profil</button>
                    </div>
                </form>
            </section>

            <section class="st-card" id="password">
                <div class="st-card-hd">
                    <div>
                        <div class="st-card-title"><span class="material-icons-round">lock_reset</span> Ubah Password</div>
                    </div>
                </div>

                <form method="post" action="<?= e(current_page()) ?>">
                    <input type="hidden" name="action" value="password">
                    <div class="st-form-grid">
                        <div class="st-field st-full">
                            <label>Password Lama</label>
                            <input type="password" name="password_lama" autocomplete="current-password" required>
                        </div>
                        <div class="st-field">
                            <label>Password Baru</label>
                            <input type="password" name="password_baru" autocomplete="new-password" required>
                        </div>
                        <div class="st-field">
                            <label>Konfirmasi Password Baru</label>
                            <input type="password" name="password_konfirmasi" autocomplete="new-password" required>
                        </div>
                    </div>
                    <div class="st-actions">
                        <button type="submit" class="st-btn st-btn-primary"><span class="material-icons-round">key</span> Ubah Password</button>
                    </div>
                </form>
            </section>

            <section class="st-card" id="privasi">
                <div class="st-card-hd">
                    <div>
                        <div class="st-card-title"><span class="material-icons-round">shield</span> Pengaturan Privasi dan Keamanan</div>
                    </div>
                </div>

                <h3 style="font-size:14px;margin:0 0 12px;font-weight:800">Aktivitas Login</h3>
                <div class="st-login-list">
                    <?php foreach ($login_activities as $act): ?>
                    <div class="st-login-item">
                        <div class="st-login-ico"><span class="material-icons-round">devices</span></div>
                        <div>
                            <div class="st-login-title"><?= e($act['device'] ?? 'Device tidak diketahui') ?></div>
                            <div class="st-login-meta">
                                Waktu login terakhir: <?= e(fmt_dt($act['waktu'] ?? null)) ?><br>
                                Lokasi login: <?= e($act['lokasi'] ?? '-') ?> · IP: <?= e($act['ip_address'] ?? get_client_ip()) ?>
                            </div>
                        </div>
                        <span class="st-badge">Aktif</span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="height:18px"></div>

                <div class="st-danger-box">
                    <h4>Logout Semua Perangkat</h4>
                    <form method="post" onsubmit="return confirm('Yakin ingin logout semua perangkat?')">
                        <input type="hidden" name="action" value="logout_all">
                        <button type="submit" class="st-btn st-btn-soft"><span class="material-icons-round">logout</span> Logout Semua Perangkat</button>
                    </form>
                </div>

                <div style="height:14px"></div>

                <div class="st-danger-box">
                    <h4>Hapus Akun</h4>
                    <form method="post" onsubmit="return confirm('Akun akan dihapus. Lanjutkan?')">
                        <input type="hidden" name="action" value="hapus_akun">
                        <div class="st-field" style="max-width:320px;margin-bottom:12px">
                            <label>Ketik HAPUS untuk konfirmasi</label>
                            <input type="text" name="konfirmasi_hapus" placeholder="HAPUS">
                        </div>
                        <button type="submit" class="st-btn st-btn-danger"><span class="material-icons-round">delete_forever</span> Hapus Akun</button>
                    </form>
                </div>
            </section>
        </main>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('setting-root');
    const darkToggle = document.querySelector('input[name="mode_gelap"]');
    const textSize = document.querySelector('select[name="ukuran_teks"]');

    if (darkToggle && root) {
        darkToggle.addEventListener('change', function () {
            root.classList.toggle('is-dark', this.checked);
        });
    }

    if (textSize && root) {
        textSize.addEventListener('change', function () {
            root.classList.remove('ukuran-kecil', 'ukuran-normal', 'ukuran-besar');
            root.classList.add('ukuran-' + this.value);
        });
    }
});
</script>

<?php include 'footer_common.php'; ?>
