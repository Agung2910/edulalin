<?php
require_once "config.php";

$msg = "";
$msg_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $msg = "Email wajib diisi.";
        $msg_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Format email tidak valid.";
        $msg_type = "error";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($uid);

        if ($stmt->fetch()) {
            $stmt->close();
            $otp = generateOTP();
            if (storeOTP($conn, $email, $otp, 'password_reset')) {
                if (sendOTPEmail($email, $otp, 'password_reset')) {
                    header("Location: verify_otp.php?email=" . urlencode($email) . "&type=password_reset");
                    exit;
                } else {
                    $msg = "Gagal mengirim email OTP. Silakan coba lagi.";
                    $msg_type = "error";
                }
            } else {
                $msg = "Terjadi kesalahan. Silakan coba lagi.";
                $msg_type = "error";
            }
        } else {
            $stmt->close();
            $msg = "Jika email terdaftar, kode OTP akan dikirim.";
            $msg_type = "success";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password - Edu Lalin</title>
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
            max-width: 400px;
        }

        .auth-left-logo { height: 52px; margin-bottom: 28px; }

        .auth-left-title {
            font-size: 26px;
            font-weight: 800;
            color: #1e3a5f;
            line-height: 1.3;
            margin-bottom: 10px;
        }

        .auth-left-sub { font-size: 14px; color: #3b5a80; line-height: 1.6; }
        .auth-left-img { margin-top: 36px; width: 240px; max-width: 100%; }

        /* KANAN */
        .auth-right {
            width: 520px;
            flex-shrink: 0;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 56px 48px;
            box-shadow: -4px 0 32px rgba(0,0,0,0.06);
        }

        .auth-logo-sm { height: 38px; margin-bottom: 32px; }
        .auth-heading { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
        .auth-subheading { font-size: 14px; color: #64748b; margin-bottom: 32px; line-height: 1.6; }

        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .alert-error { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
        .alert-success { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }

        .form-group { margin-bottom: 20px; }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .input-wrap { position: relative; }

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

        /* info box */
        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 14px 16px;
            font-size: 13px;
            color: #1e40af;
            line-height: 1.6;
            margin-bottom: 24px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .info-box svg {
            width: 18px;
            height: 18px;
            stroke: #3b82f6;
            flex-shrink: 0;
            margin-top: 1px;
        }

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

        .auth-bottom {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }

        .link-blue { color: #005eb8; font-weight: 600; text-decoration: none; }
        .link-blue:hover { text-decoration: underline; }

        @media (max-width: 900px) {
            .auth-left { display: none; }
            .auth-right { width: 100%; padding: 40px 24px; box-shadow: none; justify-content: flex-start; padding-top: 60px; }
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
    <div class="auth-heading">Reset Password</div>
    <div class="auth-subheading">Masukkan email yang terdaftar untuk mendapatkan kode verifikasi OTP.</div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="info-box">
        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
        <span>Kode OTP akan dikirim ke email Anda. Pastikan email yang dimasukkan sudah benar dan aktif.</span>
    </div>

    <form method="post">
        <div class="form-group">
            <label>Alamat Email</label>
            <div class="input-wrap">
                <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                <input type="email" name="email" placeholder="nama@email.com" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
        </div>

        <button type="submit" class="btn-submit">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
            Kirim Kode Verifikasi
        </button>
    </form>

    <div class="auth-bottom">
        Ingat password? <a href="login.php" class="link-blue">Kembali ke Login</a>
    </div>
</div>

</body>
</html>