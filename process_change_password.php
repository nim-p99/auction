<?php
require_once "database.php";
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$userId = $_SESSION['user_id'];
$redirectURL = "my_profile.php?section=account&tab=password";

// 1.Extract POST variables
$currentPassword = trim($_POST['currentpassword'] ?? '');
$newPassword1 = trim($_POST['newpassword1'] ?? '');
$newPassword2 = trim($_POST['newpassword2'] ?? '');

// 2. Check required fields
if (empty($currentPassword) || empty($newPassword1) || empty($newPassword2)) {
  $_SESSION['pass_msg'] = "Please fill in all fields.";
  $_SESSION['pass_type'] = "danger"; // Red
  header("Location: $redirectURL");
  exit();
}

// 3. Check new passwords match
if ($newPassword1 !== $newPassword2) {
    $_SESSION['pass_msg'] = "New passwords do not match.";
    $_SESSION['pass_type'] = "danger";
    header("Location: $redirectURL");
    exit();
}

// 4. Password length
if (strlen($newPassword1) < 6) {
    $_SESSION['pass_msg'] = "New password must be at least 6 characters long.";
    $_SESSION['pass_type'] = "danger";
    header("Location: $redirectURL");
    exit();
}

// 5. Fetch old hashed password from DB
$query = $connection->prepare("SELECT password FROM users WHERE user_id = ?");
$query->bind_param("i", $userId);
$query->execute();
$query->bind_result($storedHash);
$query->fetch();
$query->close();

if (!$storedHash) {
    $_SESSION['pass_msg'] = "Error fetching account details.";
    $_SESSION['pass_type'] = "danger";
    header("Location: $redirectURL");
    exit();
}

// 6. Verify current password
if (!password_verify($currentPassword, $storedHash)) {
    $_SESSION['pass_msg'] = "Current password is incorrect.";
    $_SESSION['pass_type'] = "danger";
    header("Location: $redirectURL");
    exit(); 
}

// 7. Hash new password
$newHashedPassword = password_hash($newPassword1, PASSWORD_DEFAULT);

// 8. Update password
$query = $connection->prepare("UPDATE users SET password = ? WHERE user_id = ?");
$query->bind_param("si", $newHashedPassword, $userId);

if ($query->execute()) {
    $_SESSION['pass_msg'] = "Password updated successfully!";
    $_SESSION['pass_type'] = "success"; // Green
} else {
    $_SESSION['pass_msg'] = "Error updating password: " . $query->error;
    $_SESSION['pass_type'] = "danger";
}

$query->close();


// 9. Redirect back to profile
header("Location: $redirectURL");
exit();
?>
