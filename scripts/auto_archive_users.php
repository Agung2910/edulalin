<?php
require_once __DIR__ . '/../config.php';

$conn->query("
    UPDATE users
    SET deleted_at = NOW(),
        archived_reason = 'inactive_1_year'
    WHERE
        deleted_at IS NULL
        AND last_login IS NOT NULL
        AND last_login < NOW() - INTERVAL 1 YEAR
");

echo "Auto archive selesai\n";
