<?php
require_once "config.php";
require_login();

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT id, nama, email, role, jenjang, kelas, asal_sekolah
    FROM users
    WHERE id = ? AND deleted_at IS NULL
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$user['nama']    = $user['nama']    ?? 'User';
$user['role']    = $user['role']    ?? 'user';
$user['jenjang'] = $user['jenjang'] ?? '';
$user['kelas']   = $user['kelas']   ?? '';

$jenjang_user = strtoupper(trim($user['jenjang']));
$kelas_angka  = (int) preg_replace('/[^0-9]/', '', (string)$user['kelas']);

$nama_parts = explode(' ', trim($user['nama']));
$avatar = strtoupper(substr($nama_parts[0] ?? 'U', 0, 1));
if (isset($nama_parts[1])) {
    $avatar .= strtoupper(substr($nama_parts[1], 0, 1));
}

function fmt_tanggal(?string $dt): string {
    if (!$dt || strtotime($dt) <= 0) return '-';

    $bln = [
        '', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
    ];

    $ts = strtotime($dt);
    return date('d', $ts) . ' ' . $bln[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

function get_kelompok_key(array $row): string {
    $kategori = strtolower((string)($row['kategori'] ?? ''));
    $jenjang  = strtoupper((string)($row['jenjang'] ?? ''));
    $kelas    = trim((string)($row['kelas'] ?? ''));

    if ($kategori === 'sim') {
        return 'SIM (Surat Izin Mengemudi)';
    }

    if (in_array($jenjang, ['SMA', 'SMK'], true) && $kelas !== '') {
        return 'Kelas ' . $kelas;
    }

    return $jenjang !== '' ? $jenjang : 'Umum';
}

function boleh_tampil_modul(array $row, string $jenjang_user, int $kelas_angka): bool {
    $kategori = strtolower((string)($row['kategori'] ?? ''));
    $jenjang  = strtoupper((string)($row['jenjang'] ?? ''));
    $kelas    = (int) preg_replace('/[^0-9]/', '', (string)($row['kelas'] ?? ''));

    if ($kategori === 'sim') {
        return $kelas_angka >= 12;
    }

    if ($jenjang_user !== '' && $jenjang !== '' && $jenjang !== $jenjang_user) {
        return false;
    }

    if (in_array($jenjang, ['SMA', 'SMK'], true) && $kelas_angka > 0 && $kelas > 0 && $kelas !== $kelas_angka) {
        return false;
    }

    return true;
}

$stmt = $conn->prepare("
    SELECT
        m.id,
        m.judul,
        m.kategori,
        m.jenjang,
        m.kelas,
        COALESCE(p.is_read, 0) AS is_read,
        COALESCE(p.status, 'belum') AS status,
        COUNT(q.id) AS jumlah_soal
    FROM modul m
    LEFT JOIN progress p
        ON p.modul_id = m.id
        AND p.user_id = ?
    LEFT JOIN quiz q
        ON q.materi_id = m.id
    WHERE m.is_active = 1
    GROUP BY
        m.id, m.judul, m.kategori, m.jenjang, m.kelas,
        p.is_read, p.status
    ORDER BY m.kategori, m.jenjang, m.kelas, m.id
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_modul = $stmt->get_result();

$icons_map = [
    'SD'  => '📚',
    'SMP' => '📖',
    'SMA' => '🎓',
    'SMK' => '🔧',
];

$kelompok = [];
$total_modul_tersedia = 0;
$modul_selesai = 0;
$total_kuis = 0;

while ($row = $result_modul->fetch_assoc()) {
    if (!boleh_tampil_modul($row, $jenjang_user, $kelas_angka)) {
        continue;
    }

    $key = get_kelompok_key($row);
    $jenjang_row = strtoupper((string)($row['jenjang'] ?? ''));
    $icon = strtolower((string)$row['kategori']) === 'sim'
        ? '🚗'
        : ($icons_map[$jenjang_row] ?? '📋');

    if (!isset($kelompok[$key])) {
        $kelompok[$key] = [
            'icon'          => $icon,
            'total'         => 0,
            'selesai'       => 0,
            'kuis_total'    => 0,
            'kuis_dikerjakan' => [],
            'kuis_lulus'      => [],
        ];
    }

    $kelompok[$key]['total']++;
    $total_modul_tersedia++;

    if ((int)$row['is_read'] === 1 || strtolower((string)$row['status']) === 'selesai') {
        $kelompok[$key]['selesai']++;
        $modul_selesai++;
    }

    if ((int)$row['jumlah_soal'] > 0) {
        $kelompok[$key]['kuis_total']++;
        $total_kuis++;
    }
}
$stmt->close();

$stmt = $conn->prepare("
    SELECT
        qa.quiz_id,
        qa.score,
        qa.total_questions,
        qa.correct_answers,
        qa.attempted_at,
        m.id AS modul_id,
        COALESCE(m.judul, 'Kuis') AS judul_kuis,
        m.kategori,
        m.jenjang,
        m.kelas
    FROM quiz_attempts qa
    LEFT JOIN quiz q
        ON q.id = qa.quiz_id
    LEFT JOIN modul m
        ON m.id = qa.quiz_id
        OR m.id = q.materi_id
    WHERE qa.user_id = ?
    ORDER BY qa.attempted_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$all_attempts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$kuis_dikerjakan_map = [];
$kuis_lulus_map = [];
$total_score = 0;
$total_attempt_score = 0;

foreach ($all_attempts as $att) {
    if (!boleh_tampil_modul($att, $jenjang_user, $kelas_angka)) {
        continue;
    }

    $modul_id_kuis = (int)($att['modul_id'] ?? 0);
    if ($modul_id_kuis <= 0) {
        continue;
    }

    $key = get_kelompok_key($att);
    if (!isset($kelompok[$key])) {
        continue;
    }

    $total_questions = (int)($att['total_questions'] ?? 0);
    $correct_answers = (int)($att['correct_answers'] ?? 0);
    $raw_score       = (int)($att['score'] ?? 0);

    if ($total_questions > 0) {
        $score = round(($correct_answers / $total_questions) * 100);
    } else {
        $score = $raw_score;
    }

    $kelompok[$key]['kuis_dikerjakan'][$modul_id_kuis] = true;
    $kuis_dikerjakan_map[$modul_id_kuis] = true;

    if ($score >= 70) {
        $kelompok[$key]['kuis_lulus'][$modul_id_kuis] = true;
        $kuis_lulus_map[$modul_id_kuis] = true;
    }

    $total_score += $score;
    $total_attempt_score++;
}

$kuis_dikerjakan = count($kuis_dikerjakan_map);
$kuis_lulus = count($kuis_lulus_map);


$semua_kuis_dikerjakan = ($total_kuis > 0 && $kuis_dikerjakan >= $total_kuis);
$semua_kuis_lulus      = ($total_kuis > 0 && $kuis_lulus >= $total_kuis);
$boleh_sertifikat      = ($semua_kuis_dikerjakan && $semua_kuis_lulus);
$sertifikat_diperoleh  = $boleh_sertifikat ? 1 : 0;

$rata_nilai = $total_attempt_score > 0 ? round($total_score / $total_attempt_score) : 0;

$progress_kuis_pct = $total_kuis > 0
    ? round(($kuis_dikerjakan / $total_kuis) * 100)
    : 0;

$progress_modul_pct = $total_modul_tersedia > 0
    ? round(($modul_selesai / $total_modul_tersedia) * 100)
    : 0;

$progress_total_pct = ($total_modul_tersedia + $total_kuis) > 0
    ? round((($modul_selesai + $kuis_dikerjakan) / ($total_modul_tersedia + $total_kuis)) * 100)
    : 0;

$colors = ['#3b82f6','#22c55e','#a855f7','#f97316','#ec4899','#14b8a6'];
$ci = 0;
$progress_kelas = [];

foreach ($kelompok as $nama => $data) {
    $total_item_kelompok = $data['total'] + $data['kuis_total'];
    $selesai_item_kelompok = $data['selesai'] + count($data['kuis_dikerjakan']);

    $pct = $total_item_kelompok > 0
        ? round(($selesai_item_kelompok / $total_item_kelompok) * 100)
        : 0;

    $progress_kelas[] = [
        'nama'              => $nama,
        'icon'              => $data['icon'],
        'selesai'           => $data['selesai'],
        'total'             => $data['total'],
        'pct'               => $pct,
        'color'             => $colors[$ci++ % count($colors)],
        'kuis_total'        => $data['kuis_total'],
        'kuis_dikerjakan'   => count($data['kuis_dikerjakan']),
        'kuis_lulus'        => count($data['kuis_lulus']),
    ];
}

$riwayat_kuis = array_slice(array_values(array_filter($all_attempts, function ($att) use ($jenjang_user, $kelas_angka) {
    return boleh_tampil_modul($att, $jenjang_user, $kelas_angka);
})), 0, 10);

$sertifikat = null;
if ($boleh_sertifikat) {
    foreach ($all_attempts as $att) {
        if (!boleh_tampil_modul($att, $jenjang_user, $kelas_angka)) {
            continue;
        }

        if ((int)($att['score'] ?? 0) >= 70) {
            $sertifikat = $att;
            break;
        }
    }
}

$stmt = $conn->prepare("SELECT MAX(updated_at) AS last FROM progress WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$last_modul = $stmt->get_result()->fetch_assoc()['last'] ?? null;
$stmt->close();

$stmt = $conn->prepare("SELECT MAX(attempted_at) AS last FROM quiz_attempts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$last_kuis = $stmt->get_result()->fetch_assoc()['last'] ?? null;
$stmt->close();

$last_ts = max(strtotime($last_modul ?: '0'), strtotime($last_kuis ?: '0'));
$last_update = $last_ts > 0 ? fmt_tanggal(date('Y-m-d H:i:s', $last_ts)) : 'Belum ada aktivitas';
?>
<?php include 'header_common.php'; ?>

<title>Progress</title>
<link rel="icon" type="image/png" href="assets/img/logo.png">
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

<style>

#pb-root {
    font-family: 'Plus Jakarta Sans', sans-serif !important;
    padding: 28px 32px !important;
    background: #f1f5f9 !important;
    min-height: 80vh;
    color: #0f172a;
    box-sizing: border-box;
}
#pb-root *, #pb-root *::before, #pb-root *::after {
    box-sizing: border-box !important;
}

#pb-root .pb-top {
    display: flex !important;
    align-items: flex-start !important;
    justify-content: space-between !important;
    margin-bottom: 26px !important;
    flex-wrap: wrap !important;
    gap: 12px !important;
}
#pb-root .pb-top h1 { font-size: 22px !important; font-weight: 800 !important; margin: 0 !important; }
#pb-root .pb-top p  { font-size: 13px !important; color: #475569 !important; margin: 3px 0 0 !important; }
#pb-root .pb-chip {
    display: flex !important; align-items: center !important; gap: 8px !important;
    background: #fff !important; border: 1.5px solid #e2e8f0 !important;
    border-radius: 10px !important; padding: 6px 12px 6px 6px !important;
}
#pb-root .pb-av {
    width: 32px !important; height: 32px !important; flex-shrink: 0 !important;
    background: linear-gradient(135deg,#3b82f6,#a855f7) !important;
    border-radius: 50% !important; display: flex !important;
    align-items: center !important; justify-content: center !important;
    color: #fff !important; font-size: 11px !important; font-weight: 700 !important;
}
#pb-root .pb-uname { font-size: 13px !important; font-weight: 700 !important; line-height: 1.3; }
#pb-root .pb-urole { font-size: 11px !important; color: #475569 !important; }

#pb-root .pb-g2 {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 20px !important;
    margin-bottom: 20px !important;
    width: 100% !important;
}

#pb-root .pb-g4 {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 14px !important;
    width: 100% !important;
}

