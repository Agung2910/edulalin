<?php
require_once "config.php";

if (
    !isset(
        $_SESSION['reactivate_user'],
        $_SESSION['reactivate_otp'],
        $_SESSION['reactivate_otp_exp']
    )
) {
    die("Session tidak valid");
}

if (time() > $_SESSION['reactivate_otp_exp']) {
    unset($_SESSION['reactivate_otp'], $_SESSION['reactivate_otp_exp']);
    die("OTP sudah kedaluwarsa");
}

$inputOtp = trim($_POST['otp'] ?? '');
if (!preg_match('/^[0-9]{6}$/', $inputOtp)) {
    die("Format OTP tidak valid");
}

if (!password_verify($inputOtp, $_SESSION['reactivate_otp'])) {
    die("OTP salah");
}

$stmt = $conn->prepare("
    SELECT id, nama, role
    FROM users
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param("i", $_SESSION['reactivate_user']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User tidak ditemukan");
}

$stmt = $conn->prepare("
    UPDATE users
    SET deleted_at = NULL,
        archived_reason = NULL
    WHERE id = ?
");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$stmt->close();

$_SESSION['user_id'] = $user['id'];
$_SESSION['nama']    = $user['nama'];
$_SESSION['role']    = $user['role'];

unset(
    $_SESSION['reactivate_user'],
    $_SESSION['reactivate_otp'],
    $_SESSION['reactivate_otp_exp'],
    $_SESSION['last_otp_sent']
);

$upd = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$upd->bind_param("i", $user['id']);
$upd->execute();
$upd->close();

header("Location: index.php");
exit;
