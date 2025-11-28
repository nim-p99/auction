<?php
// 1. Start Session & Connect DB
session_start();
require_once "../config/database.php";

// 2. Access Control (Admin Only)
if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: " . BASE_URL . "/browse.php");
    exit();
}

// 3. Check if auction_id was sent via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['auction_id'])) {
    
    $auction_id = $_POST['auction_id'];

    // 4. Soft Delete (Set is_active to FALSE)
    $query = $connection->prepare("UPDATE auction SET is_active = FALSE WHERE auction_id = ?");
    $query->bind_param("i", $auction_id);

    if ($query->execute()) {
        $query->close(); 
        // 5. Fetch Seller Details for Email
        $mail_query = $connection->prepare("
            SELECT u.first_name, u.email, i.title 
            FROM users AS u
            JOIN seller AS s ON s.user_id = u.user_id
            JOIN auction AS a ON a.seller_id = s.seller_id
            JOIN item AS i ON i.item_id = a.item_id
            WHERE a.auction_id = ?    
            LIMIT 1    
        ");
        $mail_query->bind_param("i", $auction_id);
        $mail_query->execute();
        $mail_query_result = $mail_query->get_result();
        $seller_to_mail = $mail_query_result->fetch_assoc();
        $mail_query->close();
        
        // 6. Send Notification Email
        if ($seller_to_mail) {
            $seller_name = ucfirst($seller_to_mail['first_name']);
            $seller_email = $seller_to_mail['email'];
            $auction_title = $seller_to_mail['title'];
            
            $subject = "Listing Removed by Admin";
            $message = "
            Dear {$seller_name},

            Unfortunately your listing: '{$auction_title}' was delisted by an administrator as it violates our terms of service or requires review.
            
            Regards,
            The Auction Site Team";
            
            $headers = "From: The Auction Site";
            
            // Suppress warnings if mail server not configured
            @mail($seller_email, $subject, $message, $headers);
        }

        // 7. Redirect to Success Page (Root Directory)
        header("Location: " . BASE_URL . "/delete_success.php");
        exit();

    } else {
        // Database Error
        echo "Error deleting record: " . $connection->error;
      }
    
} else {
    // If accessed directly without POST data, redirect back to Admin Listings
    header("Location: " . BASE_URL . "/admin/admin_listings.php");
    exit();
}
?>
