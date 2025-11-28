<?php
// 1. Load Config & Session
require_once "../config/database.php";
session_start();

// 2. Access Control
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// 3. Update Target URL
$redirectURL = BASE_URL . "/my_profile.php?section=account&tab=password";

// 4. Extract POST variables
$currentPassword = trim($_POST['currentpassword'] ?? '');
$newPassword1 = trim($_POST['newpassword1'] ?? '');
$newPassword2 = trim($_POST['newpassword2'] ?? '');

// 5. Validation
if (empty($currentPassword) || empty($newPassword1) || empty($newPassword2)) {
    $_SESSION['pass_msg'] = "Please fill in all fields.";
    $_SESSION['pass_type'] = "danger";
    header("Location: $redirectURL");
    exit();
}

if ($newPassword1 !== $newPassword2) {
    $_SESSION['pass_msg'] = "New passwords do not match.";
    $_SESSION['pass_type'] = "danger";
    header("Location: $redirectURL");
    exit();
}

if (strlen($newPassword1) < 6) {
    $_SESSION['pass_msg'] = "New password must be at least 6 characters long.";
    $_SESSION['pass_type'] = "danger";
    header("Location: $redirectURL");
    exit();
}

// 6. Fetch old hash
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

// 7. Verify current password
if (!password_verify($currentPassword, $storedHash)) {
    $_SESSION['pass_msg'] = "Current password is incorrect.";
    $_SESSION['pass_type'] = "danger";
    header("Location: $redirectURL");
    exit(); 
}

// 8. Update Password
$newHashedPassword = password_hash($newPassword1, PASSWORD_DEFAULT);

$query = $connection->prepare("UPDATE users SET password = ? WHERE user_id = ?");
$query->bind_param("si", $newHashedPassword, $userId);

if ($query->execute()) {
    $_SESSION['pass_msg'] = "Password updated successfully!";
    $_SESSION['pass_type'] = "success";
} else {
    $_SESSION['pass_msg'] = "Error updating password: " . $query->error;
    $_SESSION['pass_type'] = "danger";
}

$query->close();

// 9. Redirect
header("Location: $redirectURL");
exit();
?>
