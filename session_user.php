<?php
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT jenjang, kelas, bypass_access
        FROM users 
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->bind_result($jenjang_db, $kelas_db, $bypass_db);

    if ($stmt->fetch()) {
        $_SESSION['jenjang']        = strtolower($jenjang_db ?? '');
        $_SESSION['kelas']          = (int)($kelas_db ?? 0);
        $_SESSION['bypass_access']  = (int)($bypass_db ?? 0); // ← INI YANG PENTING
    } else {
        $_SESSION['jenjang']        = '';
        $_SESSION['kelas']          = 0;
        $_SESSION['bypass_access']  = 0;
    }

    $stmt->close();
}