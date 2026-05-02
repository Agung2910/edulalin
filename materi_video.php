<?php
$url = $_GET['url'] ?? '';

$url = str_replace("watch?v=", "embed/", $url);
$url = str_replace("youtu.be/", "youtube.com/embed/", $url);
?>
<!doctype html>
<html>
<body>

<iframe width="100%" height="480"
src="<?php echo htmlspecialchars($url); ?>"
frameborder="0" allowfullscreen></iframe>

</body>
</html>