#pb-root .pb-card {
    background: #fff !important;
    border-radius: 14px !important;
    padding: 22px !important;
    box-shadow: 0 2px 12px rgba(15,23,42,.07) !important;
    width: 100% !important;
    float: none !important;
}
#pb-root .pb-card-hdr {
    display: flex !important; align-items: center !important;
    justify-content: space-between !important;
    margin-bottom: 18px !important;
}
#pb-root .pb-card-ttl { font-size: 15px !important; font-weight: 700 !important; }
#pb-root .pb-card-meta { font-size: 11px !important; color: #94a3b8 !important; }
#pb-root .pb-lnk { font-size: 12px !important; color: #3b82f6 !important; font-weight: 600 !important; text-decoration: none !important; }

#pb-root .pb-donut-wrap {
    position: relative !important; width: 120px !important;
    height: 120px !important; flex-shrink: 0 !important;
}
#pb-root .pb-donut-wrap svg { transform: rotate(-90deg) !important; display: block !important; }
#pb-root .pb-donut-lbl {
    position: absolute !important; top: 50% !important; left: 50% !important;
    transform: translate(-50%,-50%) !important;
    text-align: center !important; pointer-events: none !important;
}
#pb-root .pb-donut-lbl .pb-pct { font-size: 24px !important; font-weight: 800 !important; line-height: 1 !important; }
#pb-root .pb-donut-lbl .pb-sub { font-size: 9px !important; color: #475569 !important; margin-top: 2px !important; line-height: 1.3 !important; }

