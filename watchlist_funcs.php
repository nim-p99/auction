<?php
session_start();
require('utilities.php');
require('database.php');
$res = "fail";
if (!isset($_POST['functionname']) || !isset($_POST['arguments'])) {
  echo $res;
  exit;
}
// Extract arguments from the POST variables:
$auction_id = $_POST['arguments'][0];
if ($_POST['functionname'] == "add_to_watchlist") {
  // TODO: Update database and return success/failure.
  $query = $connection->prepare("INSERT INTO watchlist (user_id, auction_id) VALUES (?, ?)");
  $query->bind_param("ii", $_SESSION['user_id'], $auction_id);
  if ($query->execute()) {
    $res = "success";
  };
  $query->close();
}
else if ($_POST['functionname'] == "remove_from_watchlist") {
  // TODO: Update database and return success/failure.
  $query = $connection->prepare("DELETE FROM watchlist WHERE user_id = ? AND auction_id = ?");
  $query->bind_param("ii", $_SESSION['user_id'], $auction_id);
  if ($query->execute()) {
    $res = "success";
  };
  $query->close();
}
echo json_encode(['status' => $res]);
exit;
