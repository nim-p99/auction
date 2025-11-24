<?php include_once("header.php")?>

<div class="container my-5">

<?php

// This function takes the form data and adds the new auction to the database.

/* TODO #1: Connect to MySQL database (perhaps by requiring a file that
            already does this). */


/* TODO #2: Extract form data into variables. Because the form was a 'post'
            form, its data can be accessed via $POST['auctionTitle'], 
            $POST['auctionDetails'], etc. Perform checking on the data to
            make sure it can be inserted into the database. If there is an
            issue, give some semi-helpful feedback to user. */


/* TODO #3: If everything looks good, make the appropriate call to insert
            data into the database. */

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


// Use the real category ID chosen by the user
$category_id = (int)$category_raw;

$start_datetime = date('Y-m-d H:i:s');

if (count($errors) > 0) {
  echo '<div class="alert alert-danger"><h4 class="alert-heading">There were problems with your submission:</h4><ul>';
  foreach ($errors as $err) echo '<li>' . htmlspecialchars($err) . '</li>';
  echo '</ul><hr><p>Please go back and correct these fields.</p></div>';
  mysqli_close($connection);

} else {
  // 9. Insert user into buyer table 
  /* $query = $connection->prepare("INSERT INTO buyer (user_id) VALUES (?)"); */
  /* $query->bind_param("i", $newUserID); */
  /* if (!$query->execute()) { */
  /*   die("Error creating buyer record: " . $query->error); */
  /* } */
  /* $query->close();  */

  if ($_SESSION['seller_id'] == null) {
    $query = $connection->prepare("INSERT INTO seller (user_id) VALUES (?)");
    $query->bind_param("i", $_SESSION['user_id']);
    if (!$query->execute()) {
      die("Error creating seller record: " . $query->error);
    }
    $query->close();

    $query = $connection->prepare("SELECT seller_id FROM seller WHERE user_id = (?)");
    $query->bind_param("i", $_SESSION['user_id']);
    $query->execute();
    $query->bind_result($seller_id);
    $query->fetch();
    $query->close();

    $_SESSION['seller_id'] = $seller_id; 
  } else {
    $seller_id = $_SESSION['seller_id'];
  }

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
  
  $stmt_auction = $connection->prepare(
    "INSERT INTO auction (
       seller_id, item_id, start_bid, reserve_price, buy_now_price,
       start_date_time, end_date_time)
       VALUES (?, ?, ?, ?, ?, ?, ?)"
  );

  $stmt_auction->bind_param(
    'iidddss',
    $seller_id,
    $item_id,
    $start_price,
    $reserve_price,
    $buy_now_price,
    $start_datetime,
    $end_datetime
  );

  $stmt_auction->execute();
  $auction_id = $connection->insert_id;
  $stmt_auction->close();
  mysqli_close($connection);

  //$view_url = 'view_auction.php?auction_id=' . urlencode($auction_id);
  $listing_url = 'listing.php?item_id=' . urlencode($item_id);
  echo '<div class="text-center">';
  echo 'Auction successfully created! ';
  echo '<a href="' . htmlspecialchars($listing_url) . '">View your new listing.</a>';
  echo '</div>';
}





// If all is successful, let user know.
//echo('<div class="text-center">Auction successfully created! <a href="FIXME">View your new listing.</a></div>');


?>

</div>


<?php include_once("footer.php")?>
