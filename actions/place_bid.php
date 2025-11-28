<?php
// 1. Start Session & Config
session_start();
require_once "../config/database.php";

// 2. Access Control
if(!isset($_SESSION['user_id'])){
    header('Location: ' . BASE_URL . '/login.php');
    exit();    
}

$buyer_id = $_SESSION['buyer_id'];

// 3. Get POST data
$bid_amount = (float)$_POST['bid'] ?? null;
$auction_id = (int)$_POST['auction_id'] ?? null;
$highest_bid = (float)$_POST['highest_bid'] ?? null;
$item_id = (int)$_POST['item_id'] ?? null;

// Validation 1: Valid Number
if (!is_numeric($bid_amount) || $bid_amount <= 0) {
    $_SESSION["error_message"] = "Please enter a valid bid amount!";
    header("Location: " . BASE_URL . "/listing.php?item_id=" . $item_id);
    exit();
}

// Validation 2: Higher than current
if ($bid_amount <= $highest_bid) {
    $_SESSION["error_message"] = "Your bid must be higher than the current price of £" . number_format($highest_bid, 2);
    header("Location: " . BASE_URL . "/listing.php?item_id=" . $item_id);
    exit(); 
}

// Validation 3: Check Active
$query = $connection->prepare("SELECT end_date_time FROM auction WHERE auction_id = ?");
$query->bind_param("i", $auction_id);
$query->execute();
$auction_result = $query->get_result();
$auction_data = $auction_result->fetch_assoc();
$query->close();

if (!$auction_data || new DateTime() > new DateTime($auction_data['end_date_time'])) {
  $_SESSION['error_message'] = "This auction is no longer active.";
  header("Location: " . BASE_URL . "/listing.php?item_id=" . $item_id);
  exit();
}

// Validation 4: Self-Bidding
$query = $connection->prepare("
  SELECT s.user_id AS seller_user_id
  FROM auction AS a 
  JOIN seller AS s ON a.seller_id = s.seller_id 
  WHERE a.auction_id = ?
");
$query->bind_param("i", $auction_id);
$query->execute();
$query_result = $query->get_result();
$query_row = $query_result->fetch_assoc();
$query->close();

if ($query_row && $query_row['seller_user_id'] == $_SESSION['user_id']) {
  $_SESSION['error_message'] = "You cannot place a bid on your own auction.";
  header("Location: " . BASE_URL . "/listing.php?item_id=" . $item_id);
  exit();
}

// --- NOTIFICATION PREP --- //

// Get Item Title
$auction_title_query = $connection->prepare("SELECT title FROM item WHERE item_id = ?");
$auction_title_query->bind_param("i", $item_id);
$auction_title_query->execute();
$auction_title_result = $auction_title_query->get_result();
$auction_title_array = $auction_title_result->fetch_assoc();
$auction_title = $auction_title_array['title'];
$auction_title_query->close();

// Get Previous Highest Bidder (Before inserting new bid)
$previous_highest_bidder_query = $connection->prepare("
    SELECT u.email, u.first_name, u.user_id 
    FROM bids AS b
    JOIN buyer AS t ON b.buyer_id = t.buyer_id
    JOIN users AS u ON t.user_id = u.user_id
    WHERE b.auction_id = ?
    ORDER BY b.amount DESC
    LIMIT 1
");  
$previous_highest_bidder_query->bind_param("i", $auction_id);
$previous_highest_bidder_query->execute();
$prev_highest_bidder_result = $previous_highest_bidder_query->get_result();
$outbid_user = $prev_highest_bidder_result->fetch_assoc();
$previous_highest_bidder_query->close();

// --- INSERT BID --- //
$insert_bid_query = "INSERT INTO bids (auction_id, buyer_id, amount, date) VALUES (?, ?, ?, NOW())";
$query = $connection->prepare($insert_bid_query);
$query->bind_param("iid", $auction_id, $buyer_id, $bid_amount);

if (!$query->execute()) {
    $_SESSION['error_message'] = "Failed to place bid. Please try again.";
    header("Location: " . BASE_URL . "/listing.php?item_id=" . $item_id);
    exit();
}
$query->close();

// --- NOTIFICATIONS --- //

// 1. Outbid Notification
if ($outbid_user !== null && $outbid_user['user_id'] !== $_SESSION['user_id']) {
    $outbid_email = $outbid_user['email'];
    $outbid_name = ucfirst($outbid_user['first_name']);
    $subject = "You've been outbid on: {$auction_title}";
    $message = "
        Dear {$outbid_name},

        You have been outbid on the auction for '{$auction_title}'. 
        The new current price is £{$bid_amount}.

        From: The Auction Site
    ";
    $headers = "From: The Auction Site";
    // Suppress warnings
    @mail($outbid_email, $subject, $message, $headers);
}

// 2. Watchlist Notification
$watchlist_query = $connection->prepare("
    SELECT u.email, u.first_name
    FROM watchlist AS w
    JOIN users AS u ON w.user_id = u.user_id
    WHERE w.auction_id = ?
");
$watchlist_query->bind_param("i", $auction_id);
$watchlist_query->execute();
$watchlist_result = $watchlist_query->get_result();

while ($watcher_row = $watchlist_result->fetch_assoc()){
    $watcher_name = ucfirst($watcher_row['first_name']);
    $to = $watcher_row['email'];
    $message ="
    To {$watcher_name},

    Someone made a bid of £{$bid_amount} on the auction for '{$auction_title}' that you are watching.
    
    From The Auction_Site
    ";
    $subject = "Update: New activity on '{$auction_title}'";
    $headers = "From: The Auction Site";
    
    @mail($to, $subject, $message, $headers); 
}
$watchlist_query->close();

// Success
$_SESSION['success_message'] = "Your bid of £" . number_format($bid_amount, 2) . " has been placed successfully!";
header("Location: " . BASE_URL . "/listing.php?item_id=" . $item_id);
exit();
?>
