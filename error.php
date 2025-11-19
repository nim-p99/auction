<?php
$error_id = $_GET['error_id'] ?? 0;

$message = '';
switch ($error_id) {
    case 1:
        $message = "There was a problem with this auction. Return to browse by clicking here.";
        break;
    case 2:
        $message = "You do not have permission to access this page.";
        break;
    default:
        $message = "An unknown error occurred.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Error</title>
</head>
<body>
    <h2>Error</h2>
    <p><?php echo htmlspecialchars($message); ?></p>
    <a href="/browse.php">Return to browse</a>
</body>
</html>
