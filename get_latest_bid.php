<?php
require_once 'database.php';

header('Content-Type: application/json');



if (!isset($_GET['auction_id'])){
    echo json_encode(['error'=> 'No auction ID']);
    exit;
}

$auction_id = $_GET['auction_id'];

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

if ($latest_bid){
    echo json_encode($latest_bid);
}else{
    echo json_encode(['amount' => 0]);
}
?>