<?php
// 1. Start Session & Connect DB
session_start();
require_once "../config/database.php";

// Helper function
function post_val($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

// 2. Access Control
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$errors = [];

// 3. Extract & Validate
$title        = post_val('auctionTitle');
$details      = post_val('auctionDetails');
$category_raw = post_val('auctionCategory');
$start_price  = post_val('auctionStartPrice');
$reserve_raw  = post_val('auctionReservePrice');
$end_datetime = post_val('auctionEndDate');

if ($title === '') $errors[] = "Title is required.";
if ($details === '') $errors[] = "Details are required.";
if ($category_raw === '' || $category_raw === 'Choose...') $errors[] = "Category is required.";

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

$category_id = (int)$category_raw;
$start_datetime = date('Y-m-d H:i:s');

// 4. Handle Errors
if (count($errors) > 0) {
    include_once "../includes/header.php";
    echo '<div class="container my-5"><div class="alert alert-danger"><h4 class="alert-heading">There were problems with your submission:</h4><ul>';
    foreach ($errors as $err) echo '<li>' . htmlspecialchars($err) . '</li>';
    echo '</ul><hr><p><a href="javascript:history.back()" class="alert-link">Go back and fix them</a></p></div></div>';
    include_once "../includes/footer.php";
    exit();
}

// 5. Handle Seller Creation
if (!isset($_SESSION['seller_id']) || $_SESSION['seller_id'] == null) {
    $checkSeller = $connection->prepare("SELECT seller_id FROM seller WHERE user_id = ?");
    $checkSeller->bind_param("i", $_SESSION['user_id']);
    $checkSeller->execute();
    $checkSeller->store_result();
    
    if ($checkSeller->num_rows > 0) {
        $checkSeller->bind_result($seller_id);
        $checkSeller->fetch();
    } else {
        // Create new seller
        $createSeller = $connection->prepare("INSERT INTO seller (user_id) VALUES (?)");
        $createSeller->bind_param("i", $_SESSION['user_id']);
        if (!$createSeller->execute()) {
            die("Error creating seller record: " . $connection->error);
        }
        $seller_id = $connection->insert_id;
        $createSeller->close();
        
        // Update session account type
        $_SESSION['account_type'] = 'seller';
    }
    $checkSeller->close();
    $_SESSION['seller_id'] = $seller_id;
} else {
    $seller_id = $_SESSION['seller_id'];
}

// 6. Insert Item
$item_condition = 'Unknown'; // Default for now
$stmt_item = $connection->prepare("INSERT INTO item (category_id, title, description, item_condition) VALUES (?, ?, ?, ?)");
$stmt_item->bind_param('isss', $category_id, $title, $details, $item_condition);

if (!$stmt_item->execute()) {
    die("Error inserting item: " . $connection->error);
}
$item_id = $connection->insert_id;
$stmt_item->close();

// 7. Insert Auction
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

if ($stmt_auction->execute()) {
    $stmt_auction->close();
    
    // 8. Redirect to the new listing
    header("Location: " . BASE_URL . "/listing.php?item_id=" . $item_id);
    exit();
} else {
    die("Error creating auction: " . $connection->error);
}
?>
