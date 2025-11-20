<?php

include_once("header.php");

if (isset($_POST['user_id']) && isset($_POST['action'])) {
    
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];


    if ($action == "disable") {
        $active_status = 0;
        $action_message = "disabled";
        $email_action_verb = "deactivated";
    } else {
        $active_status = 1;
        $action_message = "enabled";
        $email_action_verb = "re-activated";
    }

    $query = $connection->prepare("
        UPDATE users
        SET acc_active = ?
        WHERE user_id = ?
    ");
    $query->bind_param("ii", $active_status, $user_id);
                
        if ($query->execute()) {

            $mail_query = $connection-> prepare(
                "SELECT first_name, email, username 
                FROM users 
                WHERE user_id = ?
                LIMIT 1"
            );
            $mail_query->bind_param("i", $user_id);
            $mail_query->execute();
            $mail_query_result = $mail_query->get_result();
            $user_to_mail = $mail_query_result->fetch_assoc();
            $mail_query->close();
            
            if ($user_to_mail) {
                $first_name = ucfirst($user_to_mail['first_name']);
                $user_email = $user_to_mail['email'];
                $username = $user_to_mail['username'];
                $subject = "Account Status Change by Admin";
                $message = "
                    Dear {$first_name},

                    Your account with the username '{$username}' has been {$action_message} by an administrator.

                    If you have any questions, please contact support.

                    From: The Auction Site";

                $headers = "From: the auction_site\r\n";
                $headers .= "Content-type: text/plain; charset=UTF-8";
            
                mail($user_email, $subject, $message, $headers);
            }

        } else {
            $_SESSION['message'] = "Error updating account status: " . $stmt->error;
            $_SESSION['message_type'] = 'danger';
        }
        $query->close();
        header("Location: admin_accs.php");
        exit();

    } else {
    
    header("Location: admin_accs.php");
    exit();
}

?>