<?php
session_start();
require_once "database.php";

// 1. extract POST data 
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// 2. validate inputs
if (empty($email) || empty($password)) {
  die("Please fill in all fields.");
}

// 3. fetch user from database by their email
// (prepared statements protect from SQL injection the best)
$query = $connection->prepare("SELECT user_id, acc_active, password, username FROM users WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

if(!$user) {
  die("Invalid email or password...");
}
if($user['acc_active'] == 0){
  //die("Your Account is suspended, please contact support for assistance.");
  $_SESSION['user_id'] = $user['user_id'];
  $_SESSION['logged_in'] = true;
  $_SESSION['is_suspended'] = true;


// Ensure any persistent login cookies are cleared
  if (isset($_COOKIE['userID'])) {
      setcookie('userID', '', time() -3600, "/");
  }
  if (isset($_COOKIE['username'])) {
      setcookie('username', '', time() -3600, "/");
  }

  header("Location: account_suspended.php");
  exit();

}
// 4. verify password
if (!password_verify($password, $user['password'])) {
  die("Invalid email or password.");
}

// 5. store user_id, logged_in and account_type in session 
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['logged_in'] = true;

// If user has consented, set optional login cookies (30 days)
if (isset($_COOKIE['cookie_consent']) && $_COOKIE['cookie_consent'] === 'accept') {
    setcookie('userID', $user['user_id'], time() + (60 * 60 * 24 * 30), "/");
    setcookie('username', $user['username'], time() + (60 * 60 * 24 * 30), "/");
}


// 6. success message and redirect
if ($_SESSION['user_id'] == 1) {
  echo "<meta http-equiv='refresh' content='2;url=admin_listings.php'>";
  echo "<p>If you are not redirected automatically, <a href='admin_listings.php'>click here</a></p>";
}
else {
  echo "Login successful! Redirecting to homepage ...";

  echo "<meta http-equiv='refresh' content='2;url=browse.php'>";
  echo "<p>If you are not redirected automatically, <a href='browse.php'>click here</a></p>";
}
$query->close();
?>
