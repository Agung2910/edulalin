<?php
require_once "config.php";
require_login();

$user_id = (int)($_SESSION['user_id'] ?? 0);
if ($user_id <= 0) {
    header("Location: login.php");
    exit;
}

// Ambil data user
$stmt = $conn->prepare("SELECT id, nama, email, role, jenjang, kelas, asal_sekolah FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$nama_user    = $user['nama'] ?? 'Peserta';
$jenjang_user = $user['jenjang'] ?? '';
$kelas_user   = $user['kelas'] ?? '';
$kelas_angka  = (int) preg_replace('/[^0-9]/', '', (string)$kelas_user);

// Cek kolom tanggal di quiz_attempts, biar aman kalau nama kolom beda
function table_has_column(mysqli $conn, string $table, string $column): bool {
    $sql = "SELECT COUNT(*) AS total
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $table, $column);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return ((int)($row['total'] ?? 0)) > 0;
}

$attempt_date_col = table_has_column($conn, 'quiz_attempts', 'attempted_at') ? 'attempted_at' : 'created_at';

// Ambil semua kuis yang TERSEDIA buat user, ngikutin filter progress.php
$sql_kuis_tersedia = "
    SELECT DISTINCT
        q.id AS quiz_id,
        COALESCE(q.judul, m.judul, 'Kuis') AS judul_kuis,
        m.id AS modul_id,
        m.judul AS judul_modul,
        m.kategori,
        m.jenjang,
        m.kelas
    FROM quiz q
    JOIN modul m ON m.id = q.materi_id
    WHERE m.is_active = 1
      AND (
            (m.kategori = 'sekolah' AND (m.jenjang = ? OR ? = ''))
            OR m.kategori = 'sim'
          )
    ORDER BY m.kategori, m.jenjang, m.kelas, m.id, q.id
";
$stmt = $conn->prepare($sql_kuis_tersedia);
$stmt->bind_param("ss", $jenjang_user, $jenjang_user);
$stmt->execute();
$res = $stmt->get_result();

$kuis_tersedia = [];
while ($row = $res->fetch_assoc()) {
    // Filter kelas SMA/SMK: cuma kelas user
    if (
        in_array(strtoupper((string)($row['jenjang'] ?? '')), ['SMA', 'SMK'], true) &&
        !empty($kelas_user) &&
        !empty($row['kelas']) &&
        (string)$row['kelas'] !== (string)$kelas_user
    ) {
        continue;
    }

    // Filter SIM: cuma kelas 12 ke atas
    if (($row['kategori'] ?? '') === 'sim' && $kelas_angka < 12) {
        continue;
    }

    $kuis_tersedia[(int)$row['quiz_id']] = $row;
}
$stmt->close();

$total_kuis = count($kuis_tersedia);

if ($total_kuis <= 0) {
    die("Belum ada kuis yang tersedia untuk akun ini.");
}

// Ambil attempt TERBAIK user per kuis
// Catatan: ini pakai qa.quiz_id = q.id. Kalau di DB lama quiz_id ternyata berisi modul_id,
// query fallback di bawah akan bantu baca juga.
$sql_attempt = "
    SELECT
        qa.quiz_id,
        MAX(qa.score) AS best_score,
        MAX(qa.$attempt_date_col) AS last_attempt
    FROM quiz_attempts qa
    WHERE qa.user_id = ?
    GROUP BY qa.quiz_id
";
$stmt = $conn->prepare($sql_attempt);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$attempt_map = [];
while ($row = $res->fetch_assoc()) {
    $attempt_map[(int)$row['quiz_id']] = [
        'best_score'   => (int)($row['best_score'] ?? 0),
        'last_attempt' => $row['last_attempt'] ?? null,
    ];
}
$stmt->close();

// Fallback kalau quiz_attempts.quiz_id di proyek lama ternyata nyimpan modul_id, bukan quiz.id
foreach ($kuis_tersedia as $qid => $q) {
    if (isset($attempt_map[$qid])) {
        continue;
    }

    $modul_id = (int)$q['modul_id'];
    if (isset($attempt_map[$modul_id])) {
        $attempt_map[$qid] = $attempt_map[$modul_id];
    }
}

$kuis_dikerjakan = 0;
$kuis_lulus = 0;
$nilai_total = 0;
$last_ts = 0;
$belum = [];
$belum_lulus = [];

foreach ($kuis_tersedia as $qid => $q) {
    if (!isset($attempt_map[$qid])) {
        $belum[] = $q['judul_kuis'];
        continue;
    }

    $kuis_dikerjakan++;
    $score = (int)$attempt_map[$qid]['best_score'];
    $nilai_total += $score;

    if ($score >= 70) {
        $kuis_lulus++;
    } else {
        $belum_lulus[] = $q['judul_kuis'] . " (" . $score . ")";
    }

    $ts = strtotime((string)$attempt_map[$qid]['last_attempt']);
    if ($ts && $ts > $last_ts) {
        $last_ts = $ts;
    }
}

