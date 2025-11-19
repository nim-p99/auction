<?php

session_start();

unset($_SESSION['logged_in']);
# unset($_SESSION['account_type']);
# setcookie(session_name(), "", time() - 360);
session_destroy();
# echo "Logged out. <a href='login.php'>Log back in</a>";

// Redirect to index
header("Location: index.php");

?>
