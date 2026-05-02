<?php
require_once "config.php";
$page = 'kontak';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kontak - Edu Lalin</title>
    <link rel="icon" type="image/png" href="assets/img/logo-jr.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="kontak-page">
<?php include 'header_common.php'; ?>
<div class="site-bg">
    <div class="site-paper">
        <div class="container">
            <section class="card">
                <h2>Informasi Kontak</h2>
                <div class="kontak-grid">
                    <div class="kontak-box">
                        <h3>Alamat</h3>
                        <p>Jl. Jatinegara Timur No.107, Jakarta Timur</p>
                    </div>
                    <div class="kontak-box">
                        <h3>Email</h3>
                        <p>admin@edulalin.com<br>admin@edulalin.com</p>
                    </div>
                    <div class="kontak-box">
                        <h3>Telepon</h3>
                        <p>0812-3456-7890</p>
                    </div>
                    <div class="kontak-box">
                        <h3>Jam Operasional</h3>
                        <p>Senin - Jumat<br>07.30 - 16.30 WIB</p>
                    </div>
                    <a href="https://www.instagram.com/jasaraharja_jakarta" target="_blank" class="kontak-box social ig">
                        Instagram
                    </a>
                    <a href="https://tiktok.com/@edulalin" target="_blank" class="kontak-box social tiktok">
                        TikTok
                    </a>
                    <a href="mailto:info@edulalin.id" class="kontak-box social mail">
                        Email Kami
                    </a>
                    <a href="https://www.facebook.com/JasaRaharja" target="_blank" class="kontak-box social fb">
                        Facebook
                    </a>
                    <a href="http://www.youtube.com/@OfficialJasaRaharja" target="_blank" class="kontak-box social yt">
                        YouTube
                    </a>
                </div>
            </section>
        </div>
    </div>
</div>
<?php include 'footer_common.php'; ?>
</body>
</html>