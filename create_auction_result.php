<?php include_once("header.php")?>

<div class="container my-5">

<?php
$config = include('../auction_config/db_config.php');

$connection = mysqli_connect(
  $config['host'],
  $config['username'],
  $config['password'],
  $config['database']
);

if (!$connection) {
  die('<div class="alert alert-danger">Database connection failed: '
      . htmlspecialchars(mysqli_connect_error()) . '</div>');
}

$errors = [];

function post_val($key) {
  return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

$title        = post_val('auctionTitle');
$details      = post_val('auctionDetails');
$category_raw = post_val('auctionCategory');
$start_price  = post_val('auctionStartPrice');
$reserve_raw  = post_val('auctionReservePrice');
$end_datetime = post_val('auctionEndDate');

if ($title === '') $errors[] = "Title is required.";
if ($details === '') $errors[] = "Details are required.";

if ($category_raw === '' || $category_raw === 'Choose...') {
  $errors[] = "Category is required.";
}

if ($start_price === '') {
  $errors[] = "Starting price is required.";
} elseif (!is_numeric($start_price) || $start_price <= 0) {
  $errors[] = "Starting price must be a positive number.";
} else {
  $start_price = (float)$start_price;
}

$reserve_price = null;
if ($reserve_raw !== '') {
  if (!is_numeric($reserve_raw) || $reserve_raw < 0) {
    $errors[] = "Reserve price must be non-negative.";
  } else {
    $reserve_price = (float)$reserve_raw;
  }
}

if ($end_datetime === '') {
  $errors[] = "End date/time is required.";
} else {
  $end_datetime = str_replace('T', ' ', $end_datetime) . ':00';
}

session_start();
// Update to match real session variable name
$seller_id = $_SESSION['account_id'] ?? 1;

// Use the real category ID chosen by the user
$category_id = (int)$category_raw;

$start_datetime = date('Y-m-d H:i:s');

if (count($errors) > 0) {
  echo '<div class="alert alert-danger"><h4 class="alert-heading">There were problems with your submission:</h4><ul>';
  foreach ($errors as $err) echo '<li>' . htmlspecialchars($err) . '</li>';
  echo '</ul><hr><p>Please go back and correct these fields.</p></div>';
  mysqli_close($connection);

} else {

  $item_condition = 'Unknown';

  $stmt_item = $connection->prepare(
    "INSERT INTO item (category_id, title, description, item_condition)
     VALUES (?, ?, ?, ?)"
  );

  $stmt_item->bind_param('isss', $category_id, $title, $details, $item_condition);
  $stmt_item->execute();
  $item_id = $connection->insert_id;
  $stmt_item->close();

  $buy_now_price = null;
  $current_price = (int)round($start_price);
  $num_bids = 0;

  $stmt_auction = $connection->prepare(
    "INSERT INTO auction (
       seller_id, item_id, start_bid, reserve_price, buy_now_price,
       start_date_time, end_date_time, current_price, num_bids
     ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
  );

  $stmt_auction->bind_param(
    'iidddssii',
    $seller_id,
    $item_id,
    $start_price,
    $reserve_price,
    $buy_now_price,
    $start_datetime,
    $end_datetime,
    $current_price,
    $num_bids
  );

  $stmt_auction->execute();
  $auction_id = $connection->insert_id;
  $stmt_auction->close();
  mysqli_close($connection);

  $view_url = 'view_auction.php?auction_id=' . urlencode($auction_id);

  echo '<div class="text-center">';
  echo 'Auction successfully created! ';
  echo '<a href="' . htmlspecialchars($view_url) . '">View your new listing.</a>';
  echo '</div>';
}
?>

</div>

<?php include_once("footer.php")?>
