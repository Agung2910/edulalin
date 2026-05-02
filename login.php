<?php
require_once "config.php";

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$msg = "";
$msg_type = "";

if (isset($_SESSION['success_message'])) {
    $msg = $_SESSION['success_message'];
    $msg_type = "success";
    unset($_SESSION['success_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!$email || !$pass) {
        $msg = "Email dan password wajib diisi.";
        $msg_type = "error";
    } else {
        $stmt = $conn->prepare("
            SELECT id, nama, password, role,
                   deleted_at, archived_reason, is_verified
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($user = $res->fetch_assoc()) {
            if (!password_verify($pass, $user['password'])) {
                $msg = "Email atau password salah.";
                $msg_type = "error";
            } elseif (!$user['is_verified']) {
                $msg = "Akun belum diverifikasi. Silakan cek email.";
                $msg_type = "error";
            } elseif ($user['deleted_at'] !== null) {
                if ($user['archived_reason'] === 'inactive_1_year') {
                    $_SESSION['reactivate_user'] = $user['id'];
                    header("Location: reactivate.php");
                    exit;
                } else {
                    $msg = "Akun Anda dinonaktifkan oleh admin.";
                    $msg_type = "error";
                }
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama']    = $user['nama'];
                $_SESSION['role']    = $user['role'];
                $_SESSION['email']   = $email;

                $upd = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $upd->bind_param("i", $user['id']);
                $upd->execute();
                $upd->close();

                if ($user['role'] === 'admin') {
                    header("Location: admin/index.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            }
        } else {
            $msg = "Email atau password salah.";
            $msg_type = "error";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Edu Lalin</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
        }

        /* ── KIRI ── */
        .auth-left {
            flex: 1;
            background: #dbeafe;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            position: relative;
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
            max-width: 420px;
        }

        .auth-left-logo {
            height: 56px;
            margin-bottom: 32px;
        }

        .auth-left-title {
            font-size: 28px;
            font-weight: 800;
            color: #1e3a5f;
            line-height: 1.25;
            margin-bottom: 12px;
        }

        .auth-left-sub {
            font-size: 15px;
            color: #3b5a80;
            line-height: 1.6;
        }

        .auth-left-img {
            margin-top: 40px;
            width: 260px;
            max-width: 100%;
        }

        /* ── KANAN ── */
        .auth-right {
            width: 520px;
            flex-shrink: 0;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 56px 48px;
            box-shadow: -4px 0 32px rgba(0,0,0,0.06);
            overflow-y: auto;
        }

        .auth-logo-sm {
            height: 40px;
            margin-bottom: 32px;
        }

        .auth-heading {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .auth-subheading {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 32px;
        }

        /* alert */
        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .alert-error { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
        .alert-success { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }

        /* form */
        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg.input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            stroke: #94a3b8;
            pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            font-size: 14px;
            color: #0f172a;
            background: #f8fafc;
            transition: border-color 0.2s, background 0.2s;
        }

        .input-wrap input:focus {
            outline: none;
            border-color: #005eb8;
            background: #ffffff;
        }

        .input-wrap input::placeholder { color: #94a3b8; }

        .toggle-pw {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            background: none;
            border: none;
            padding: 0;
            display: flex;
            align-items: center;
        }

        .toggle-pw svg { width: 18px; height: 18px; stroke: currentColor; }
        .toggle-pw:hover { color: #475569; }

        .form-footer-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: #475569;
        }

        .checkbox-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #005eb8;
            cursor: pointer;
        }

        .link-blue {
            color: #005eb8;
            text-decoration: none;
            font-weight: 600;
        }
        .link-blue:hover { text-decoration: underline; }

        /* tombol */
        .btn-submit {
            width: 100%;
            padding: 13px;
            background: #005eb8;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s, transform 0.1s;
        }
        .btn-submit:hover { background: #00418a; transform: translateY(-1px); }
        .btn-submit svg { width: 18px; height: 18px; stroke: currentColor; }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
            color: #94a3b8;
            font-size: 13px;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .btn-register {
            width: 100%;
            padding: 12px;
            background: #ffffff;
            color: #0f172a;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: border-color 0.2s, background 0.2s;
        }
        .btn-register:hover { border-color: #005eb8; background: #f0f7ff; }

        .auth-bottom {
            margin-top: 24px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }

        /* responsive */
        @media (max-width: 900px) {
            .auth-left { display: none; }
            .auth-right { width: 100%; padding: 40px 24px; box-shadow: none; }
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
    <div class="auth-heading">Selamat Datang Kembali!</div>
    <div class="auth-subheading"></div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?= $msg_type ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <form method="post" autocomplete="on">
        <div class="form-group">
            <label>Email</label>
            <div class="input-wrap">
                <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                <input type="email" name="email" placeholder="nama@email.com" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Password</label>
            <div class="input-wrap">
                <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                <input type="password" name="password" id="pw" placeholder="Masukkan kata sandi" required>
                <button type="button" class="toggle-pw" onclick="togglePw('pw', this)">
                    <svg id="eye-pw" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                </button>
            </div>
        </div>

        <div class="form-footer-row">
            <label class="checkbox-label">
                <input type="checkbox" name="remember"> Ingat Saya
            </label>
            <a href="reset_password.php" class="link-blue">Lupa Password?</a>
        </div>

        <button type="submit" class="btn-submit">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
            Masuk Sekarang
        </button>
    </form>

    <div class="divider">atau</div>

    <a href="register.php" class="btn-register">Daftar Akun Baru</a>

    <div class="auth-bottom">
        Belum punya akun? <a href="register.php" class="link-blue">Daftar Sekarang</a>
    </div>
</div>

<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.style.opacity = input.type === 'text' ? '0.5' : '1';
}
</script>
</body>
</html>