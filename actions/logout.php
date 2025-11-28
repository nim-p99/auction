<?php
session_start();

// Load Config (to get BASE_URL)
require_once "../config/database.php";

// Destroy Session
unset($_SESSION['logged_in']);
unset($_SESSION['account_type']);
unset($_SESSION['user_id']);
$_SESSION = array();
session_destroy();

// Redirect to Index
header("Location: " . BASE_URL . "/index.php");
exit();
?>
