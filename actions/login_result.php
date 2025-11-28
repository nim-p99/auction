<?php
// 1. Start Session & Connect DB
session_start();
require_once "../config/database.php";

// Helper function to handle errors and redirect back
function login_error($msg) {
    $_SESSION['login_error'] = $msg;
    // Redirect back to login page
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

// 2. Extract Data
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// 3. Validate Inputs
if (empty($email) || empty($password)) {
    login_error("Please fill in all fields.");
}

// 4. Fetch User
$query = $connection->prepare("SELECT user_id, acc_active, password FROM users WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
$query->close();

// Check if user exists
if (!$user) {
    login_error("Invalid email or password.");
}

// Check if suspended
if ($user['acc_active'] == 0) {
    login_error("Your account is suspended. Please contact support.");
}

// 5. Verify Password
if (!password_verify($password, $user['password'])) {
    login_error("Invalid email or password.");
}

// 6. Success: Set Session Variables
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['logged_in'] = true;

// 7. Redirect based on Role
// Admin (User ID 1) goes to Admin Dashboard
if ($_SESSION['user_id'] == 1) {
    header("Location: " . BASE_URL . "/admin/admin_listings.php");
} 
// Everyone else goes to Browse
else {
    header("Location: " . BASE_URL . "/browse.php");
}
exit();
?>
