<?php
require_once "config.php";
require_login();

$user_id  = $_SESSION['user_id'];
$modul_id = (int)($_GET['modul_id'] ?? 0);
if ($modul_id <= 0) {
    header("Location: modul.php");
    exit;
}

$stmt = $conn->prepare("SELECT p.skor,m.judul,u.nama 
                        FROM progress p
                        JOIN modul m ON p.modul_id=m.id
                        JOIN users u ON p.user_id=u.id
                        WHERE p.user_id=? AND p.modul_id=? LIMIT 1");
$stmt->bind_param("ii",$user_id,$modul_id);
$stmt->execute();
$stmt->bind_result($skor,$judul_modul,$nama_user);

if (!$stmt->fetch()) {
    $stmt->close();
    die("Belum ada progress untuk modul ini.");
}
$stmt->close();

if ($skor < 5) {
    die("Skor belum memenuhi syarat sertifikat (min 5).");
}

if (!file_exists("fpdf.php")) {
    die("File fpdf.php tidak ditemukan. Download dari http://www.fpdf.org dan taruh di folder ini.");
}
require_once "fpdf.php";

$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',26);
$pdf->Cell(0,20,'SERTIFIKAT KELULUSAN',0,1,'C');
$pdf->SetFont('Arial','',16);
$pdf->Cell(0,10,'Diberikan kepada:',0,1,'C');
$pdf->SetFont('Arial','B',20);
$pdf->Cell(0,15,$nama_user,0,1,'C');
$pdf->SetFont('Arial','',14);
$pdf->MultiCell(0,8,"Atas keberhasilan menyelesaikan modul:\n\"".$judul_modul."\"\nDengan skor: ".$skor,0,'C');
$pdf->Ln(10);
$pdf->Cell(0,10,'Tanggal: '.date('d-m-Y'),0,1,'C');
$pdf->Output('I','sertifikat.pdf');
exit;
