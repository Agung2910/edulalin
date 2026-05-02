<?php
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$flash = "";
$flash_type = "success";

$stmt = $conn->prepare("
    SELECT nama, email, asal_sekolah, kelas, jenjang, password,
           no_telp, tanggal_lahir
    FROM users
    WHERE id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

$profile = $profile ?: [];
$profile = array_merge([
    'nama'           => '',
    'email'          => '',
    'password'       => '',
    'no_telp'        => '',
    'tanggal_lahir'  => '',
    'jenjang'        => '',
    'kelas'          => '',
    'asal_sekolah'   => ''
], $profile);

if (!empty($profile['tanggal_lahir'])) {
    $profile['tanggal_lahir'] = date('Y-m-d', strtotime($profile['tanggal_lahir']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {

    $nama          = trim($_POST['nama'] ?? '');
    $asal_sekolah  = trim($_POST['asal_sekolah'] ?? '');
    $kelas         = trim($_POST['kelas'] ?? '');
    $jenjang       = $_POST['jenjang'] ?? '';
    $no_telp       = trim($_POST['no_telp'] ?? '');
    $tanggal_lahir = $_POST['tanggal_lahir'] ?: null;

    if ($nama === '' || strlen($nama) < 3) {
        $flash = "Nama minimal 3 karakter";
        $flash_type = "error";
    } else {
        $stmt = $conn->prepare("
            UPDATE users SET
                nama = ?,
                asal_sekolah = ?,
                kelas = ?,
                jenjang = ?,
                no_telp = ?,
                tanggal_lahir = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            "ssssssi",
            $nama,
            $asal_sekolah,
            $kelas,
            $jenjang,
            $no_telp,
            $tanggal_lahir,
            $user_id
        );

        if ($stmt->execute()) {
            header("Location: setting.php");
            exit;
        } else {
            $flash = "Gagal menyimpan perubahan";
            $flash_type = "error";
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {

    $old     = $_POST['old_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($new) < 8) {
        $flash = "Password baru minimal 8 karakter";
        $flash_type = "error";
    } elseif ($new !== $confirm) {
        $flash = "Konfirmasi password tidak sama";
        $flash_type = "error";
    } elseif (!password_verify($old, $profile['password'])) {
        $flash = "Password lama salah";
        $flash_type = "error";
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $user_id);
        $stmt->execute();
        $stmt->close();

        $flash = "Password berhasil diganti";
        $flash_type = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Pengaturan Akun</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.settings-wrapper {
    max-width: 1100px;
    margin: 40px auto;
    display: flex;
    gap: 24px;
}
.settings-sidebar {
    width: 240px;
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,.05);
}
.settings-sidebar h3 {
    font-size: 16px;
    margin-bottom: 12px;
}
.settings-sidebar a {
    display: block;
    padding: 10px 12px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    color: #111;
    margin-bottom: 6px;
}
.settings-sidebar a.active,
.settings-sidebar a:hover {
    background: #2563eb;
    color: #fff;
}
.settings-content {
    flex: 1;
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,.05);
}
.section-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 16px;
}
.form-group label {
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}
.form-group input,
.form-group select {
    width: 100%;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
}
</style>
</head>

<body>
<?php include 'header_common.php'; ?>
<div class="settings-wrapper">

    <aside class="settings-sidebar">
        <h3>⚙ Pengaturan</h3>
        <a href="#profil" class="active">Profil Saya</a>
        <a href="#password">Ganti Password</a>
        <a href="index.php">← Kembali</a>
    </aside>

    <main class="settings-content">

        <?php if ($flash): ?>
            <div class="alert-<?= $flash_type ?>">
                <?= htmlspecialchars($flash) ?>
            </div>
        <?php endif; ?>

        <section id="profil">
            <div class="section-title">Profil Saya</div>
            <form method="post">
                <input type="hidden" name="update_profile" value="1">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama"
                           value="<?= htmlspecialchars($profile['nama']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email"
                           value="<?= htmlspecialchars($profile['email']) ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Nomor HP</label>
                    <input type="tel" name="no_telp"
                           value="<?= htmlspecialchars($profile['no_telp']) ?>"
                           placeholder="08xxxxxxxxxx">
                </div>

                <div class="form-group">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir"
                           value="<?= htmlspecialchars($profile['tanggal_lahir']) ?>">
                </div>

                <div class="form-group">
                    <label>Jenjang</label>
                    <select name="jenjang">
                        <option value="">-- Pilih --</option>
                        <?php foreach (['SD','SMP','SMA','SMK'] as $j): ?>
                            <option value="<?= $j ?>"
                                <?= $profile['jenjang'] === $j ? 'selected' : '' ?>>
                                <?= $j ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Kelas</label>
                    <input type="text" name="kelas"
                           value="<?= htmlspecialchars($profile['kelas']) ?>">
                </div>

                <div class="form-group">
                    <label>Asal Sekolah</label>
                    <input type="text" name="asal_sekolah"
                           value="<?= htmlspecialchars($profile['asal_sekolah']) ?>">
                </div>

                <button class="btn btn-primary">Simpan Profil</button>
            </form>
        </section>

        <br><br>

        <section id="password">
            <div class="section-title">Ganti Password</div>
            <form method="post">
                <input type="hidden" name="change_password" value="1">
                <div class="form-group">
                    <label>Password Lama</label>
                    <input type="password" name="old_password" required>
                </div>

                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" name="new_password" required>
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" required>
                </div>

                <button class="btn btn-primary">Ganti Password</button>
            </form>
        </section>
    </main>
</div>
</body>
</html>