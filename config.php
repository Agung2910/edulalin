<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

ob_start(); // <-- tambah ini paling atas

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";

if ($_SERVER['SERVER_NAME'] === 'localhost') {
    // LOCAL (XAMPP)
    $user = "root";
    $pass = "";
    $db   = "db"; // sesuaikan sama DB local lu
} else {
    // HOSTING (CPANEL)
    $user = "tesa4121_edulalin";
    $pass = "kampungmelayujr";
    $db   = "tesa4121_edulalin";
}

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Gagal konek database: " . $conn->connect_error);
}

require_once __DIR__ . "/session_user.php";

$conn->query("SET time_zone = '+07:00'");

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

function require_admin() {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        header("Location: ../login.php");
        exit;
    }
}

function log_activity($tipe, $aksi, $keterangan) {
    global $conn;

    $user_id   = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $ip        = $_SERVER['REMOTE_ADDR'] ?? null;

    $stmt = $conn->prepare("
        INSERT INTO activity_log (waktu, user_id, tipe, aksi, keterangan, ip_address)
        VALUES (NOW(), ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "issss",
        $user_id,
        $tipe,
        $aksi,
        $keterangan,
        $ip
    );
    $stmt->execute();
    $stmt->close();
}

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'm.agungg37@gmail.com');      
define('SMTP_PASS', 'ygyh kfov hxty gbdn');           
define('SMTP_FROM_EMAIL', 'noreply@edulalin.com');
define('SMTP_FROM_NAME', 'Edu Lalin');

define('OTP_EXPIRY_MINUTES', 10); 
define('OTP_LENGTH', 6);

function generateOTP($length = OTP_LENGTH) {
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= random_int(0, 9);
    }
    return $otp;
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function sendOTPEmail($email, $otp, $type = 'registration') {
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        error_log("PHPMailer not installed. Run: composer require phpmailer/phpmailer");
        return false;
    }
    
    require_once __DIR__ . '/vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email);
        
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        
        if ($type == 'registration') {
            $mail->Subject = 'Kode Verifikasi Registrasi - Edu Lalin';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='text-align: center; margin-bottom: 30px;'>
                        <img src='https://your-domain.com/assets/img/logo-jr.png' alt='Edu Lalin' style='height: 50px;'>
                    </div>
                    <h2 style='color: #1f8a4d;'>Kode Verifikasi OTP Anda</h2>
                    <p>Terima kasih telah mendaftar di Edu Lalin. Gunakan kode OTP berikut untuk verifikasi akun Anda:</p>
                    <div style='background: #f0f0f0; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px;'>
                        <h1 style='color: #1f8a4d; font-size: 36px; letter-spacing: 5px; margin: 0;'>$otp</h1>
                    </div>
                    <p>Kode ini berlaku selama " . OTP_EXPIRY_MINUTES . " menit.</p>
                    <p style='color: #666; font-size: 12px;'>Jika Anda tidak merasa mendaftar, abaikan email ini.</p>
                </div>
            ";
        } else {
            $mail->Subject = 'Kode Reset Password - Edu Lalin';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='text-align: center; margin-bottom: 30px;'>
                        <img src='https://your-domain.com/assets/img/logo-jr.png' alt='Edu Lalin' style='height: 50px;'>
                    </div>
                    <h2 style='color: #1f8a4d;'>Reset Password</h2>
                    <p>Anda telah meminta reset password. Gunakan kode OTP berikut:</p>
                    <div style='background: #f0f0f0; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px;'>
                        <h1 style='color: #1f8a4d; font-size: 36px; letter-spacing: 5px; margin: 0;'>$otp</h1>
                    </div>
                    <p>Kode ini berlaku selama " . OTP_EXPIRY_MINUTES . " menit.</p>
                    <p style='color: #666; font-size: 12px;'>Jika Anda tidak meminta reset password, abaikan email ini.</p>
                </div>
            ";
        }
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email gagal dikirim: {$mail->ErrorInfo}");
        return false;
    }
}

function storeOTP($conn, $email, $otp, $type = 'registration') {
    $delete_query = "DELETE FROM otp_verification WHERE email = ? AND type = ? AND is_used = 0";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ss", $email, $type);
    $stmt->execute();
    $stmt->close();
    
    $expires_at = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
    $insert_query = "INSERT INTO otp_verification (email, otp_code, type, expires_at) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssss", $email, $otp, $type, $expires_at);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

function verifyOTP($conn, $email, $otp, $type = 'registration') {
    $email = trim($email);
    $otp = trim($otp);
    $type = trim($type);
    
    error_log("verifyOTP called with: email=$email, otp=$otp, type=$type");
    
    $query = "SELECT id, type FROM otp_verification 
              WHERE email = ? AND otp_code = ? AND type = ? 
              AND is_used = 0 AND expires_at > NOW()
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $email, $otp, $type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $otp_id = $row['id'];
        error_log("OTP verified successfully! ID: $otp_id, Type: " . $row['type']);
        $stmt->close();
        
        $update_query = "UPDATE otp_verification SET is_used = 1 WHERE id = ?";
        $stmt2 = $conn->prepare($update_query);
        $stmt2->bind_param("i", $otp_id);
        $stmt2->execute();
        $stmt2->close();
        
        return true;
    }
    
    error_log("OTP verification failed for email=$email, otp=$otp, type=$type");
    
    $stmt->close();
    return false;
}

function cleanExpiredOTP($conn) {
    $query = "DELETE FROM otp_verification WHERE expires_at < NOW() OR is_used = 1";
    return $conn->query($query);
}

function require_admin_or_guru() {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
        header("Location: ../index.php");
        exit;
    }
}

function is_guru() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'guru';
}

if (!function_exists('require_jenjang')) {
    function require_jenjang(array $allowed): void {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit;
        }

        if (!isset($_SESSION['jenjang'])) {
            header("Location: blocked.php");
            exit;
        }

        if (!in_array($_SESSION['jenjang'], $allowed)) {
            header("Location: blocked.php");
            exit;
        }
    }
}

function get_user_age(): int {
    if (!isset($_SESSION['user_id'])) return 0;

    global $conn;
    $uid = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT tanggal_lahir 
        FROM users 
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->bind_result($dob);

    if ($stmt->fetch() && $dob) {
        $stmt->close();
        return (int)floor((time() - strtotime($dob)) / 31556926);
    }

    $stmt->close();
    return 0;
}
?>