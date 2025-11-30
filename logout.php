<?php

session_start();

if (isset($_SESSION['user_id'])) {
    // Remove all session variables
    $_SESSION = array();

    // If there's a session cookie, expire it
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, "/");
    }

    // Destroy the session
    session_destroy();
}

// 2. Expire our own login cookies (userID, username)
if (isset($_COOKIE['userID'])) {
    setcookie('userID', '', time() - 3600, "/");
}

if (isset($_COOKIE['username'])) {
    setcookie('username', '', time() - 3600, "/");
}

// (We keep cookie_consent so we still remember their choice)

// 3. Redirect to index
header("Location: index.php");
exit();

?>
