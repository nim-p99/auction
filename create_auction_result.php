<?php include_once("header.php")?>

<div class="container my-5">

<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: create_auction.php');
  exit();
}

$errors = [];

// Helper to safely read POST values
function post_val($key) {
  return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

// ----- Grab POST values -----
$title        = post_val('auctionTitle');
$details      = post_val('auctionDetails');
$category_raw = post_val('auctionCategory');

$start_price_raw = post_val('auctionStartPrice');
$reserve_raw     = post_val('auctionReservePrice');
$buy_now_raw     = post_val('auctionBuyNowPrice');

$start_raw = post_val('auctionStartDate');
$end_raw   = post_val('auctionEndDate');

$condition_raw = post_val('auctionCondition');

// ----- Basic validation -----
if ($title === '') {
  $errors[] = "Title is required.";
}
if ($details === '') {
  $errors[] = "Details are required.";
}

$category_id = null;
if ($category_raw === '' || $category_raw === 'Choose.' || $category_raw === 'Choose...') {
  $errors[] = "Category is required.";
} else {
  $category_id = (int)$category_raw;
}

// ----- Prices -----
$start_price = null;
if ($start_price_raw === '') {
  $errors[] = "Starting price is required.";
} elseif (!is_numeric($start_price_raw) || $start_price_raw <= 0) {
  $errors[] = "Starting price must be a positive number.";
} else {
  $start_price = (float)$start_price_raw;
}

$reserve_price = null;
if ($reserve_raw !== '') {
  if (!is_numeric($reserve_raw) || $reserve_raw < 0) {
    $errors[] = "Reserve price must be non-negative.";
  } else {
    $reserve_price = (float)$reserve_raw;
  }
}

$buy_now_price = null;
if ($buy_now_raw !== '') {
  if (!is_numeric($buy_now_raw)) {
    $errors[] = "Buy now price must be a number.";
  } else {
    $buy_now_price = (float)$buy_now_raw;

    if ($buy_now_price < 0) {
      $errors[] = "Buy now price cannot be negative.";
    }
    if ($start_price !== null && $buy_now_price < $start_price) {
      $errors[] = "Buy now price must be at least the starting price.";
    }
    if ($reserve_price !== null && $buy_now_price < $reserve_price) {
      $errors[] = "Buy now price must be at least the reserve price.";
    }
  }
}

// ----- Dates (start + end, at least 1 hour apart) -----
$start_datetime = null;
$end_datetime   = null;

if ($start_raw === '') {
  $errors[] = "Start date/time is required.";
} else {
  $dt = DateTime::createFromFormat('Y-m-d\TH:i', $start_raw);
  if (!$dt) {
    $errors[] = "Start date/time is invalid.";
  } else {
    $start_datetime = $dt;
  }
}

if ($end_raw === '') {
  $errors[] = "End date/time is required.";
} else {
  $dt = DateTime::createFromFormat('Y-m-d\TH:i', $end_raw);
  if (!$dt) {
    $errors[] = "End date/time is invalid.";
  } else {
    $end_datetime = $dt;
  }
}

if ($start_datetime && $end_datetime) {
  if ($end_datetime <= $start_datetime) {
    $errors[] = "End date/time must be after the start date/time.";
  } else {
    $min_end = clone $start_datetime;
    $min_end->modify('+1 hour');
    if ($end_datetime < $min_end) {
      $errors[] = "Auction must be live for at least one hour.";
    }
  }
}

// ----- Item condition -----
$item_condition = 'Unknown';
if ($condition_raw !== '') {
  $item_condition = $condition_raw;
}

// ----- Photo upload (up to 5 images, stored on disk) -----
$photo_urls = [];
$photo_url  = null; // JSON string to store in DB, or null

if (count($errors) === 0 && isset($_FILES['auctionPhotos']) && is_array($_FILES['auctionPhotos']['name'])) {

  $names       = $_FILES['auctionPhotos']['name'];
  $tmp_names   = $_FILES['auctionPhotos']['tmp_name'];
  $error_codes = $_FILES['auctionPhotos']['error'];
  $sizes       = $_FILES['auctionPhotos']['size'];

  $max_size     = 5 * 1024 * 1024; // 5MB
  $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

  $valid_indices = [];

  for ($i = 0; $i < count($names); $i++) {
    if ($error_codes[$i] === UPLOAD_ERR_NO_FILE) {
      continue;
    }

    if ($error_codes[$i] !== UPLOAD_ERR_OK) {
      $errors[] = "There was an error uploading one of your photos.";
      continue;
    }

    if ($sizes[$i] > $max_size) {
      $errors[] = "Photos must be smaller than 5MB.";
      continue;
    }

    $ext = strtolower(pathinfo($names[$i], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts, true)) {
      $errors[] = "Only JPG, PNG, GIF and WEBP images are allowed.";
      continue;
    }

    $valid_indices[] = $i;
  }

  if (!empty($valid_indices) && count($errors) === 0) {
    // Only keep first 5
    $valid_indices = array_slice($valid_indices, 0, 5);

    $upload_dir_fs  = __DIR__ . '/uploads/';
    $upload_dir_url = 'uploads/';

    if (!is_dir($upload_dir_fs)) {
      mkdir($upload_dir_fs, 0755, true);
    }

    foreach ($valid_indices as $idx) {
      $ext      = strtolower(pathinfo($names[$idx], PATHINFO_EXTENSION));
      $basename = bin2hex(random_bytes(16));
      $filename = $basename . '.' . $ext;

      $target_fs  = $upload_dir_fs . $filename;
      $source_tmp = $tmp_names[$idx];

      if (!move_uploaded_file($source_tmp, $target_fs)) {
        $errors[] = "Failed to save one of your uploaded photos. Please try again.";
        break;
      } else {
        $photo_urls[] = $upload_dir_url . $filename; // relative path
      }
    }

    if (!empty($photo_urls) && count($errors) === 0) {
      $photo_url = json_encode($photo_urls);
    }
  }
}

// ----- If anything failed, show errors and stop -----
if (count($errors) > 0) {
  echo '<div class="alert alert-danger"><h4 class="alert-heading">There were problems with your submission:</h4><ul>';
  foreach ($errors as $err) {
    echo '<li>' . htmlspecialchars($err) . '</li>';
  }
  echo '</ul><hr><p>Please go back and correct these fields.</p></div>';
  mysqli_close($connection);
} else {

  // ----- Ensure seller record exists -----
  if (empty($_SESSION['seller_id'])) {
    $query = $connection->prepare("INSERT INTO seller (user_id) VALUES (?)");
    $query->bind_param("i", $_SESSION['user_id']);
    if (!$query->execute()) {
      die("Error creating seller record: " . $query->error);
    }
    $query->close();

    $query = $connection->prepare("SELECT seller_id FROM seller WHERE user_id = ?");
    $query->bind_param("i", $_SESSION['user_id']);
    $query->execute();
    $query->bind_result($seller_id);
    $query->fetch();
    $query->close();

    $_SESSION['seller_id'] = $seller_id;
    //---- notification to buyer  that they have created a seller account
    $email_query= $connection->prepare(
      "SELECT u.username, u.email
      FROM users AS u
      WHERE u.user_id = ? 
    ");
    $email_query->bind_param("i", $_SESSION['user_id']);
    $email_query->execute();
    $email_query->bind_result($user_name, $user_email);
    $email_query->fetch();
    $email_query->close();

    $message =
    "
    Dear $user_name,

    You have successfully created a SELLER account. You can now view your active and completed listings, 
    alongside their associated reviews in the 'Seller Dashboard' within 'My Proflile'. 
    
    From The Auction Site.
    ";
    $headers = "From: the auction_site";
        $headers .= "Content-type: text/plain; charset=UTF-8";
    mail($user_email, "Seller Account Created", $message, $headers);

  } else {
    $seller_id = $_SESSION['seller_id'];
  }

  // ----- Insert into item (includes photo_url JSON) -----
  $stmt_item = $connection->prepare(
    "INSERT INTO item (category_id, title, description, photo_url, item_condition)
     VALUES (?, ?, ?, ?, ?)"
  );
  $stmt_item->bind_param(
    'issss',
    $category_id,
    $title,
    $details,
    $photo_url,
    $item_condition
  );
  $stmt_item->execute();
  $item_id = $connection->insert_id;
  $stmt_item->close();

  // ----- Insert into auction -----
  $start_str = $start_datetime->format('Y-m-d H:i:s');
  $end_str   = $end_datetime->format('Y-m-d H:i:s');

  $stmt_auction = $connection->prepare(
    "INSERT INTO auction (
       seller_id, item_id, start_bid, reserve_price, buy_now_price,
       start_date_time, end_date_time
     )
     VALUES (?, ?, ?, ?, ?, ?, ?)"
  );
  $stmt_auction->bind_param(
    'iidddss',
    $seller_id,
    $item_id,
    $start_price,
    $reserve_price,
    $buy_now_price,
    $start_str,
    $end_str
  );
  $stmt_auction->execute();
  $auction_id = $connection->insert_id;
  $stmt_auction->close();

  

  // Success message + link to listing
  $listing_url = 'listing.php?item_id=' . urlencode($item_id);
  echo '<div class="text-center">';
  echo 'Auction successfully created! ';
  echo '<a href="' . htmlspecialchars($listing_url) . '">View your new listing.</a>';
  echo '</div>';

  $email_q= $connection->prepare(
      "SELECT u.username, u.email
      FROM users AS u
      WHERE u.user_id = ? 
    ");
    $email_q->bind_param("i", $_SESSION['user_id']);
    $email_q->execute();
    $email_q->bind_result($user_name, $user_email);
    $email_q->fetch();
    $email_q->close();
     $message ="
    To  $user_name,

    You have successfully listed $title.
    You will recieve updates on it's progress.
    
    From The Auction Site";
    $headers = "From: the auction_site";
        $headers .= "Content-type: text/plain; charset=UTF-8";
    mail($user_email, "Listing Created", $message, $headers);
    mysqli_close($connection);
    
  }

?>

</div>

<?php include_once("footer.php")?>