#pb-root .pb-prog-body  { display: flex !important; align-items: center !important; gap: 24px !important; }
#pb-root .pb-prog-items { flex: 1 !important; display: flex !important; flex-direction: column !important; gap: 14px !important; }
#pb-root .pb-prog-row   { display: flex !important; flex-direction: column !important; gap: 5px !important; }
#pb-root .pb-prog-top   { display: flex !important; align-items: center !important; justify-content: space-between !important; }
#pb-root .pb-prog-lbl   { display: flex !important; align-items: center !important; gap: 6px !important; font-size: 12px !important; font-weight: 600 !important; }
#pb-root .pb-prog-val   { font-size: 12px !important; font-weight: 700 !important; color: #475569 !important; }
#pb-root .pb-bar-t      { height: 6px !important; background: #f1f5f9 !important; border-radius: 999px !important; overflow: hidden !important; }
#pb-root .pb-bar-f      { height: 100% !important; border-radius: 999px !important; display: block !important; }
#pb-root .pb-banner {
    margin-top: 16px !important; background: #fffbeb !important;
    border-radius: 10px !important; padding: 10px 14px !important;
    display: flex !important; align-items: center !important;
    gap: 8px !important; font-size: 12px !important;
    color: #92400e !important; font-weight: 500 !important;
}

