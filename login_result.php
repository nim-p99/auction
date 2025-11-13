<?php

require_once "database_connect.php";

$email = $_POST['email'];  
$password = $_POST['password'];
// TODO: Extract $_POST variables, check they're OK, and attempt to login.
// Notify user of success/failure and redirect/give navigation options.

// For now, I will just set session variables and redirect.

// SQL Query for CHECKING EMAIL and pw
 $checkinglogin = "SELECT * FROM users WHERE email = ? AND password = ?";
        $stmt = $connection->prepare($checkinglogin);
        $stmt->bind_param("ss", $email,$password); // 
        $stmt->execute();
        $result = $stmt->get_result();

         if ($result->num_rows > 0) {
            $user = $result->fetch_assoc(); //checking if it comes back with a row= email pw exists
         echo('<div class="text-center">You are now logged in! You will be redirected shortly.</div>');
        }else{
                echo "Email or password incorrect";}


session_start();
$_SESSION['logged_in'] = true;
$_SESSION['username'] = $user["username"];
$_SESSION['account_type'] = $user["account_type"]; // we need to add this column to pull from it

echo "Logged in as: " . $_SESSION["username"] .


// Redirect to index after 5 seconds
header("refresh:5;url=index.php");

?>