<?php


// TODO: Extract $_POST variables, check they're OK, and attempt to make a bid.
// Notify user of success/failure and redirect/give navigation options.
include_once("header.php");
require_once("database.php");

if(!isset($_SESSION['user_id'])){
    //redirects if not logged in
    header('Location: login.php');
    exit();   
}

$user_id = $_SESSION['user_id'];

// Get POST data
$bid_amount = $_POST['bid'] ?? null;
$auction_id = $_POST['auction_id'] ?? null;

//need buyer ID bec bid table uses buyer_id
$getBuyer = $connection->prepare("SELECT buyer_id FROM buyer WHERE user_id = ?");
$getBuyer->bind_param("i", $user_id);
$getBuyer->execute();
$buyerResult = $getBuyer->get_result();

if ($buyerRow = $buyerResult->fetch_assoc()) {
    $buyer_id = $buyerRow['buyer_id'];
} else {
    $_SESSION["error_message"] = "You must have a buyer account to place a bid.";
    header("Location: listing.php?item_id=" . $auction_id);
    exit();
}


// If bid amount missing = error
if (empty($bid_amount)) {
    $_SESSION["error_message"]= " Please enter a bid!";
    header("Location: listing.php?item_id=" . $auction_id);
    exit();
} // could add error message if user enters a -#????

// gets current auction details
$auctiondeets_query = "SELECT current_price, num_bids FROM auction WHERE auction_id = ?";
$stmt = $connection->prepare($auctiondeets_query);
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION["error_message"]= "Auction not found";
    header("Location: listing.php?item_id=" . $auction_id);
    exit();
}

$auction = $result->fetch_assoc();
$current_price = $auction['current_price'];
$num_bids = $auction['num_bids'];

// checking bid is higher than highest bid (current bid)
if ($bid_amount <= $current_price) {
    $_SESSION ["error_message"]="Your bid must be higher than the current price of £" . number_format($current_price, 2);
    header("Location: listing.php?item_id=" . $auction_id);
    exit();;
   
}

// Insert bid into bids table
$insert_bid_query = "INSERT INTO bids (auction_id, buyer_id, amount, date) VALUES (?, ?, ?, NOW())";
$stmt = $connection->prepare($insert_bid_query);
$stmt->bind_param("iid", $auction_id, $buyer_id, $bid_amount);

if (!$stmt->execute()) {
    $_SESSION['error_message'] = "Failed to place bid. Please try again.";
    header("Location: listing.php?item_id=" . $auction_id);
    exit();
}

// Update auction's current_price and num_bids
$new_num_bids = $num_bids + 1;
$update_auction_query = "UPDATE auction SET current_price = ?, num_bids = ? WHERE auction_id = ?";
$stmt = $connection->prepare($update_auction_query);
$stmt->bind_param("dii", $bid_amount, $new_num_bids, $auction_id);

if (!$stmt->execute()) {
    $_SESSION['error_message'] = "Bid placed but failed to update auction. Please contact support.";
    header("Location: listing.php?item_id=" . $auction_id);
    exit();
}

// Exitoooo!
$_SESSION['success_message'] = "Your bid of £" . number_format($bid_amount, 2) . " has been placed successfully!";
header("Location: listing.php?item_id=" . $auction_id);

$stmt->close();
$connection->close();
exit();
?>



