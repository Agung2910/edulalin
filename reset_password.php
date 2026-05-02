<?php
require_once "config.php";

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_verified'])) {
    header("Location: lupa_password.php");
    exit;
}

$email = $_SESSION['reset_email'];
$msg = "";
$msg_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass1 = $_POST['password'] ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';

    if (strlen($pass1) < 8) {
        $msg = "Password minimal 8 karakter.";
        $msg_type = "error";
    } elseif ($pass1 !== $pass2) {
        $msg = "Konfirmasi password tidak sama.";
        $msg_type = "error";
    } else {
        $hash = password_hash($pass1, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hash, $email);

        if ($stmt->execute()) {
            $stmt->close();
            
            unset($_SESSION['reset_email'], $_SESSION['otp_verified']);
            
            $_SESSION['success_message'] = "Password berhasil direset! Silakan login dengan password baru.";
            
            header("Location: login.php");
            exit;
        } else {
            $msg = "Terjadi kesalahan saat mengupdate password.";
            $msg_type = "error";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Password - Edu Lalin</title>
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
        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #cfd8dc;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #1f8a4d;
        }
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input {
            padding-right: 40px;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            user-select: none;
            font-size: 18px;
            color: #666;
        }
        .toggle-password:hover {
            color: #333;
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
            border: 1px solid #16a34a;
            color: #166534;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <h1 class="auth-title">Buat Password Baru</h1>
    <p class="auth-subtitle">Masukkan password baru untuk akun: <strong><?php echo htmlspecialchars($email); ?></strong></p>
    
    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?php echo $msg_type; ?>">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label>Password Baru</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" required>
                <span class="toggle-password" onclick="togglePassword('password')"></span>
            </div>
            <small style="font-size: 12px; color: #60748a; display: block; margin-top: 4px;">
                Minimal 8 karakter
            </small>
        </div>

        <div class="form-group">
            <label>Konfirmasi Password Baru</label>
            <div class="password-wrapper">
                <input type="password" name="password_confirm" id="password_confirm" required>
                <span class="toggle-password" onclick="togglePassword('password_confirm')"></span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full">Simpan Password Baru</button>
    </form>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>