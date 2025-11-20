<?php

// TODO: Extract $_POST variables, check they're OK, and attempt to make a bid.
// Notify user of success/failure and redirect/give navigation options.
include_once("header.php");



/* echo '<pre>'; */
/* var_dump($_POST); */
/* var_dump($_SESSION); */
/* echo '</pre>'; */


if(!isset($_SESSION['user_id'])){
    //redirects if not logged in
    header('Location: login.php');
    exit();   
}

$buyer_id = $_SESSION['buyer_id'];
// Get POST data
$bid_amount = (float)$_POST['bid'] ?? null;
$auction_id = (int)$_POST['auction_id'] ?? null;
$highest_bid = (float)$_POST['highest_bid'] ?? null;
$item_id = (int)$_POST['item_id'] ?? null;
/* echo '<pre>'; */
/* var_dump($buyer_id, $item_id, $auction_id, $bid_amount, $highest_bid); */
/* echo '</pre>'; */
/* exit(); */

// If bid amount missing = error
if (!is_numeric($bid_amount) || $bid_amount <= 0) {
    $_SESSION["error_message"]= " Please enter a valid bid amount!";
    header("Location: listing.php?item_id=" . $item_id);
    exit();
} // could add error message if user enters a -#????


// checking bid is higher than highest bid (current bid)
if ($bid_amount <= $highest_bid) {
    $_SESSION["error_message"]="Your bid must be higher than the current price of £" . number_format($highest_bid, 2);
    header("Location: listing.php?item_id=" . $item_id);
    exit(); 
}

// check auction is active
$query = $connection->prepare("SELECT end_date_time FROM auction WHERE auction_id = ?");
$query->bind_param("i", $auction_id);
$query->execute();
$auction_result = $query->get_result();
$auction_data = $auction_result->fetch_assoc();
if (!$auction_data || new DateTime() > new DateTime($auction_data['end_date_time'])) {
  $_SESSION['error_message'] = "This auction is no longer active.";
  header("Location: listing.php?item_id=" . $item_id);
  exit();
}
$query->close();

// Insert bid into bids table
$insert_bid_query = "INSERT INTO bids (auction_id, buyer_id, amount, date) VALUES (?, ?, ?, NOW())";
$query = $connection->prepare($insert_bid_query);
$query->bind_param("iid", $auction_id, $buyer_id, $bid_amount);


if (!$query->execute()) {
    die("Insert failed: " . $query->error);
    $_SESSION['error_message'] = "Failed to place bid. Please try again.";
    header("Location: listing.php?item_id=" . $item_id);
    exit();
}





// Exitoooo!
$_SESSION['success_message'] = "Your bid of £" . number_format($bid_amount, 2) . " has been placed successfully!";
header("Location: listing.php?item_id=" . $item_id);

$query->close();

exit();
?>