#pb-root .pb-sc {
    background: #fff !important; border-radius: 14px !important;
    padding: 16px !important; box-shadow: 0 2px 12px rgba(15,23,42,.07) !important;
    display: flex !important; align-items: flex-start !important;
    justify-content: space-between !important;
    float: none !important; width: 100% !important;
}
#pb-root .pb-sc-v { font-size: 28px !important; font-weight: 800 !important; line-height: 1 !important; margin: 5px 0 3px !important; }
#pb-root .pb-sc-l { font-size: 12px !important; color: #475569 !important; font-weight: 500 !important; }
#pb-root .pb-sc-s { font-size: 11px !important; color: #94a3b8 !important; }
#pb-root .pb-sc-i {
    width: 36px !important; height: 36px !important; border-radius: 10px !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    flex-shrink: 0 !important;
}
#pb-root .pb-sc-i .material-icons-round { font-size: 19px !important; }
#pb-root .ic-b { background: #eff6ff !important; color: #3b82f6 !important; }
#pb-root .ic-g { background: #f0fdf4 !important; color: #22c55e !important; }
#pb-root .ic-p { background: #faf5ff !important; color: #a855f7 !important; }
#pb-root .ic-y { background: #fffbeb !important; color: #f59e0b !important; }

#pb-root .pb-tbl { width: 100% !important; border-collapse: collapse !important; font-size: 13px !important; }
#pb-root .pb-tbl th {
    text-align: left !important; padding: 10px 14px !important;
    font-size: 11px !important; font-weight: 700 !important; color: #475569 !important;
    text-transform: uppercase !important; letter-spacing: .05em !important;
    border-bottom: 2px solid #e2e8f0 !important; background: #f1f5f9 !important;
}
#pb-root .pb-tbl th:first-child { border-radius: 8px 0 0 8px !important; }
#pb-root .pb-tbl th:last-child  { border-radius: 0 8px 8px 0 !important; }
#pb-root .pb-tbl td {
    padding: 12px 14px !important; border-bottom: 1px solid #e2e8f0 !important;
    vertical-align: middle !important;
}
#pb-root .pb-tbl tr:last-child td { border-bottom: none !important; }
#pb-root .pb-tbl tr:hover td { background: #f8fafc !important; }

#pb-root .pb-k-cell  { display: flex !important; align-items: center !important; gap: 10px !important; }
#pb-root .pb-k-icon  { width: 34px !important; height: 34px !important; border-radius: 10px !important; background: #f1f5f9 !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 17px !important; flex-shrink: 0 !important; }
#pb-root .pb-k-name  { font-weight: 600 !important; font-size: 13px !important; }
#pb-root .pb-bdg-m   { display: inline-block !important; background: #eff6ff !important; color: #3b82f6 !important; font-size: 11px !important; font-weight: 600 !important; padding: 3px 8px !important; border-radius: 20px !important; }
#pb-root .pb-mini-b  { height: 6px !important; background: #e2e8f0 !important; border-radius: 999px !important; overflow: hidden !important; width: 80px !important; }
#pb-root .pb-mini-f  { height: 100% !important; border-radius: 999px !important; display: block !important; }
#pb-root .pb-pct-c   { font-weight: 700 !important; font-size: 13px !important; }