$semua_dikerjakan = ($kuis_dikerjakan >= $total_kuis);
$semua_lulus      = ($kuis_lulus >= $total_kuis);
$boleh_sertifikat = ($semua_dikerjakan && $semua_lulus);
$rata_nilai       = $kuis_dikerjakan > 0 ? round($nilai_total / $kuis_dikerjakan) : 0;
$tanggal_lulus    = $last_ts > 0 ? date('d-m-Y', $last_ts) : date('d-m-Y');

if (!$boleh_sertifikat) {
    $msg  = "Sertifikat belum bisa dibuat.\n\n";
    $msg .= "Kuis dikerjakan: " . $kuis_dikerjakan . " / " . $total_kuis . "\n";
    $msg .= "Kuis lulus: " . $kuis_lulus . " / " . $total_kuis . "\n\n";

    if (!empty($belum)) {
        $msg .= "Kuis belum dikerjakan:\n- " . implode("\n- ", array_slice($belum, 0, 10)) . "\n\n";
    }

    if (!empty($belum_lulus)) {
        $msg .= "Kuis belum lulus minimal 70:\n- " . implode("\n- ", array_slice($belum_lulus, 0, 10)) . "\n";
    }

    die(nl2br(htmlspecialchars($msg)));
}

if (!file_exists("fpdf.php")) {
    die("File fpdf.php tidak ditemukan. Download dari fpdf.org lalu taruh di folder yang sama dengan sertifikat.php.");
}
require_once "fpdf.php";

class SertifikatPDF extends FPDF {
    function Header() {
        // border luar
        $this->SetDrawColor(30, 58, 95);
        $this->SetLineWidth(1.5);
        $this->Rect(10, 10, 277, 190);

        // border dalam
        $this->SetDrawColor(245, 158, 11);
        $this->SetLineWidth(0.7);
        $this->Rect(16, 16, 265, 178);
    }
}

$pdf = new SertifikatPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

// Judul
$pdf->SetFont('Arial', 'B', 30);
$pdf->SetTextColor(15, 23, 42);
$pdf->Ln(22);
$pdf->Cell(0, 14, 'SERTIFIKAT KELULUSAN', 0, 1, 'C');

$pdf->SetFont('Arial', '', 13);
$pdf->SetTextColor(71, 85, 105);
$pdf->Cell(0, 9, 'Website Edukasi Lalu Lintas', 0, 1, 'C');

$pdf->Ln(14);
$pdf->SetFont('Arial', '', 15);
$pdf->SetTextColor(71, 85, 105);
$pdf->Cell(0, 10, 'Diberikan kepada:', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 24);
$pdf->SetTextColor(245, 158, 11);
$pdf->Cell(0, 14, $nama_user, 0, 1, 'C');

$pdf->Ln(6);
$pdf->SetFont('Arial', '', 13);
$pdf->SetTextColor(15, 23, 42);

$deskripsi = "Telah menyelesaikan seluruh kuis yang tersedia dan memenuhi syarat kelulusan dengan nilai minimal 70 pada setiap kuis.";
$pdf->MultiCell(230, 8, $deskripsi, 0, 'C');

$pdf->Ln(6);
$pdf->SetFont('Arial', 'B', 13);
$pdf->Cell(0, 8, 'Ringkasan Hasil', 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, 'Total Kuis: ' . $total_kuis . ' | Dikerjakan: ' . $kuis_dikerjakan . ' | Lulus: ' . $kuis_lulus . ' | Rata-rata Nilai: ' . $rata_nilai, 0, 1, 'C');

if (!empty($jenjang_user) || !empty($kelas_user)) {
    $info_kelas = trim(($jenjang_user ?: '') . ' ' . ($kelas_user ?: ''));
    $pdf->Cell(0, 8, 'Kategori Peserta: ' . $info_kelas, 0, 1, 'C');
}

$pdf->Ln(18);
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(71, 85, 105);
$pdf->Cell(95, 8, 'Tanggal Kelulusan: ' . $tanggal_lulus, 0, 0, 'C');
$pdf->Cell(105, 8, '', 0, 0, 'C');
$pdf->Cell(80, 8, 'Admin Edu Lalin', 0, 1, 'C');

$pdf->Ln(14);
$pdf->Cell(95, 8, '', 0, 0, 'C');
$pdf->Cell(105, 8, '', 0, 0, 'C');
$pdf->Cell(80, 8, '____________________', 0, 1, 'C');

$pdf->SetY(178);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(100, 116, 139);
$pdf->Cell(0, 8, 'Belajar hari ini, aman di jalan nanti.', 0, 1, 'C');

$filename = 'sertifikat-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', strtolower($nama_user)) . '.pdf';
$pdf->Output('I', $filename);
exit;
