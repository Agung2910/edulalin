<?php
require_once "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/PHPMailer/Exception.php';
require 'vendor/PHPMailer/PHPMailer.php';
require 'vendor/PHPMailer/SMTP.php';

if (!isset($_SESSION['reactivate_user'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['reactivate_user'];

$stmt = $conn->prepare("
    SELECT id, nama, email 
    FROM users 
    WHERE id = ? 
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$msg = "";
$msg_type = "";

if (isset($_POST['send_otp'])) {

    if (isset($_SESSION['last_otp_sent']) && time() - $_SESSION['last_otp_sent'] < 60) {
        $msg = "Silakan tunggu 1 menit sebelum mengirim OTP lagi.";
        $msg_type = "error";
    } else {

        $otp = rand(100000, 999999);

        $_SESSION['reactivate_otp']     = password_hash($otp, PASSWORD_DEFAULT);
        $_SESSION['reactivate_otp_exp'] = time() + 300; // 5 menit
        $_SESSION['last_otp_sent']      = time();

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = 'tls';
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(SMTP_USER, SMTP_FROM);
            $mail->addAddress($user['email'], $user['nama']);

            $mail->isHTML(true);
            $mail->Subject = 'Kode OTP Aktivasi Akun Edu Lalin';
            $mail->Body = "
                <h3>Aktivasi Akun Edu Lalin</h3>
                <p>Halo <b>{$user['nama']}</b>,</p>
                <p>Kode OTP Anda:</p>
                <h2 style='letter-spacing:2px;'>$otp</h2>
                <p>Berlaku selama <b>5 menit</b>.</p>
                <p>Jika Anda tidak merasa melakukan ini, abaikan email ini.</p>
            ";

            $mail->send();

            $msg = "OTP berhasil dikirim ke email Anda.";
            $msg_type = "success";

        } catch (Exception $e) {
            $msg = "Gagal mengirim OTP. Silakan coba lagi.";
            $msg_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Aktivasi Akun - Edu Lalin</title>
<link rel="icon" type="image/png" href="assets/img/logo-jr.png">
<style>
body{
    font-family: system-ui, Arial, sans-serif;
    background:#f1f5f9;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
}
.card{
    background:white;
    padding:30px;
    border-radius:12px;
    width:100%;
    max-width:420px;
    box-shadow:0 10px 25px rgba(0,0,0,.1);
}
h2{margin-bottom:8px;}
p{font-size:14px;color:#555;}
.alert{
    padding:10px;
    border-radius:8px;
    margin-bottom:16px;
    font-size:14px;
}
.alert-success{
    background:#dcfce7;
    color:#166534;
}
.alert-error{
    background:#fee2e2;
    color:#991b1b;
}
.btn{
    width:100%;
    padding:10px;
    border:none;
    border-radius:999px;
    font-weight:600;
    cursor:pointer;
}
.btn-primary{
    background:#005eb8;
    color:white;
}
.btn-primary:hover{background:#00418a;}
input{
    width:100%;
    padding:10px;
    border-radius:8px;
    border:1px solid #cbd5e1;
    margin-bottom:12px;
}
</style>
</head>
<body>

<div class="card">
    <h2>Akun Tidak Aktif</h2>
    <p>Akun Anda lama tidak digunakan.  
       Silakan verifikasi untuk mengaktifkan kembali.</p>

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <button type="submit" name="send_otp" class="btn btn-primary">
            Kirim OTP ke Email
        </button>
    </form>

    <?php if (isset($_SESSION['reactivate_otp'])): ?>
    <hr style="margin:20px 0;">
    <form action="verify_reactivate.php" method="post">
        <input type="text" name="otp" placeholder="Masukkan OTP" required>
        <button class="btn btn-primary">Verifikasi</button>
    </form>
    <?php endif; ?>
</div>

</body>
</html>
