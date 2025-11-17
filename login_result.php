<?php
require_once "database.php";

// check POST using isset, trim and empty 
// return false if invalid email 
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$password = mysqli_real_escape_string($connection, $_POST['password']);

// TODO: Extract $_POST variables, check they're OK, and attempt to login.
// Notify user of success/failure and redirect/give navigation options.

// For now, I will just set session variables and redirect.
// SQL Query for CHECKING EMAIL and pw
// will check 'password = SHA('$password')

$query = "SELECT user_id FROM users WHERE username = ? AND password = ?";
$user = mysqli_query($connection, $query);


?>