#pb-root .pb-kq-cell { display: flex !important; align-items: center !important; gap: 8px !important; }
#pb-root .pb-kq-dot  { width: 28px !important; height: 28px !important; border-radius: 8px !important; background: #f1f5f9 !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 13px !important; flex-shrink: 0 !important; }
#pb-root .pb-bdg     { display: inline-block !important; padding: 3px 10px !important; border-radius: 20px !important; font-size: 11px !important; font-weight: 700 !important; }
#pb-root .pb-bdg.ok   { background: #dcfce7 !important; color: #15803d !important; }
#pb-root .pb-bdg.fail { background: #fee2e2 !important; color: #b91c1c !important; }
#pb-root .pb-bdg.warn { background: #fff7ed !important; color: #c2410c !important; }
#pb-root .pb-bdg.none { background: #f1f5f9 !important; color: #94a3b8 !important; }
#pb-root .pb-nilai      { font-weight: 800 !important; font-size: 15px !important; }
#pb-root .pb-nilai.high { color: #15803d !important; }
#pb-root .pb-nilai.mid  { color: #ca8a04 !important; }
#pb-root .pb-nilai.low  { color: #b91c1c !important; }

#pb-root .pb-bot-row {
    display: grid !important;
    grid-template-columns: 1fr 330px !important;
    gap: 20px !important;
    margin-bottom: 20px !important;
    width: 100% !important;
}

#pb-root .pb-cert-wrap {
    background: linear-gradient(135deg,#0f172a,#1e3a5f) !important;
    border-radius: 12px !important; padding: 18px !important;
    position: relative !important; overflow: hidden !important;
    margin-bottom: 14px !important;
}
#pb-root .pb-cert-wrap::before {
    content: '' !important; position: absolute !important;
    top: -20px !important; right: -20px !important;
    width: 90px !important; height: 90px !important;
    background: rgba(245,158,11,.15) !important; border-radius: 50% !important;
}
#pb-root .pb-cert-wrap::after {
    content: '' !important; position: absolute !important;
    bottom: -25px !important; left: -10px !important;
    width: 70px !important; height: 70px !important;
    background: rgba(59,130,246,.12) !important; border-radius: 50% !important;
}
#pb-root .pb-cert-bd    { border: 1.5px solid rgba(245,158,11,.4) !important; border-radius: 8px !important; padding: 14px !important; position: relative !important; z-index: 1 !important; }
#pb-root .pb-cert-logo  { text-align: center !important; font-size: 20px !important; margin-bottom: 4px !important; }
#pb-root .pb-cert-ttl   { text-align: center !important; font-size: 10px !important; font-weight: 800 !important; color: #f59e0b !important; letter-spacing: .15em !important; }
#pb-root .pb-cert-sub   { text-align: center !important; font-size: 8px !important; color: rgba(255,255,255,.5) !important; letter-spacing: .08em !important; }
#pb-root .pb-cert-to    { text-align: center !important; font-size: 10px !important; color: rgba(255,255,255,.55) !important; margin: 8px 0 2px !important; }
#pb-root .pb-cert-nm    { text-align: center !important; font-size: 16px !important; font-weight: 800 !important; color: #f59e0b !important; }
#pb-root .pb-cert-desc  { text-align: center !important; font-size: 8.5px !important; color: rgba(255,255,255,.5) !important; line-height: 1.5 !important; margin-top: 6px !important; }
#pb-root .pb-cert-bl    { font-weight: 700 !important; color: rgba(255,255,255,.8) !important; }
#pb-root .pb-cert-ft    { display: flex !important; align-items: flex-end !important; justify-content: space-between !important; margin-top: 10px !important; }
#pb-root .pb-cert-dt    { font-size: 8px !important; color: rgba(255,255,255,.4) !important; }
#pb-root .pb-cert-sg    { font-size: 9px !important; font-weight: 700 !important; color: rgba(255,255,255,.55) !important; }
#pb-root .pb-cert-qr    { width: 26px !important; height: 26px !important; background: #fff !important; border-radius: 4px !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 16px !important; }
#pb-root .pb-ci-ttl     { font-size: 14px !important; font-weight: 700 !important; }
#pb-root .pb-ci-dt      { font-size: 12px !important; color: #475569 !important; margin-top: 3px !important; }
#pb-root .pb-cert-empty { text-align: center !important; padding: 28px 16px !important; color: #94a3b8 !important; font-size: 13px !important; line-height: 1.6 !important; }
#pb-root .pb-cert-empty .material-icons-round { font-size: 36px !important; display: block !important; margin-bottom: 8px !important; color: #cbd5e1 !important; }

