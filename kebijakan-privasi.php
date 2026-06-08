<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kebijakan Privasi - Edu Lalin</title>

<link rel="stylesheet" href="assets/css/style.css">

<style>
body{
    margin:0;
    background:#f5f7fb;
    color:#1f2937;
    font-family:'Inter', Arial, sans-serif;
}

.legal-hero{
    background:linear-gradient(135deg,#1e3a8a 0%, #2563eb 100%);
    padding:80px 20px;
    color:#fff;
    text-align:center;
}

.legal-hero h1{
    margin:0;
    font-size:42px;
    font-weight:700;
    letter-spacing:-0.5px;
}

.legal-hero p{
    margin-top:12px;
    font-size:15px;
    opacity:.9;
}

.legal-wrapper{
    max-width:980px;
    margin:-40px auto 60px;
    padding:0 20px;
}

.legal-card{
    background:#fff;
    border-radius:18px;
    padding:56px 64px;
    box-shadow:0 8px 28px rgba(15,23,42,.08);
    border:1px solid #e5e7eb;
}

.legal-top{
    display:flex;
    justify-content:space-between;
    gap:20px;
    flex-wrap:wrap;
    margin-bottom:42px;
    padding-bottom:24px;
    border-bottom:1px solid #e5e7eb;
}

.legal-top div{
    font-size:14px;
    color:#64748b;
}

.legal-section{
    margin-bottom:42px;
}

.legal-section h2{
    margin:0 0 18px;
    font-size:22px;
    color:#0f172a;
    font-weight:700;
}

.legal-section p{
    margin:0 0 16px;
    line-height:1.9;
    font-size:15px;
    color:#374151;
}

.legal-section ul{
    margin:0;
    padding-left:22px;
}

.legal-section li{
    margin-bottom:12px;
    line-height:1.8;
    color:#374151;
    font-size:15px;
}

.legal-footer{
    border-top:1px solid #e5e7eb;
    margin-top:52px;
    padding-top:24px;
    color:#64748b;
    font-size:14px;
}

a{
    color:#2563eb;
    text-decoration:none;
}

a:hover{
    text-decoration:underline;
}

@media(max-width:768px){

    .legal-hero{
        padding:60px 20px;
    }

    .legal-hero h1{
        font-size:32px;
    }

    .legal-card{
        padding:32px 24px;
    }

    .legal-section h2{
        font-size:20px;
    }
}
</style>
</head>
<body>

<?php include 'header_common.php'; ?>

<section class="legal-hero">
    <h1>Kebijakan Privasi</h1>
    <p>Informasi pengelolaan dan perlindungan data pengguna Edu Lalin</p>
</section>

<div class="legal-wrapper">
    <div class="legal-card">

        <div class="legal-top">
            <div>
                <strong>Dokumen Privasi</strong><br>
                Edu Lalin Platform
            </div>

            <div>
                Terakhir diperbarui<br>
                1 Januari 2026
            </div>
        </div>

        <div class="legal-section">
            <h2>1. Pendahuluan</h2>

            <p>
                Kebijakan Privasi ini menjelaskan bagaimana Edu Lalin mengumpulkan, menggunakan, menyimpan, dan melindungi data pengguna yang menggunakan layanan pada platform Edu Lalin.
            </p>
        </div>

        <div class="legal-section">
            <h2>2. Informasi yang Dikumpulkan</h2>

            <ul>
                <li>Nama lengkap dan informasi profil pengguna.</li>
                <li>Alamat email dan nomor telepon.</li>
                <li>Data aktivitas pembelajaran dan progres pengguna.</li>
                <li>Informasi perangkat, browser, dan alamat IP.</li>
            </ul>
        </div>

        <div class="legal-section">
            <h2>3. Penggunaan Informasi</h2>

            <p>
                Informasi pengguna digunakan untuk menyediakan layanan pembelajaran, mengelola akun, meningkatkan kualitas layanan, dan menjaga keamanan platform.
            </p>
        </div>

        <div class="legal-section">
            <h2>4. Cookies dan Teknologi Pelacakan</h2>

            <p>
                Platform dapat menggunakan cookies untuk menyimpan preferensi pengguna dan membantu analisis penggunaan layanan.
            </p>
        </div>

        <div class="legal-section">
            <h2>5. Penyimpanan dan Keamanan Data</h2>

            <p>
                Edu Lalin menerapkan langkah keamanan yang wajar untuk melindungi data pengguna dari akses tidak sah, kehilangan, atau penyalahgunaan data.
            </p>
        </div>

        <div class="legal-section">
            <h2>6. Hak Pengguna</h2>

            <ul>
                <li>Memperbarui data akun dan informasi pribadi.</li>
                <li>Meminta penghapusan akun.</li>
                <li>Menghubungi platform terkait data pribadi.</li>
            </ul>
        </div>

        <div class="legal-section">
            <h2>7. Perubahan Kebijakan Privasi</h2>

            <p>
                Kebijakan Privasi dapat diperbarui sewaktu-waktu sesuai kebutuhan layanan dan ketentuan yang berlaku.
            </p>
        </div>

        <div class="legal-section">
            <h2>8. Informasi Kontak</h2>

            <p>
                Jika memiliki pertanyaan terkait Kebijakan Privasi ini, silakan hubungi:
            </p>

            <p>
                Email: <a href="mailto:privacy@edulalin.id">privacy@edulalin.id</a>
            </p>
        </div>

        <div class="legal-footer">
            © <?php echo date('Y'); ?> Edu Lalin. Seluruh hak cipta dilindungi.
        </div>

    </div>
</div>

<?php include 'footer_common.php'; ?>

</body>
</html>
