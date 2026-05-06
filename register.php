<?php
require_once "config.php";

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$msg = "";
$msg_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role_daftar   = sanitize_input($_POST['role_daftar'] ?? 'siswa');
    $nama          = sanitize_input($_POST['nama'] ?? '');
    $email         = sanitize_input($_POST['email'] ?? '');
    $pass1         = $_POST['password']  ?? '';
    $pass2         = $_POST['password2'] ?? '';
    $asal_sekolah  = sanitize_input($_POST['asal_sekolah'] ?? '');
    $no_telp       = sanitize_input($_POST['no_telp'] ?? '');
    $agree_terms   = isset($_POST['agree_terms']);

    // Khusus siswa
    $tempat_lahir  = sanitize_input($_POST['tempat_lahir'] ?? '');
    $tanggal_lahir = sanitize_input($_POST['tanggal_lahir'] ?? '');
    $kelas         = sanitize_input($_POST['kelas'] ?? '');
    $jenjang       = sanitize_input($_POST['jenjang'] ?? '');
    $instagram     = sanitize_input($_POST['instagram'] ?? '');
    $tiktok        = sanitize_input($_POST['tiktok'] ?? '');

    $errors = [];

    if (!$agree_terms) $errors[] = "Anda harus menyetujui Syarat & Ketentuan";
    if (empty($nama) || strlen($nama) < 3) $errors[] = "Nama lengkap minimal 3 karakter";
    if (empty($asal_sekolah)) $errors[] = "Asal sekolah wajib diisi";
    if (!preg_match('/^[0-9]{10,15}$/', $no_telp)) $errors[] = "No telepon harus berupa angka (10–15 digit)";

    if ($role_daftar === 'siswa') {
        if (empty($kelas)) $errors[] = "Kelas wajib dipilih";
        if (empty($jenjang)) $errors[] = "Jenjang wajib dipilih";
        if (empty($tempat_lahir)) $errors[] = "Tempat lahir wajib diisi";
        if (empty($tanggal_lahir)) $errors[] = "Tanggal lahir wajib diisi";
        elseif (!strtotime($tanggal_lahir)) $errors[] = "Format tanggal lahir tidak valid";
        if (in_array($jenjang, ['SMP', 'SMA'])) {
            if (empty($instagram)) $errors[] = "Instagram wajib diisi untuk jenjang SMP & SMA";
            elseif (!preg_match('/^https?:\/\/(www\.)?instagram\.com\/[A-Za-z0-9._]+$/', $instagram)) $errors[] = "Format Instagram harus https://instagram.com/username";
            if (empty($tiktok)) $errors[] = "TikTok wajib diisi untuk jenjang SMP & SMA";
            elseif (!preg_match('/^https?:\/\/(www\.)?tiktok\.com\/@?[A-Za-z0-9._]+$/', $tiktok)) $errors[] = "Format TikTok harus https://tiktok.com/@username";
        }
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email tidak valid";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) $errors[] = "Email sudah terdaftar";
        $check->close();
    }

    if (strlen($pass1) < 8) $errors[] = "Password minimal 8 karakter";
    if ($pass1 !== $pass2) $errors[] = "Konfirmasi password tidak sama";

    if (empty($errors)) {
        $hash    = password_hash($pass1, PASSWORD_DEFAULT);
        $role_db = ($role_daftar === 'guru') ? 'guru' : 'user';

        $_SESSION['pending_registration'] = [
            'nama'          => $nama,
            'email'         => $email,
            'password'      => $hash,
            'role'          => $role_db,
            'asal_sekolah'  => $asal_sekolah,
            'no_telp'       => $no_telp,
            'tempat_lahir'  => ($role_daftar === 'siswa') ? $tempat_lahir  : '',
            'tanggal_lahir' => ($role_daftar === 'siswa') ? $tanggal_lahir : '',
            'kelas'         => ($role_daftar === 'siswa') ? $kelas         : '',
            'jenjang'       => ($role_daftar === 'siswa') ? $jenjang       : '',
            'instagram'     => ($role_daftar === 'siswa') ? $instagram     : '',
            'tiktok'        => ($role_daftar === 'siswa') ? $tiktok        : '',
        ];

        $otp = generateOTP();
        if (storeOTP($conn, $email, $otp, 'registration')) {
            if (sendOTPEmail($email, $otp, 'registration')) {
                header("Location: verify_otp.php?email=" . urlencode($email) . "&type=registration");
                exit;
            } else { $msg = "Gagal mengirim email OTP."; $msg_type = "error"; }
        } else { $msg = "Terjadi kesalahan."; $msg_type = "error"; }
    } else {
        $msg = implode("<br>", $errors);
        $msg_type = "error";
    }
}