#pb-root .pb-btn-dl {
    width: 100% !important; padding: 11px !important;
    background: #3b82f6 !important; color: #fff !important;
    border: none !important; border-radius: 10px !important;
    font-family: 'Plus Jakarta Sans', sans-serif !important;
    font-size: 13px !important; font-weight: 700 !important;
    cursor: pointer !important; text-decoration: none !important;
    display: flex !important; align-items: center !important;
    justify-content: center !important; gap: 6px !important;
    transition: background .2s !important;
}
#pb-root .pb-btn-dl:hover { background: #2563eb !important; }

#pb-root .pb-empty { text-align: center !important; padding: 32px 16px !important; color: #94a3b8 !important; font-size: 13px !important; }
#pb-root .pb-empty .material-icons-round { font-size: 36px !important; display: block !important; margin-bottom: 8px !important; color: #cbd5e1 !important; }

#pb-root .pb-quote { display: flex !important; align-items: center !important; gap: 10px !important; color: #475569 !important; font-size: 13px !important; padding: 4px 2px 20px !important; }

@media (max-width: 1080px) {
    #pb-root .pb-g2      { grid-template-columns: 1fr !important; }
    #pb-root .pb-g4      { grid-template-columns: repeat(2, 1fr) !important; }
    #pb-root .pb-bot-row { grid-template-columns: 1fr !important; }
}
@media (max-width: 640px) {
    #pb-root               { padding: 16px !important; }
    #pb-root .pb-g4        { grid-template-columns: 1fr 1fr !important; }
    #pb-root .pb-mini-b    { width: 55px !important; }
    #pb-root .pb-bot-row   { grid-template-columns: 1fr !important; }
}
</style>

