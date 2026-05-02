<?php
require_once "../config.php";
require_admin(); 

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    echo "<p>User tidak valid</p>";
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        id,
        nama,
        email,
        no_telp,
        asal_sekolah,
        kelas,
        jenjang,
        tanggal_lahir,
        role,
        created_at,
        last_login,
        is_verified
    FROM users
    WHERE id = ? AND deleted_at IS NULL
    LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "<p>User tidak ditemukan atau sudah dihapus</p>";
    exit;
}
?>

<style>
.detail-wrapper {
    font-size:14px;
}
.detail-grid {
    display:grid;
    grid-template-columns: 160px 1fr;
    gap:10px 14px;
}
.detail-label {
    color:#6b7280;
    font-weight:600;
}
.detail-value {
    color:#111827;
}
.detail-badge {
    display:inline-block;
    padding:4px 10px;
    border-radius:999px;
    font-size:11px;
    font-weight:600;
}
.badge-admin { background:#fee2e2; color:#991b1b; }
.badge-guru  { background:#fef3c7; color:#92400e; }
.badge-user  { background:#dbeafe; color:#1e40af; }
.badge-ok    { background:#dcfce7; color:#166534; }
.badge-no    { background:#fee2e2; color:#991b1b; }
</style>

<div class="detail-wrapper">
    <h3 style="margin-bottom:12px;">📋 Detail Pengguna</h3>

    <div class="detail-grid">
        <div class="detail-label">Nama</div>
        <div class="detail-value"><?= htmlspecialchars($user['nama']) ?></div>

        <div class="detail-label">Email</div>
        <div class="detail-value"><?= htmlspecialchars($user['email']) ?></div>

        <div class="detail-label">No. Telp</div>
        <div class="detail-value"><?= $user['no_telp'] ?: '-' ?></div>

        <div class="detail-label">Sekolah</div>
        <div class="detail-value"><?= $user['asal_sekolah'] ?: '-' ?></div>

        <div class="detail-label">Kelas</div>
        <div class="detail-value"><?= $user['kelas'] ?: '-' ?></div>

        <div class="detail-label">Jenjang</div>
        <div class="detail-value"><?= $user['jenjang'] ?: '-' ?></div>

        <div class="detail-label">Tanggal Lahir</div>
        <div class="detail-value">
            <?= $user['tanggal_lahir'] ? date('d M Y', strtotime($user['tanggal_lahir'])) : '-' ?>
        </div>

        <div class="detail-label">Role</div>
        <div class="detail-value">
            <?php
            $roleClass = 'badge-user';
            if ($user['role'] === 'admin') $roleClass = 'badge-admin';
            elseif ($user['role'] === 'guru') $roleClass = 'badge-guru';
            ?>
            <span class="detail-badge <?= $roleClass ?>">
                <?= strtoupper($user['role']) ?>
            </span>
        </div>

        <div class="detail-label">Status Akun</div>
        <div class="detail-value">
            <?php if ($user['is_verified']): ?>
                <span class="detail-badge badge-ok">Terverifikasi</span>
            <?php else: ?>
                <span class="detail-badge badge-no">Belum Verifikasi</span>
            <?php endif; ?>
        </div>

        <div class="detail-label">Terdaftar</div>
        <div class="detail-value"><?= date('d M Y', strtotime($user['created_at'])) ?></div>

        <div class="detail-label">Login Terakhir</div>
        <div class="detail-value">
            <?= $user['last_login']
                ? date('d M Y H:i', strtotime($user['last_login']))
                : 'Belum login'; ?>
        </div>
    </div>
</div>
