<?php
require_once "config.php";

if (isset($_SESSION['user_id'])) {
    header("Location: index.php"); 
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Masuk / Daftar - Edu Lalin</title>
    <link rel="icon" type="image/png" href="assets/img/logo-jr.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-wrapper {
            max-width: 1080px;
            margin: 40px auto;
            display: grid;
            grid-template-columns: minmax(0,1.2fr) minmax(0,1fr);
            gap: 32px;
            align-items: stretch;
        }
        .auth-illustration {
            border-radius: 16px;
            overflow: hidden;
        }
        .auth-illustration img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 28px 32px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        }
        .auth-title {
            font-size: 22px;
            margin-bottom: 4px;
        }
        .auth-subtitle {
            font-size: 14px;
            margin-bottom: 18px;
            color: #60748a;
        }
        .auth-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }
        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 8px 0;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid #d0d7de;
            color: #4b5563;
        }
        .auth-tab.active {
            background: #1f8a4d;
            border-color: #1f8a4d;
            color: #ffffff;
        }
        .auth-form {
            display: none;
        }
        .auth-form.active {
            display: block;
        }
        .auth-form label {
            font-size: 13px;
            font-weight: 600;
            margin-top: 10px;
            display: block;
        }
        .auth-form input {
            width: 100%;
            padding: 8px 10px;
            margin-top: 4px;
            border-radius: 8px;
            border: 1px solid #cfd8dc;
            font-size: 14px;
        }
        .auth-form button {
            margin-top: 18px;
            width: 100%;
        }
    </style>
</head>
<body>

<div class="topbar">
    <div class="brand">
        <img src="assets/img/logo-jr.png" alt="Logo Edu Lalin">
        <span class="brand-title">Edu Lalin</span>
    </div>
</div>
<div class="auth-wrapper">
    <div class="auth-illustration">
        <img src="assets/img/auth-illustration.jpg" alt="Ilustrasi Edu Lalin">
    </div>
    <div class="auth-card">
        <h1 class="auth-title">Selamat Datang!</h1>
        <p class="auth-subtitle">Masuk atau daftar akun Edu Lalin untuk mulai belajar.</p>
        <div class="auth-tabs">
            <button class="auth-tab active" data-target="login">Masuk</button>
            <button class="auth-tab" data-target="register">Daftar</button>
        </div>

        <form class="auth-form active" id="form-login" action="login.php" method="post">
            <label for="email_login">Email</label>
            <input type="email" id="email_login" name="email" required>

            <label for="password_login">Kata Sandi</label>
            <input type="password" id="password_login" name="password" required>

            <button type="submit" class="btn btn-primary">Masuk</button>
        </form>

        <form class="auth-form" id="form-register" action="register.php" method="post">
            <label for="nama_reg">Nama Lengkap</label>
            <input type="text" id="nama_reg" name="nama" required>

            <label for="email_reg">Email</label>
            <input type="email" id="email_reg" name="email" required>

            <label for="password_reg">Kata Sandi</label>
            <input type="password" id="password_reg" name="password" required>

            <button type="submit" class="btn btn-primary">Daftar Akun</button>
        </form>
    </div>
</div>
<script>
document.querySelectorAll('.auth-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const target = tab.getAttribute('data-target');
        document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
        document.getElementById('form-' + target).classList.add('active');
    });
});
</script>
</body>
</html>