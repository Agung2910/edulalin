<?php
require_once "config.php";

$email = $_GET['email'] ?? '';
$type = $_GET['type'] ?? 'registration';

if (empty($email)) {
    header("Location: register.php");
    exit;
}

$otp = generateOTP();

if (storeOTP($conn, $email, $otp, $type)) {
    if (sendOTPEmail($email, $otp, $type)) {
        $_SESSION['otp_resent'] = "Kode OTP baru telah dikirim ke email Anda.";
    } else {
        $_SESSION['otp_resent'] = "Gagal mengirim email. Silakan coba lagi.";
    }
} else {
    $_SESSION['otp_resent'] = "Terjadi kesalahan. Silakan coba lagi.";
}

header("Location: verify_otp.php?email=" . urlencode($email) . "&type=" . urlencode($type));
exit;
?>