<div id="pb-root">
    <div class="pb-top">
        <div>
            <h1>Progress Belajar</h1>
        </div>
    </div>

    <div class="pb-g2">
        <div class="pb-card">
            <div class="pb-card-hdr">
                <span class="pb-card-ttl">Ringkasan Progress</span>
                <span class="pb-card-meta">Update: <?= htmlspecialchars($last_update) ?></span>
            </div>
            <div class="pb-prog-body">
                <?php
                $circ   = round(2 * M_PI * 48);
                $filled = round($circ * $progress_total_pct / 100);
                ?>
                <div class="pb-donut-wrap">
                    <svg width="120" height="120" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="48" fill="none" stroke="#e2e8f0" stroke-width="10"/>
                        <circle cx="60" cy="60" r="48" fill="none" stroke="#3b82f6" stroke-width="10"
                                stroke-dasharray="<?= $filled ?> <?= $circ ?>"
                                stroke-linecap="round"/>
                    </svg>
                    <div class="pb-donut-lbl">
                        <div class="pb-pct"><?= $progress_total_pct ?>%</div>
                        <div class="pb-sub">Progress<br>Keseluruhan</div>
                    </div>
                </div>
                <div class="pb-prog-items">
                    <?php
                    $bars = [
                        ['icon'=>'description', 'label'=>'Modul',      'done'=>$modul_selesai,        'total'=>$total_modul_tersedia,  'color'=>'#3b82f6'],
                        ['icon'=>'task_alt',    'label'=>'Kuis',        'done'=>$kuis_dikerjakan,      'total'=>$total_kuis,   'color'=>'#22c55e'],
                        ['icon'=>'emoji_events','label'=>'Sertifikat',  'done'=>$sertifikat_diperoleh, 'total'=>1, 'color'=>'#f59e0b'],
                    ];
                    foreach ($bars as $b):
                        $bp = $b['total'] > 0 ? min(100, round($b['done'] / $b['total'] * 100)) : 0;
                    ?>
                    <div class="pb-prog-row">
                        <div class="pb-prog-top">
                            <div class="pb-prog-lbl">
                                <span class="material-icons-round" style="font-size:15px;color:<?= $b['color'] ?>"><?= $b['icon'] ?></span>
                                <?= $b['label'] ?>
                            </div>
                            <div class="pb-prog-val"><?= $b['done'] ?> / <?= $b['total'] ?></div>
                        </div>
                        <div class="pb-bar-t"><div class="pb-bar-f" style="width:<?= $bp ?>%;background:<?= $b['color'] ?>"></div></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if ($progress_total_pct >= 50): ?>
            <div class="pb-banner">⭐ Bagus! Kamu sudah melewati lebih dari setengah perjalanan!</div>
            <?php elseif ($progress_total_pct > 0): ?>
            <?php else: ?>
            <div class="pb-banner">📖 Mulai belajar modul pertamamu sekarang!</div>
            <?php endif; ?>
        </div>

        <div>
            <div class="pb-card-hdr" style="padding:0 2px;margin-bottom:12px">
                <span class="pb-card-ttl">Statistik</span>
            </div>
            <div class="pb-g4">
                <div class="pb-sc">
                    <div>
                        <div class="pb-sc-l">Modul Selesai</div>
                        <div class="pb-sc-v"><?= $modul_selesai ?></div>
                        <div class="pb-sc-s">Dari <?= $total_modul_tersedia ?> Modul</div>
                    </div>
                    <div class="pb-sc-i ic-b"><span class="material-icons-round">menu_book</span></div>
                </div>
                <div class="pb-sc">
                    <div>
                        <div class="pb-sc-l">Kuis Dikerjakan</div>
                        <div class="pb-sc-v"><?= $kuis_dikerjakan ?></div>
                        <div class="pb-sc-s">Dari <?= $total_kuis ?> Kuis</div>
                    </div>
                    <div class="pb-sc-i ic-g"><span class="material-icons-round">task_alt</span></div>
                </div>
                <div class="pb-sc">
                    <div>
                        <div class="pb-sc-l">Rata-rata Nilai</div>
                        <div class="pb-sc-v"><?= $kuis_dikerjakan > 0 ? $rata_nilai : '-' ?></div>
                        <div class="pb-sc-s">Dari semua kuis</div>
                    </div>
                    <div class="pb-sc-i ic-p"><span class="material-icons-round">bar_chart</span></div>
                </div>
                <div class="pb-sc">
                    <div>
                        <div class="pb-sc-l">Sertifikat</div>
                        <div class="pb-sc-v"><?= $sertifikat_diperoleh ?></div>
                        <div class="pb-sc-s">Muncul jika semua kuis lulus</div>
                    </div>
                    <div class="pb-sc-i ic-y"><span class="material-icons-round">emoji_events</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="pb-card" style="margin-bottom:20px">
        <div class="pb-card-hdr">
            <span class="pb-card-ttl">Progress per Kelompok Modul</span>
            <a href="modul.php" class="pb-lnk">Lihat Semua Modul</a>
        </div>
        <?php if (empty($progress_kelas)): ?>
        <div class="pb-empty">
            <span class="material-icons-round">folder_open</span>
            Belum ada modul yang tersedia.
        </div>
        <?php else: ?>
        <table class="pb-tbl">
            <thead>
                <tr>
                    <th>Kelompok</th>
                    <th>Modul Selesai</th>
                    <th>Kuis</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($progress_kelas as $kl): ?>
                <tr>
                    <td>
                        <div class="pb-k-cell">
                            <div class="pb-k-icon"><?= $kl['icon'] ?></div>
                            <span class="pb-k-name"><?= htmlspecialchars($kl['nama']) ?></span>
                        </div>
                    </td>
                    <td><span class="pb-bdg-m"><?= $kl['selesai'] ?> / <?= $kl['total'] ?> Modul</span></td>
                    <td>
                        <?php if ($kl['kuis_dikerjakan'] > 0): ?>
                            <div style="display:flex;flex-direction:column;gap:3px">
                                <span style="font-size:12px;font-weight:600;color:#0f172a">
                                    <?= $kl['kuis_dikerjakan'] ?> / <?= $kl['kuis_total'] ?> dikerjakan
                                </span>
                                <?php if ($kl['kuis_lulus'] > 0): ?>
                                    <span class="pb-bdg ok" style="width:fit-content">
                                        <?= $kl['kuis_lulus'] ?> lulus ✓
                                    </span>
                                <?php else: ?>
                                    <span class="pb-bdg fail" style="width:fit-content">Belum lulus</span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <span class="pb-bdg none">Belum ada</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div class="pb-mini-b">
                                <div class="pb-mini-f" style="width:<?= $kl['pct'] ?>%;background:<?= $kl['color'] ?>"></div>
                            </div>
                            <span class="pb-pct-c" style="color:<?= $kl['color'] ?>"><?= $kl['pct'] ?>%</span>
                        </div>
                    </td>
                    <td>
                        <?php
                        $p = $kl['pct'];
                        if ($p >= 100)    echo '<span class="pb-bdg ok">Selesai ✓</span>';
                        elseif ($p >= 75) echo '<span class="pb-bdg ok">On Track</span>';
                        elseif ($p >= 40) echo '<span class="pb-bdg warn">Sedang</span>';
                        elseif ($p > 0)   echo '<span class="pb-bdg fail">Perlu Usaha</span>';
                        else              echo '<span class="pb-bdg none">Belum Mulai</span>';
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <div class="pb-bot-row">
        <div class="pb-card">
            <div class="pb-card-hdr">
                <span class="pb-card-ttl">Riwayat Kuis</span>
            </div>
            <?php if (empty($riwayat_kuis)): ?>
            <div class="pb-empty">
                <span class="material-icons-round">quiz</span>
                Belum ada riwayat kuis.
            </div>
            <?php else: ?>
            <table class="pb-tbl">
                <thead>
                    <tr>
                        <th>Kuis</th>
                        <th>Nilai</th>
                        <th>Benar</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat_kuis as $kq):

                        $tq = (int)$kq['total_questions'];
                        $ca = (int)$kq['correct_answers'];

                        if ($tq > 0) {
                            $sc = round(($ca / $tq) * 100);
                        } else {
                            $sc = (int)$kq['score'];
                        }

                        $lls   = $sc >= 70;
                        $nc    = $sc >= 80 ? 'high' : ($sc >= 70 ? 'mid' : 'low');
                        $ico   = $lls ? '✅' : '❌';
                        $benar = $tq > 0 ? "$ca/$tq" : '-';
                    ?>
                    <tr>
                        <td>
                            <div class="pb-kq-cell">
                                <div class="pb-kq-dot"><?= $ico ?></div>
                                <span style="font-weight:600"><?= htmlspecialchars($kq['judul_kuis']) ?></span>
                            </div>
                        </td>
                        <td class="pb-nilai <?= $nc ?>"><?= $sc ?></td>
                        <td style="color:#475569;font-size:12px"><?= $benar ?></td>
                        <td><span class="pb-bdg <?= $lls ? 'ok' : 'fail' ?>"><?= $lls ? 'Lulus' : 'Tidak Lulus' ?></span></td>
                        <td style="color:#475569;font-size:12px"><?= fmt_tanggal($kq['attempted_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (count($riwayat_kuis) >= 10): ?>
            <a href="kuis.php" class="pb-lnk" style="display:inline-flex;align-items:center;gap:4px;margin-top:14px">
                <span class="material-icons-round" style="font-size:15px">add</span>
                Lihat Semua Riwayat Kuis
            </a>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="pb-card">
            <div class="pb-card-hdr">
                <span class="pb-card-ttl">Sertifikatmu</span>
                <a href="sertifikat.php" class="pb-lnk">Lihat Semua</a>
            </div>

            <?php if ($sertifikat): ?>
            <div class="pb-cert-wrap">
                <div class="pb-cert-bd">
                    <div class="pb-cert-logo">🏅</div>
                    <div class="pb-cert-ttl">SERTIFIKAT</div>
                    <div class="pb-cert-sub">PENYELESAIAN</div>
                    <div class="pb-cert-to">Diberikan kepada</div>
                    <div class="pb-cert-nm"><?= htmlspecialchars($user['nama']) ?></div>
                    <div class="pb-cert-desc">
                        Telah menyelesaikan dan lulus kuis<br>
                        <span class="pb-cert-bl">"<?= htmlspecialchars($sertifikat['judul_kuis']) ?>"</span><br>
                        dengan nilai <?= (int)$sertifikat['score'] ?>
                    </div>
                    <div class="pb-cert-ft">
                        <div>
                            <div class="pb-cert-dt"><?= fmt_tanggal($sertifikat['attempted_at']) ?></div>
                            <div class="pb-cert-sg">Admin Edu Lalin</div>
                        </div>
                        <div class="pb-cert-qr">🔲</div>
                    </div>
                </div>
            </div>
            <div style="margin-bottom:14px">
                <div class="pb-ci-ttl"><?= htmlspecialchars($sertifikat['judul_kuis']) ?></div>
                <div class="pb-ci-dt">Diperoleh pada <?= fmt_tanggal($sertifikat['attempted_at']) ?></div>
            </div>
            <a href="sertifikat.php?download=1" class="pb-btn-dl">
                <span class="material-icons-round" style="font-size:17px">download</span>
                Download Sertifikat
            </a>
            <?php else: ?>
            <div class="pb-cert-empty">
                <span class="material-icons-round">workspace_premium</span>
                Belum ada sertifikat.<br>Selesaikan dan luluskan semua kuis yang tersedia untuk mendapatkan sertifikat!
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="pb-quote">
        <span class="material-icons-round" style="color:#3b82f6;font-size:20px">format_quote</span>
        <em>"Belajar hari ini, aman di jalan nanti."</em>
    </div>
</div>

<?php include 'footer_common.php'; ?>