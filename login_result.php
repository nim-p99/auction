<?php
session_start();
require_once "database.php";



$email = $_POST['email'];
$password = $_POST['password'];

// SQL Query for CHECKING EMAIL and pw
 $checkinglogin = "SELECT * FROM users WHERE email = ? AND password = ?";
        $stmt = $connection->prepare($checkinglogin);
        $stmt->bind_param("ss", $email,$password); // 
        $stmt->execute();
        $result = $stmt->get_result();

         if ($result->num_rows > 0) {
         $user = $result->fetch_assoc();
        

    
        //sets session after sucessful 
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user["username"];
        $_SESSION['first_name']=$user["first_name"];
        $_SESSION['user_id'] = $user["user_id"];
        $_SESSION['account_type'] = $user["account_type"];


        echo '<div class="text-center">You are now logged in! You will be redirected shortly.</div>';
        echo "<p class='text-center'>Logged in as: " . $_SESSION["username"] . "</p>";

        // Redirect to browse page after 3 seconds
        header("refresh:3;url=my_profile.php");

    } else {
        echo '<div class="text-center">Email or password incorrect.</div>';
        header("refresh:3;url=browse.php");
    }



$stmt->close();
$connection->close();




?>
