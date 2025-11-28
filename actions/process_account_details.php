<?php
// 1. Start Session & Config
session_start();
require_once "../config/database.php";

// 2. Access Control
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 3. Process Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Extract Data
    $new_first = trim($_POST['firstname'] ?? '');
    $new_last = trim($_POST['lastname'] ?? '');
    $new_user = trim($_POST['username'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $new_phone = trim($_POST['phonenumber'] ?? '');

    // Basic Validation
    if (empty($new_first) || empty($new_last) || empty($new_user) || empty($new_email)) {
        $_SESSION['details_msg'] = "Name, Username, and Email are required.";
        $_SESSION['details_type'] = "danger";
        header("Location: " . BASE_URL . "/my_profile.php?section=account&tab=details");
        exit();
    }

    // Update Query
    $query = $connection->prepare("
        UPDATE users
        SET first_name = ?, family_name = ?, username = ?, email = ?, phone_number = ?
        WHERE user_id = ?
    ");
    
    // Convert empty phone to NULL 
    $phone_param = empty($new_phone) ? null : $new_phone;

    $query->bind_param("sssssi", $new_first, $new_last, $new_user, $new_email, $phone_param, $user_id);

    if ($query->execute()) {
        $_SESSION['details_msg'] = "Account details updated successfully.";
        $_SESSION['details_type'] = "success";
        
        $_SESSION['username'] = $new_user;
    } else {
        $_SESSION['details_msg'] = "Update failed: " . $connection->error;
        $_SESSION['details_type'] = "danger";
    }

    $query->close();
}

// 4. Redirect
header("Location: " . BASE_URL . "/my_profile.php?section=account&tab=details");
exit();
?>
