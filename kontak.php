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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body.kontak-page {
            font-family: 'Open Sans', sans-serif;
            background: #f0f2f8;
            color: #222;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── HERO ── */
        .kontak-hero {
            background: #1e3a5f;
            padding: 36px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .kontak-hero-left h1 {
            font-size: 32px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        .kontak-hero-left p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
        }

        .kontak-hero-icon {
            width: 90px;
            height: 90px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .kontak-hero-icon i {
            font-size: 44px;
            color: #a5b4fc;
        }

        /* ── BODY ── */
        .kontak-body {
            max-width: 1100px;
            width: 100%;
            margin: 0 auto;
            padding: 32px 24px;
            flex: 1;
        }

        /* ── SECTION LABEL ── */
        .section-label {
            font-size: 11px;
            font-weight: 700;
            color: #9aa0b4;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-label i {
            font-size: 16px;
            color: #1a3faa;
        }

        /* ── CARDS ── */
        .k-card {
            background: #fff;
            border: 1px solid #e4e8f0;
            border-radius: 16px;
            padding: 24px 28px;
        }

        /* ── MAIN ROW (info + map side by side) ── */
        .kontak-main-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        /* ── INFO ITEMS ── */
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 20px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .ibox {
            width: 40px;
            height: 40px;
            background: #eef1fb;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .ibox i {
            font-size: 20px;
            color: #1a3faa;
        }

        .ilabel {
            font-size: 10px;
            font-weight: 700;
            color: #9aa0b4;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 3px;
        }

        .ival {
            font-size: 14px;
            color: #222;
            line-height: 1.55;
        }

        .ival a {
            color: #1a3faa;
            text-decoration: none;
            font-weight: 600;
        }

        .ival a:hover {
            text-decoration: underline;
        }

        /* ── ALAMAT ── */
        .alamat-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 4px;
        }

        .adot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #1a3faa;
            margin-top: 5px;
            flex-shrink: 0;
        }

        .atext {
            font-size: 13px;
            color: #444;
            line-height: 1.6;
        }

        .atext strong {
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #1a3faa;
            margin-top: 4px;
        }

        /* ── MAP CARD ── */
        .kontak-map-card {
            background: #fff;
            border: 1px solid #e4e8f0;
            border-radius: 16px;
            overflow: hidden;
        }

        .kontak-map-card iframe {
            display: block;
            width: 100%;
            height: 100%;
            min-height: 320px;
            border: none;
        }

        /* ── SOSIAL MEDIA ── */
        .kontak-sosmed-row {
            margin-bottom: 0;
        }

        .sosmed-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
        }

        .sosmed-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 16px 10px;
            border-radius: 12px;
            border: 1px solid #e4e8f0;
            background: #fafbff;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            color: #222;
            transition: background 0.15s, border-color 0.15s, transform 0.12s;
        }

        .sosmed-btn:hover {
            background: #f0f4ff;
            border-color: #1a3faa;
            transform: translateY(-2px);
        }

        .sosmed-btn i {
            font-size: 26px;
        }

        .sosmed-btn.ig i { color: #e1306c; }
        .sosmed-btn.tt i { color: #111; }
        .sosmed-btn.yt i { color: #ff0000; }
        .sosmed-btn.fb i { color: #1877f2; }
        .sosmed-btn.mail i { color: #1a3faa; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .kontak-hero { padding: 32px 20px; }
            .kontak-hero-left h1 { font-size: 24px; }
            .kontak-hero-icon { width: 72px; height: 72px; }
            .kontak-hero-icon i { font-size: 36px; }

            .kontak-body { padding: 20px 16px; }
            .k-card { padding: 18px 16px; }

            .kontak-main-row { grid-template-columns: 1fr; }
            .kontak-map-card iframe { min-height: 260px; }

            .sosmed-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 480px) {
            .kontak-hero { flex-direction: column; align-items: flex-start; }
            .kontak-hero-icon { display: none; }

            .sosmed-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body class="kontak-page">

<?php include 'header_common.php'; ?>

<!-- HERO -->
<div class="kontak-hero">
    <div class="kontak-hero-left">
        <h1>Kontak</h1>
    </div>
</div>

<div class="kontak-body">
    <!-- BARIS UTAMA: INFO KONTAK + PETA -->
    <div class="kontak-main-row">
        <!-- KARTU KIRI: INFORMASI KONTAK -->
        <div class="k-card">
            <div class="section-label">
                <i class="ti ti-info-circle"></i> Informasi Kontak
            </div>

            <div class="info-item">
                <div class="ibox"><i class="ti ti-mail"></i></div>
                <div>
                    <div class="ilabel">Email</div>
                    <div class="ival">
                        <a href="mailto:m.agungg37@gmail.com">admin@edulalin.com</a>
                    </div>
                </div>
            </div>

            <div class="info-item">
                <div class="ibox"><i class="ti ti-brand-whatsapp"></i></div>
                <div>
                    <div class="ilabel">WhatsApp</div>
                    <div class="ival">
                        <a href="https://wa.me/6281234567890" target="_blank">0812-3456-7890</a>
                    </div>
                </div>
            </div>

            <div class="info-item">
                <div class="ibox"><i class="ti ti-clock"></i></div>
                <div>
                    <div class="ilabel">Jam Operasional</div>
                    <div class="ival">Senin – Jumat<br>07.30 – 16.30 WIB</div>
                </div>
            </div>

            <div class="info-item">
                <div class="ibox"><i class="ti ti-map-pin"></i></div>
                <div>
                    <div class="ilabel">Alamat Kantor</div>
                    <div class="alamat-row">
                        <div class="atext">
                            Jl. Jatinegara Timur No.107, RT.1/RW.2, Bali Mester,
                            Kecamatan Jatinegara, Kota Jakarta Timur,
                            Daerah Khusus Ibukota Jakarta 13310
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KARTU KANAN: PETA -->
        <div class="kontak-map-card">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d589.5944808308718!2d106.86832035659104!3d-6.22369828233813!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f37853ab699f%3A0x178e1c4e01d774a9!2sPT%20Jasa%20Raharja%20Kanwil%20DKI%20Jakarta!5e0!3m2!1sid!2sid!4v1779075681163!5m2!1sid!2sid"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="Peta lokasi kantor Edu Lalin">
            </iframe>
        </div>
    </div>

    <!-- BARIS BAWAH: SOSIAL MEDIA -->
    <div class="k-card kontak-sosmed-row">
        <div class="section-label">
            <i class="ti ti-world"></i> Temukan Kami di
        </div>
        <div class="sosmed-grid">
            <a href="https://www.instagram.com/jasaraharja_jakarta" target="_blank" class="sosmed-btn ig">
                <i class="ti ti-brand-instagram"></i>
                <span>Instagram</span>
            </a>
            <a href="https://tiktok.com/@edulalin" target="_blank" class="sosmed-btn tt">
                <i class="ti ti-brand-tiktok"></i>
                <span>TikTok</span>
            </a>
            <a href="http://www.youtube.com/@OfficialJasaRaharja" target="_blank" class="sosmed-btn yt">
                <i class="ti ti-brand-youtube"></i>
                <span>YouTube</span>
            </a>
            <a href="https://www.facebook.com/JasaRaharja" target="_blank" class="sosmed-btn fb">
                <i class="ti ti-brand-facebook"></i>
                <span>Facebook</span>
            </a>
            <a href="mailto:info@edulalin.id" class="sosmed-btn mail">
                <i class="ti ti-mail-forward"></i>
                <span>Email Kami</span>
            </a>
        </div>
    </div>
</div>

<?php include 'footer_common.php'; ?>
</body>
</html>