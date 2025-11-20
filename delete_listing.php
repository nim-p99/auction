<?php
include_once("header.php");
require("utilities.php");

// if not admin => redirecct
if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: browse.php");
    exit();
}

//Check if item-id was sent via post

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['auction_id'])) {
    $auction_id = $_POST['auction_id'];

//delete it
$query = $connection->prepare("UPDATE auction SET is_active = FALSE WHERE auction_id =?");
$query->bind_param("i", $auction_id);

    //if succes ==> send email to seller that their  listing was removed 
    if ($query->execute()) {
        

        $mail_query = $connection->prepare(
        "SELECT u.first_name, u.email, i.title 
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
        
        $seller_name = ucfirst($seller_to_mail['first_name']);
        $seller_email = $seller_to_mail['email'];
        $auction_title = $seller_to_mail['title'];
        $subject = "Listing Removed by Admin";
        $message = "
        Dear {$seller_name}

        Unfortunately your listing: '{$auction_title}' was delisted by admin as it violates our terms of service.
        
        From: The Auction Site";
        $headers="From: the auction_site";
        $headers .= "Content-type: text/plain; charset=UTF-8";
        
        mail($seller_email, $subject, $message, $headers);

        header("Location: delete_success.php");

    } else {
        echo "Error deleting record: " . $connection->error;
    }
    $query->close();
} else {
    header("Location: admin_listings.php");
}
?>