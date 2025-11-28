<?php

include_once("header.php");
require("utilities.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: pending_review.php");
    exit();
}

$transaction_id = $_POST['transaction_id'];
$rating = (int)$_POST['rating'];
$comment = $_POST['comment'];
$review_type = $_POST['review_type']; #buyer_reviewing_seller or seller_reviewing_buyer

//Validation
if ($rating < 1 ||  $rating > 5){
    $_SESSION['error_message'] = "Invalid rating selected.";
    header("Location: pending_review.php");
    exit();
}

if ($review_type === 'buyer_reviewing_seller'){
    //update transaction
    $update_sql = $connection->prepare(
        "UPDATE transaction SET seller_rating = ?, seller_comments= ? 
        WHERE transaction_id = ?");
    $update_sql->bind_param("isi", $rating, $comment, $transaction_id);

    if ($update_sql->execute()) {
        $update_sql->close();

        $find_seller_sql=$connection->prepare("
            SELECT a.seller_id
            FROM transaction AS t
            JOIN bids AS b ON t.bid_id=b.bid_id
            JOIN auction AS a ON b.auction_id = a.auction_id
            WHERE t.transaction_id = ?
        ");

        $find_seller_sql->bind_param("i", $transaction_id);
        $find_seller_sql->execute();
        $result = $find_seller_sql->get_result();
        $target_seller_id = Null;

        if ($row  = $result->fetch_assoc()){
            $target_seller_id = $row['seller_id'];
        }
        $find_seller_sql->close();
        
        if ($target_seller_id){
            update_seller_average($connection, $target_seller_id);
        }

        $_SESSION['success_message'] = "Review submitted successfully";
    }else{
        $_SESSION['error_message'] = "Database error: ".$connection->error;

    }    
    
} elseif($review_type === 'seller_reviewing_buyer') {
    #update transaction table
    $update_sql = $connection->prepare("
        UPDATE transaction SET buyer_rating = ?, buyer_comments = ?
        WHERE transaction_id  =?
    ");
    $update_sql-> bind_param("isi", $rating, $comment, $transaction_id);

    if ($update_sql->execute()){
        $update_sql->close();

        #getting buyer_id
        $find_byer_sql = $connection->prepare("
            SELECT b.buyer_id
            FROM transaction AS t
            JOIN bids AS b ON t.bid_id = b.bid_id
            WHERE t.transaction_id = ?
        ");
        $find_byer_sql->bind_param("i", $transaction_id);
        $find_byer_sql->execute();

        $result = $find_byer_sql->get_result();
        $target_buyer_id = null;

        if ($row = $result->fetch_assoc()){
            $target_buyer_id = $row['buyer_id'];
        }
        $find_byer_sql->close();

        #recalcualte buyer average
        if($target_buyer_id) {
            update_buyer_average($connection, $target_buyer_id);
        }

        $_SESSION['success_message'] = "Feedback submitted  successfully!";
    }else{
        $_SESSION['error_message'] = "Database error";
    }
}

#redirect
header("Location: pending_review.php");
exit();
?>