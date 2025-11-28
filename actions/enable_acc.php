<?php
// 1. Start Session & Connect DB
session_start();
require_once "../config/database.php";

// 2. Access Control (Admin Only)
if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: " . BASE_URL . "/browse.php");
    exit();
}

// 3. Process Request
if (isset($_POST['user_id']) && isset($_POST['action'])) {
    
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];

    if ($action == "disable") {
        $active_status = 0;
        $action_message = "disabled";
    } else {
        $active_status = 1;
        $action_message = "enabled";
    }

    // Update User Status
    $query = $connection->prepare("UPDATE users SET acc_active = ? WHERE user_id = ?");
    $query->bind_param("ii", $active_status, $user_id);
                
    if ($query->execute()) {
        $query->close(); 
        // Fetch User Details for Email
        $mail_query = $connection->prepare("SELECT first_name, email, username FROM users WHERE user_id = ? LIMIT 1");
        $mail_query->bind_param("i", $user_id);
        $mail_query->execute();
        $mail_query_result = $mail_query->get_result();
        $user_to_mail = $mail_query_result->fetch_assoc();
        $mail_query->close();
        
        // Send Email
        if ($user_to_mail) {
            $first_name = ucfirst($user_to_mail['first_name']);
            $user_email = $user_to_mail['email'];
            $username = $user_to_mail['username'];
            
            $subject = "Account Status Change by Admin";
            $message = "
            Dear {$first_name},

            Your account with the username '{$username}' has been {$action_message} by an administrator.

            If you have any questions, please contact support.

            Regards,
            The Auction Site Team";

            $headers = "From: The Auction Site";
            
            @mail($user_email, $subject, $message, $headers);
        }
        
        // Success Redirect
        header("Location: " . BASE_URL . "/admin/admin_accs.php");
        exit();

    } else {
      // Database Error 
        $query->close();
        $_SESSION['error'] = "Database update failed.";
        header("Location: " . BASE_URL . "/admin/admin_accs.php");
        exit();
    }

} else {
    // Missing POST data
    header("Location: " . BASE_URL . "/admin/admin_accs.php");
    exit();
}
?>
