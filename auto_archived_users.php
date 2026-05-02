<?php
require_once "config.php";

$sql = "
UPDATE users
SET deleted_at = NOW(),
    archived_reason = 'inactive_1_year'
WHERE
    deleted_at IS NULL
    AND last_login IS NOT NULL
    AND last_login < NOW() - INTERVAL 1 YEAR
";

$conn->query($sql);

echo "Auto archive selesai: " . $conn->affected_rows . " user diarsip\n";
