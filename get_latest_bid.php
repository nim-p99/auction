<?php
require_once 'database.php';
require_once 'utilities.php';

header('Content-Type: application/json');



if (!isset($_GET['auction_id'])){
    echo json_encode(['error'=> 'No auction ID']);
    exit;
}

$auction_id = $_GET['auction_id'];
//getting highest bid
$query = $connection->prepare(
    "SELECT buyer_id, bid_id,date, amount 
    FROM bids
    WHERE auction_id = ?
    ORDER BY amount DESC
    LIMIT 1
    ");
$query->bind_param("i", $auction_id);
$query->execute();
$result = $query->get_result();
$latest_bid = $result->fetch_assoc();
$current_amount = $latest_bid ? $latest_bid['amount'] : 0;

//bid history
$history_query = $connection->prepare(
    "SELECT b.amount, b.date, u.username, b.buyer_id
    FROM bids AS b
    JOIN buyer AS byr ON b.buyer_id = byr.buyer_id
    JOIN users AS u ON byr.user_id = u.user_id
    WHERE b.auction_id = ?
    ORDER BY b.amount DESC, b.date ASC"
);
$history_query->bind_param("i", $auction_id);
$history_query->execute();
$h_result = $history_query->get_result();

ob_start();
list_bid_history($h_result);
$history_html = ob_get_clean();


echo json_encode([
    'amount' => $current_amount,
    'history' => $history_html
]);

?>