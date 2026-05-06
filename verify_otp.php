<?php
require_once "config.php";

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$email = $_GET['email'] ?? '';
$type = $_GET['type'] ?? 'registration';

$allowedTypes = ['registration', 'password_reset'];
if (!in_array($type, $allowedTypes)) {
    $type = 'registration';
}

if (isset($_GET['debug'])) {
    echo "<div style='background:#fef3c7;border:2px solid #f59e0b;padding:15px;margin:20px;border-radius:8px;'>";
    echo "<h3>🐛 DEBUG MODE</h3>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    echo "<p><strong>Type dari URL:</strong> <code>" . htmlspecialchars($type) . "</code></p>";
    echo "<p><strong>Allowed Types:</strong> <code>" . implode(', ', $allowedTypes) . "</code></p>";
    echo "<p><strong>Type Valid?:</strong> " . (in_array($type, $allowedTypes) ? '✅ YES' : '❌ NO - fallback to registration') . "</p>";
    echo "<p><strong>URL saat ini:</strong> <code>" . htmlspecialchars($_SERVER['REQUEST_URI']) . "</code></p>";
    echo "</div>";
}

if (empty($email)) {
    header("Location: register.php");
    exit;
}

$msg = "";
$msg_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');
    
    if (empty($otp)) {
        $msg = "Kode OTP wajib diisi.";
        $msg_type = "error";
    } elseif (strlen($otp) !== 6 || !ctype_digit($otp)) {
        $msg = "Kode OTP harus 6 digit angka.";
        $msg_type = "error";
    } else {
        $verified = verifyOTP($conn, $email, $otp, $type);

        if ($verified) {
            if ($type === 'registration') {
                if (!isset($_SESSION['pending_registration'])) {
                    $msg = "Data registrasi tidak ditemukan. Silakan daftar ulang.";
                    $msg_type = "error";
                } else {
                    $data = $_SESSION['pending_registration'];

                    $tanggal = !empty($data['tanggal_lahir']) ? $data['tanggal_lahir'] : null;
                    $tempat  = !empty($data['tempat_lahir'])  ? $data['tempat_lahir']  : null;
                    $kelas   = !empty($data['kelas'])         ? $data['kelas']         : null;
                    $jenjang = !empty($data['jenjang'])       ? $data['jenjang']       : null;
                    $ig      = !empty($data['instagram'])     ? $data['instagram']     : null;
                    $tt      = !empty($data['tiktok'])        ? $data['tiktok']        : null;

                    $stmt = $conn->prepare("
                        INSERT INTO users 
                        (
                            nama,
                            tempat_lahir,
                            tanggal_lahir,
                            asal_sekolah,
                            kelas,
                            jenjang,
                            email,
                            no_telp,
                            instagram,
                            tiktok,
                            password,
                            role,
                            is_verified,
                            created_at
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
                    ");

                    $stmt->bind_param(
                        "ssssssssssss",
                        $data['nama'],
                        $tempat,
                        $tanggal,
                        $data['asal_sekolah'],
                        $kelas,
                        $jenjang,
                        $data['email'],
                        $data['no_telp'],
                        $ig,
                        $tt,
                        $data['password'],
                        $data['role']
                    );

                    if ($stmt->execute()) {
                        unset($_SESSION['pending_registration']);
                        $_SESSION['success_message'] = "Registrasi berhasil! Silakan login.";
                        header("Location: login.php");
                        exit;
                    } else {
                        $msg = "Gagal menyimpan data.";
                        $msg_type = "error";
                    }
                }
            }

            if ($type === 'password_reset') {
                $_SESSION['reset_email'] = $email;
                $_SESSION['otp_verified'] = true;
                header("Location: reset_password.php");
                exit;
            }
        } else {
            $msg = "Kode OTP salah atau sudah kadaluarsa.";
            $msg_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verifikasi OTP - Edu Lalin</title>
    <link rel="icon" type="image/png" href="assets/img/logo-jr.png">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            background: url("assets/img/background-web.png") no-repeat center top fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 480px;
            border: 2px solid #000;
        }
        .auth-title {
            font-size: 22px;
            margin-bottom: 4px;
            color: #333;
        }
        .auth-subtitle {
            font-size: 14px;
            margin-bottom: 24px;
            color: #60748a;
        }
        .otp-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #075985;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            font-size: 13px;
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
            color: #333;
        }
        .otp-input {
            width: 100%;
            padding: 14px 12px;
            border-radius: 8px;
            border: 2px solid #cfd8dc;
            font-size: 24px;
            text-align: center;
            letter-spacing: 8px;
            font-weight: 600;
            transition: border-color 0.3s ease;
        }
        .otp-input:focus {
            outline: none;
            border-color: #005eb8;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 999px;
            border: 1px solid transparent;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: transform .1s ease, box-shadow .1s ease, background .1s ease, border-color .1s ease;
        }
        .btn-primary {
            background: #005eb8;
            box-shadow: 0 4px 10px rgba(0,94,184,.35);
            color: #ffffff;
            border-color: #005eb8;
        }
        .btn-primary:hover {
            background: #00418a;
            transform: translateY(-3px);
        }
        .btn-full {
            width: 100%;
            margin-top: 8px;
        }
        .auth-links {
            margin-top: 16px;
            text-align: center;
            font-size: 13px;
        }
        .auth-links a {
            color: #005eb8;
            text-decoration: none;
            font-weight: 600;
        }
        .auth-links a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .alert-error {
            background: #fee;
            border: 1px solid #dc2626;
            color: #991b1b;
        }
        .alert-success {
            background: #dcfce7;
            border: 1px solid: #005eb8;
            color: #005eb8;
        }
        .resend-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        .resend-text {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .btn-resend {
            background: transparent;
            color: #005eb8;
            border: 1px solid #005eb8;
            padding: 6px 16px;
            font-size: 13px;
        }
        .btn-resend:hover {
            background: #f0fdf4;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <h1 class="auth-title">Verifikasi Kode OTP</h1>
    <p class="auth-subtitle">Masukkan kode 6 digit yang telah dikirim ke email Anda</p>
    
    <div class="otp-info">
        📧 Kode OTP telah dikirim ke:<br>
        <strong><?php echo htmlspecialchars($email); ?></strong>
    </div>
    
    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?php echo $msg_type; ?>">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label>Kode OTP (6 digit)</label>
            <input type="text" name="otp" class="otp-input" 
                   maxlength="6" pattern="[0-9]{6}" 
                   placeholder="000000" 
                   autocomplete="off"
                   autofocus
                   required>
        </div>

        <button type="submit" class="btn btn-primary btn-full">Verifikasi</button>
    </form>
    
    <div class="resend-section">
        <p class="resend-text">Tidak menerima kode?</p>
        <a href="resend_otp.php?email=<?php echo urlencode($email); ?>&type=<?php echo urlencode($type); ?>" class="btn btn-resend">
            Kirim Ulang Kode
        </a>
    </div>
    
    <div class="auth-links">
        <a href="register.php">← Kembali ke Registrasi</a>
    </div>
</div>

<script>
const otpInput = document.querySelector('.otp-input');
otpInput.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>

</body>
</html>