$posted_role = $_POST['role_daftar'] ?? 'siswa';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar - Edu Lalin</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
        }

        /* KIRI */
        .auth-left {
            flex: 1;
            background: #dbeafe;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow: hidden;
        }

        .auth-left-bg {
            position: absolute;
            inset: 0;
            background-image: url("assets/img/thumbnail-smp.png");
            background-size: cover;
            background-position: center;
        }

        .auth-left-content {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 400px;
        }

        .auth-left-logo { height: 52px; margin-bottom: 28px; }
        .auth-left-title { font-size: 26px; font-weight: 800; color: #1e3a5f; line-height: 1.3; margin-bottom: 10px; }
        .auth-left-sub   { font-size: 14px; color: #3b5a80; line-height: 1.6; }
        .auth-left-img   { margin-top: 36px; width: 240px; max-width: 100%; }

        /* KANAN */
        .auth-right {
            width: 560px;
            flex-shrink: 0;
            background: #ffffff;
            padding: 48px 48px 60px;
            box-shadow: -4px 0 32px rgba(0,0,0,0.06);
            overflow-y: auto;
        }

        .auth-logo-sm    { height: 38px; margin-bottom: 28px; }
        .auth-heading    { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
        .auth-subheading { font-size: 14px; color: #64748b; margin-bottom: 24px; }

        /* alert */
        .alert { padding: 12px 14px; border-radius: 10px; font-size: 13px; margin-bottom: 20px; line-height: 1.6; }
        .alert-error   { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
        .alert-success { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }

        /* ROLE TOGGLE */
        .role-toggle { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 28px; }

        .role-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            background: #f8fafc;
            transition: border-color 0.2s, background 0.2s, color 0.2s;
            user-select: none;
        }

        .role-card:hover { border-color: #93c5fd; background: #eff6ff; }

        .role-card.active { border-color: #005eb8; background: #eff6ff; color: #005eb8; }

        .role-dot {
            width: 18px; height: 18px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            transition: border-color 0.2s;
        }

        .role-card.active .role-dot { border-color: #005eb8; }

        .role-card.active .role-dot::after {
            content: '';
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #005eb8;
            display: block;
        }

        .role-icon { font-size: 18px; }

        /* form */
        .section-label {
            font-size: 11px; font-weight: 700; letter-spacing: 0.08em;
            text-transform: uppercase; color: #94a3b8;
            margin-bottom: 14px; padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
        }

        .form-section { margin-bottom: 24px; }
        .form-row     { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group   { margin-bottom: 16px; }

        .form-group label {
            display: block; font-size: 13px; font-weight: 600;
            color: #374151; margin-bottom: 6px;
        }

        .req { color: #ef4444; }

        .input-wrap { position: relative; }

        .input-wrap svg.input-icon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            width: 15px; height: 15px;
            stroke: #94a3b8; pointer-events: none;
        }

        .input-wrap input,
        .input-wrap select {
            width: 100%;
            padding: 10px 12px 10px 36px;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            font-size: 14px; color: #0f172a;
            background: #f8fafc;
            transition: border-color 0.2s, background 0.2s;
            appearance: none;
        }

        .input-wrap input[type="date"] { padding-left: 12px; }

        .input-wrap input:focus,
        .input-wrap select:focus {
            outline: none; border-color: #005eb8; background: #ffffff;
        }

        .input-wrap input::placeholder { color: #94a3b8; }

        .input-hint { font-size: 11px; color: #94a3b8; margin-top: 4px; }

        .toggle-pw {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            cursor: pointer; color: #94a3b8;
            background: none; border: none; padding: 0;
            display: flex; align-items: center;
        }
        .toggle-pw svg { width: 17px; height: 17px; stroke: currentColor; }
        .toggle-pw:hover { color: #475569; }

        /* agree */
        .agree-group { margin-bottom: 24px; }

        .agree-label {
            display: flex; align-items: flex-start; gap: 10px;
            font-size: 12px; color: #475569; line-height: 1.5;
            cursor: pointer; margin-bottom: 10px;
        }

        .agree-label input[type="checkbox"] {
            width: 15px; height: 15px; flex-shrink: 0;
            margin-top: 1px; accent-color: #005eb8; cursor: pointer;
        }

        .agree-label a { color: #005eb8; font-weight: 600; text-decoration: none; }
        .agree-label a:hover { text-decoration: underline; }

        /* tombol */
        .btn-submit {
            width: 100%; padding: 13px;
            background: #005eb8; color: #ffffff;
            border: none; border-radius: 10px;
            font-size: 15px; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background 0.2s, transform 0.1s;
        }
        .btn-submit:hover { background: #00418a; transform: translateY(-1px); }
        .btn-submit svg { width: 18px; height: 18px; stroke: currentColor; }

        .auth-bottom { margin-top: 20px; text-align: center; font-size: 13px; color: #64748b; }
        .link-blue { color: #005eb8; font-weight: 600; text-decoration: none; }
        .link-blue:hover { text-decoration: underline; }

        #sosmed-wrapper { display: none; }

        @media (max-width: 900px) {
            .auth-left { display: none; }
            .auth-right { width: 100%; padding: 32px 20px 48px; box-shadow: none; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- KIRI -->
<div class="auth-left">
    <div class="auth-left-bg"></div>
    <div class="auth-left-content">
    </div>
</div>

<!-- KANAN -->
<div class="auth-right">
    <div class="auth-heading">Daftar Akun Baru</div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="on">
        <input type="hidden" name="role_daftar" id="role_daftar" value="<?= htmlspecialchars($posted_role) ?>">

        <!-- PILIH ROLE -->
        <div class="section-label">Daftar Sebagai</div>
        <div class="role-toggle">
            <div class="role-card <?= $posted_role === 'siswa' ? 'active' : '' ?>" data-role="siswa">
                <span>Siswa</span>
            </div>
            <div class="role-card <?= $posted_role === 'guru' ? 'active' : '' ?>" data-role="guru">
                <span>Guru</span>
            </div>
        </div>

        <!-- DATA AKUN (semua role) -->
        <div class="form-section">
            <div class="section-label">Data Akun</div>

            <div class="form-group">
                <label>Nama Lengkap <span class="req">*</span></label>
                <div class="input-wrap">
                    <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                    <input type="text" name="nama" placeholder="Nama lengkap Anda" required
                           value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Email <span class="req">*</span></label>
                <div class="input-wrap">
                    <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                    <input type="email" name="email" placeholder="nama@email.com" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password <span class="req">*</span></label>
                    <div class="input-wrap">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                        <input type="password" name="password" id="pw1" placeholder="Min. 8 karakter" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('pw1',this)">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password <span class="req">*</span></label>
                    <div class="input-wrap">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                        <input type="password" name="password2" id="pw2" placeholder="Ulangi password" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('pw2',this)">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ASAL SEKOLAH + NO TELP (semua role) -->
        <div class="form-section">
            <div class="section-label">Data Sekolah</div>

            <div class="form-group">
                <label>Asal Sekolah <span class="req">*</span></label>
                <div class="input-wrap">
                    <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
                    <input type="text" name="asal_sekolah" placeholder="Nama sekolah" required
                           value="<?= htmlspecialchars($_POST['asal_sekolah'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>No Telepon <span class="req">*</span></label>
                <div class="input-wrap">
                    <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                    <input type="text" name="no_telp" placeholder="08xxxxxxxxxx" required
                           value="<?= htmlspecialchars($_POST['no_telp'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- KHUSUS SISWA -->
        <div id="siswa-only">

            <div class="form-section">
                <div class="section-label">Data Pribadi</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tempat Lahir <span class="req">*</span></label>
                        <div class="input-wrap">
                            <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            <input type="text" name="tempat_lahir" id="tempat_lahir" placeholder="Kota kelahiran"
                                   value="<?= htmlspecialchars($_POST['tempat_lahir'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Lahir <span class="req">*</span></label>
                        <div class="input-wrap">
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                                   value="<?= htmlspecialchars($_POST['tanggal_lahir'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-label">Data Kelas</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Jenjang <span class="req">*</span></label>
                        <div class="input-wrap">
                            <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/></svg>
                            <select name="jenjang" id="jenjang_select">
                                <option value="">-- Pilih Jenjang --</option>
                                <option value="SD"  <?= (($_POST['jenjang'] ?? '') == 'SD')  ? 'selected' : '' ?>>SD</option>
                                <option value="SMP" <?= (($_POST['jenjang'] ?? '') == 'SMP') ? 'selected' : '' ?>>SMP</option>
                                <option value="SMA" <?= (($_POST['jenjang'] ?? '') == 'SMA') ? 'selected' : '' ?>>SMA</option>
                                <option value="SMK" <?= (($_POST['jenjang'] ?? '') == 'SMK') ? 'selected' : '' ?>>SMK</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Kelas <span class="req">*</span></label>
                        <div class="input-wrap">
                            <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/></svg>
                            <select name="kelas" id="kelas_select">
                                <option value="">-- Pilih Kelas --</option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>" <?= (($_POST['kelas'] ?? '') == $i) ? 'selected' : '' ?>>Kelas <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Sosmed SMP/SMA -->
                <div id="sosmed-wrapper">
                    <div class="form-group">
                        <label>Instagram <span class="req">*</span></label>
                        <div class="input-wrap">
                            <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                            <input type="text" name="instagram" id="instagram" placeholder="https://instagram.com/username"
                                   value="<?= htmlspecialchars($_POST['instagram'] ?? '') ?>">
                        </div>
                        <div class="input-hint">Wajib untuk jenjang SMP & SMA</div>
                    </div>
                    <div class="form-group">
                        <label>TikTok <span class="req">*</span></label>
                        <div class="input-wrap">
                            <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19c-4.418 0-8-1.79-8-4s3.582-4 8-4 8 1.79 8 4-3.582 4-8 4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 7s-2 0-2-3H15v12.5C15 17.88 13.21 19 11 19s-4-1.12-4-2.5S8.79 14 11 14"/></svg>
                            <input type="text" name="tiktok" id="tiktok" placeholder="https://tiktok.com/@username"
                                   value="<?= htmlspecialchars($_POST['tiktok'] ?? '') ?>">
                        </div>
                        <div class="input-hint">Wajib untuk jenjang SMP & SMA</div>
                    </div>
                </div>
            </div>

        </div><!-- end siswa-only -->

        <!-- PERSETUJUAN -->
        <div class="agree-group">
            <label class="agree-label" id="agree-ortu-wrap">
                <input type="checkbox" name="agree_orang_tua" id="agree_orang_tua">
                <span>Saya menyatakan telah mendapat izin dari orang tua/wali untuk menggunakan edulalin.com</span>
            </label>
            <label class="agree-label">
                <input type="checkbox" name="agree_terms" required>
                <span>Saya menyetujui pengumpulan dan penggunaan data pribadi sesuai <a href="syarat-ketentuan.php">Syarat & Ketentuan</a> serta <a href="kebijakan-privasi.php">Kebijakan Privasi</a> Edulalin.com</span>
            </label>
        </div>

        <button type="submit" class="btn-submit">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            Daftar Sekarang
        </button>
    </form>

    <div class="auth-bottom">
        Sudah punya akun? <a href="login.php" class="link-blue">Masuk di sini</a>
    </div>
</div>

<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.style.opacity = input.type === 'text' ? '0.5' : '1';
}

const roleHidden    = document.getElementById('role_daftar');
const roleCards     = document.querySelectorAll('.role-card');
const siswaOnly     = document.getElementById('siswa-only');
const agreeOrtuWrap = document.getElementById('agree-ortu-wrap');
const agreeOrtu     = document.getElementById('agree_orang_tua');
const jenjangSelect = document.getElementById('jenjang_select');
const sosmedWrapper = document.getElementById('sosmed-wrapper');
const igInput       = document.getElementById('instagram');
const tiktokInput   = document.getElementById('tiktok');
const tempat        = document.getElementById('tempat_lahir');
const tanggal       = document.getElementById('tanggal_lahir');
const kelasSelect   = document.getElementById('kelas_select');

function toggleSosmed() {
    const v    = jenjangSelect.value;
    const show = v === 'SMP' || v === 'SMA';
    sosmedWrapper.style.display = show ? 'block' : 'none';
    igInput.required    = show;
    tiktokInput.required = show;
    if (!show) { igInput.value = ''; tiktokInput.value = ''; }
}

function applyRole(role) {
    roleHidden.value = role;
    roleCards.forEach(c => c.classList.toggle('active', c.dataset.role === role));

    if (role === 'siswa') {
        siswaOnly.style.display      = 'block';
        agreeOrtuWrap.style.display  = 'flex';
        agreeOrtu.required           = true;
        tempat.required              = true;
        tanggal.required             = true;
        jenjangSelect.required       = true;
        kelasSelect.required         = true;
        toggleSosmed();
    } else {
        siswaOnly.style.display      = 'none';
        agreeOrtuWrap.style.display  = 'none';
        agreeOrtu.required           = false;
        tempat.required              = false;
        tanggal.required             = false;
        jenjangSelect.required       = false;
        kelasSelect.required         = false;
        igInput.required             = false;
        tiktokInput.required         = false;
    }
}

roleCards.forEach(card => card.addEventListener('click', () => applyRole(card.dataset.role)));
jenjangSelect.addEventListener('change', toggleSosmed);

// init
applyRole('<?= $posted_role ?>');
function toggleSosmed() {
    if (!jenjangSelect) return;

    const v    = jenjangSelect.value;
    const show = v === 'SMP' || v === 'SMA';

    sosmedWrapper.style.display = show ? 'block' : 'none';
    igInput.required    = show;
    tiktokInput.required = show;

    if (!show) {
        igInput.value = '';
        tiktokInput.value = '';
    }
}
</script>
</body>
</html>