<?php

// Ambil data user kalau sudah login
if (isset($_SESSION['user_id']) && isset($conn)) {
    $stmt = $conn->prepare("SELECT nama, tanggal_lahir FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$currentPage = basename($_SERVER['PHP_SELF']);

function isActive($pages = []) {
    global $currentPage;
    return in_array($currentPage, $pages) ? 'active' : '';
}
?>

<nav class="main-nav">
    <div class="nav-inner">
        <div class="nav-left">
            <a href="index.php" class="brand-link">
                <img src="assets/img/logo-jr.png" class="nav-logo">
                <img src="assets/img/logo-edu.png" class="nav-logo">
            </a>
            <a href="index.php"
               class="nav-link <?= isActive(['index.php']) ?>">
               Beranda
            </a>
            <a href="profil_program.php"
               class="nav-link <?= isActive(['profil_program.php']) ?>">
               Tentang Kami
            </a>
            <a href="kontak.php"
               class="nav-link <?= isActive(['kontak.php']) ?>">
               Kontak
            </a>
        </div>
        <div class="nav-right">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="welcome-text">
                    Halo, <?= htmlspecialchars($_SESSION['nama']?? '')?>
                </span>
                <a href="setting.php" class="icon-btn">⚙</a>
                <a href="logout.php" class="btn btn-outline">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline">Masuk</a>
                <a href="register.php" class="btn btn-primary">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</nav>