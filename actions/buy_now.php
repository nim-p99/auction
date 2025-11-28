<?php
session_start();
require_once "../config/database.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

// Validation
if (!isset($_POST['auction_id'])) {
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: " . BASE_URL . "/browse.php");
    exit();
}

// Pull buyer and auction id
$buyer_id = $_SESSION['buyer_id'];
$auction_id = $_POST['auction_id'];

// Get auction details
$auction_query = $connection->prepare("
    SELECT a.buy_now_price, a.item_id 
    FROM auction a 
    WHERE a.auction_id = ?
");
$auction_query->bind_param("i", $auction_id);
$auction_query->execute();
$auction_result = $auction_query->get_result();
$auction = $auction_result->fetch_assoc();
$auction_query->close();

// If auction id missing -> back to browse page
if (!$auction) {
    $_SESSION['error_message'] = "Auction not found.";
    header("Location: " . BASE_URL . "/browse.php");
    exit();
}

$item_id = $auction['item_id'];
$buy_now_price = $auction['buy_now_price'];

// Create Bid Record (At Buy Now Price)
$insert_bid = $connection->prepare("
    INSERT INTO bids (auction_id, buyer_id, amount, date) 
    VALUES (?, ?, ?, NOW())
");
$insert_bid->bind_param("iid", $auction_id, $buyer_id, $buy_now_price);

if (!$insert_bid->execute()) {
    $_SESSION['error_message'] = "Failed to create bid: " . $insert_bid->error;
    header("Location: " . BASE_URL . "/listing.php?item_id=" . $item_id);
    exit();
}
$bid_id = $connection->insert_id;
$insert_bid->close();

// Create Transaction Record
$insert_transaction = $connection->prepare("
    INSERT INTO transaction (bid_id, seller_rating, seller_comments, buyer_rating, buyer_comments) 
    VALUES (?, NULL, '', NULL, '')
");
$insert_transaction->bind_param("i", $bid_id);

if (!$insert_transaction->execute()) {
    $_SESSION['error_message'] = "Failed to create transaction: " . $insert_transaction->error;
    header("Location: " . BASE_URL . "/listing.php?item_id=" . $item_id);
    exit();
}
$insert_transaction->close();

// End Auction (Set End Date to NOW)
$end_auction = $connection->prepare("
    UPDATE auction 
    SET end_date_time = NOW() 
    WHERE auction_id = ?
");
$end_auction->bind_param("i", $auction_id);

if (!$end_auction->execute()) {
    $_SESSION['error_message'] = "Failed to end auction: " . $end_auction->error;
    header("Location: " . BASE_URL . "/listing.php?item_id=" . $item_id);
    exit();
}
$end_auction->close();

// Success & Redirect
$_SESSION['success_message'] = "Purchase successful!! You bought this item for £" . number_format($buy_now_price, 2);
header("Location: " . BASE_URL . "/listing.php?item_id=" . $item_id);
exit();
?>
