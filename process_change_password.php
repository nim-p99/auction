<?php
require_once "database.php";
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to change your password.");
}

$userId = $_SESSION['user_id'];

// 1.Extract POST variables
$currentPassword = trim($_POST['currentpassword'] ?? '');
$newPassword1 = trim($_POST['newpassword1'] ?? '');
$newPassword2 = trim($_POST['newpassword2'] ?? '');

// 2. Check required fields
if (empty($currentPassword) || empty($newPassword1) || empty($newPassword2)) {
    die("Please fill in all fields.");
}

// 3. Check new passwords match
if ($newPassword1 !== $newPassword2) {
    die("New passwords do not match.");
}

// 4. Password length
if (strlen($newPassword1) < 6) {
    die("New password must be at least 6 characters long.");
}

// 5. Fetch old hashed password from DB
$query = $connection->prepare("SELECT password FROM users WHERE user_id = ?");
$query->bind_param("i", $userId);
$query->execute();
$query->bind_result($storedHash);
$query->fetch();
$query->close();

if (!$storedHash) {
    die("Error fetching account details.");
}

// 6. Verify current password
if (!password_verify($currentPassword, $storedHash)) {
    die("Current password is incorrect.");
}

// 7. Hash new password
$newHashedPassword = password_hash($newPassword1, PASSWORD_DEFAULT);

// 8. Update password
$query = $connection->prepare("UPDATE users SET password = ? WHERE user_id = ?");
$query->bind_param("si", $newHashedPassword, $userId);

if (!$query->execute()) {
    die("Error updating password: " . $query->error);
}

$query->close();

// 9. Success message
echo "Password updated successfully!";
?>
