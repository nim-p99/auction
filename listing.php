<?php 
include_once "includes/header.php";
require_once "includes/utilities.php";

// 1. Display Flash Messages
if (isset($_SESSION['error_message'])) {
    echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
    unset($_SESSION['success_message']);
}

// 2. Validate Item ID
if (!isset($_GET['item_id'])) {
    header("Location: " . BASE_URL . "/browse.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$item_id = $_GET['item_id'];
$seller_id = null;
$auction_id = null;

// 3. Get Auction IDs
$query = $connection->prepare("SELECT seller_id, auction_id FROM auction WHERE item_id = ?");
$query->bind_param("i", $item_id);
$query->execute();
$query->bind_result($seller_id, $auction_id);

if (!$query->fetch()) {
    $query->close();
    header("Location: " . BASE_URL . "/error.php?error_id=1");
    exit();
}
$query->close();

// 4. Fetch Full Auction Data
$auction_sql = "SELECT a.*, i.title, i.description 
                FROM auction a
                JOIN item i ON a.item_id = i.item_id
                WHERE a.auction_id = ?";
$query = $connection->prepare($auction_sql);
$query->bind_param("i", $auction_id);
$query->execute();
$auction = $query->get_result()->fetch_assoc();

// 5. Time & Bid Logic
$start_time = new DateTime($auction['start_date_time']);
$end_time = new DateTime($auction['end_date_time']);
$now = new DateTime();

$highest_bid_sql = "SELECT MAX(amount) AS highest_bid FROM bids WHERE auction_id = ?";
$query = $connection->prepare($highest_bid_sql);
$query->bind_param("i", $auction_id);
$query->execute();
$result = $query->get_result();
$row = $result->fetch_assoc();

$highest_bid = $row["highest_bid"];
if ($highest_bid == null) {
    $highest_bid = 0;
}

// 6. Handle Auction End (Winning Logic)
if ($now > $end_time && $highest_bid > 0) { 
    $winner_id_query = $connection->prepare("
        SELECT bd.amount, bd.buyer_id, u.email, u.first_name, i.title, a.mail_sent, bd.bid_id
        FROM bids AS bd
        JOIN buyer AS b ON b.buyer_id = bd.buyer_id
        JOIN users AS u ON b.user_id = u.user_id
        JOIN auction AS a ON a.auction_id = bd.auction_id
        JOIN item AS i ON a.item_id = i.item_id
        WHERE bd.auction_id = ? AND bd.amount = ?
        ORDER BY date ASC LIMIT 1
    ");
    $winner_id_query->bind_param("id", $auction_id, $highest_bid); 
    $winner_id_query->execute();
    $winner_result = $winner_id_query->get_result();
  
    if (($winner_row = $winner_result->fetch_assoc()) && $winner_row['mail_sent'] !== 1) {
        $winner_id_query->close();
        
        $update_mail_query = $connection->prepare("UPDATE auction SET mail_sent = 1 WHERE auction_id = ?");
        $update_mail_query->bind_param("i", $auction_id);
        $update_mail_query->execute();
        $update_mail_query->close();
        
        if (isset($winner_row['bid_id'])) {
            $trans_query = $connection->prepare("INSERT INTO `transaction` (bid_id) VALUES (?)");
            $trans_query->bind_param("i", $winner_row['bid_id']);
            $trans_query->execute();
            $trans_query->close(); 
        }
    }
}

// 7. Check Watchlist Status
$has_session = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$watching = false;

if ($has_session) {
    $query = $connection->prepare("SELECT watchlist_id FROM watchlist WHERE user_id = ? AND auction_id = ?");
    $query->bind_param("ii", $user_id, $auction_id);
    $query->execute();
    $query->store_result();
    $watching = $query->num_rows > 0;
    $query->close();
}

// 8. Time Remaining Calculation
$time_display_string = '';

if ($now < $start_time) {
    // Case 1: Future
    $time_to_start = date_diff($now, $start_time);
    $time_display_string = ' (starts in ' . display_time_remaining($time_to_start) . ')';
    $auction_status = 'FUTURE';
} elseif ($now > $end_time) {
    // Case 2: Ended
    $auction_status = 'ENDED';
} else {
    // Case 3: Active
    $time_to_end = date_diff($now, $end_time);
    $time_display_string = ' (ends in ' . display_time_remaining($time_to_end) . ')';
    $auction_status = 'ACTIVE';
}
?>

<div class="container">

<div class="row"> 
  <div class="col-sm-8"> 
    <h2 class="my-3"><?php echo htmlspecialchars($auction['title']); ?></h2>
    <p class="my-3"><?php echo htmlspecialchars($auction['description']); ?></p>
  </div>
  
  <div class="col-sm-4 align-self-center">
    <?php
      echo('<a href="' . BASE_URL . '/seller_profile.php?seller_id=' . $seller_id . '">Seller Profile</a>');
      
      // Show Watchlist buttons if not admin
      if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== 1): 
    ?>
        <div id="watch_nowatch" <?php if ($has_session && $watching) echo('style="display: none"');?> >
          <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to watchlist</button>
        </div>
        <div id="watch_watching" <?php if (!$has_session || !$watching) echo('style="display: none"');?> >
          <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
          <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
        </div>
    <?php endif; ?>
  </div>
</div>

<div class="row"> 
  <div class="col-sm-8"></div>

  <div class="col-sm-4">
    
    <?php 
    // CASE 1: AUCTION ENDED
    if ($auction_status == 'ENDED'): ?>
       
       <div class="alert alert-secondary">This auction ended <?php echo(date_format($end_time, 'j M H:i'))?></div>
       <p class="lead">Winning bid: £<?php echo(number_format($highest_bid, 2))?></p>

    <?php 
    // CASE 2: AUCTION HAS NOT STARTED
    elseif ($auction_status == 'FUTURE'): ?>
       
       <div class="alert alert-warning">
           Auction starts on <?php echo(date_format($start_time, 'j M H:i') . $time_display_string); ?>
       </div>
       <p class="lead">Starting Price: £<?php echo(number_format($auction['start_bid'], 2)) ?></p>
       <button class="btn btn-secondary form-control" disabled>Can't Bid Yet</button>

    <?php 
    // CASE 3: AUCTION IS ACTIVE
    else: ?>

        <?php if (!is_null($auction["buy_now_price"])): ?>
          <p></p>
          <form method="POST" action="<?php echo BASE_URL; ?>/actions/buy_now.php" onsubmit="return confirm('Are you sure you want to buy this item now?');">
            <input type="hidden" name="auction_id" value="<?php echo $auction_id; ?>">
            <button type="submit" class="btn-info btn-sm">Buy Now at: £<?php echo number_format($auction["buy_now_price"], 2); ?></button>
          </form>
        <?php endif; ?>

       <p>Auction ends <?php echo(date_format($end_time, 'j M H:i') . $time_display_string) ?></p>  
       <p class="lead">Current bid: £<?php echo(number_format($highest_bid, 2)) ?></p>

       <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] !== 1){ ?>
         <form method="POST" action="<?php echo BASE_URL; ?>/actions/place_bid.php">
           <input type="hidden" name="auction_id" value="<?php echo $auction_id; ?>">
           <input type="hidden" name="highest_bid" value="<?php echo $highest_bid; ?>">
           <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
           <div class="input-group">
             <div class="input-group-prepend">
               <span class="input-group-text">£</span>
             </div>
             <input type="number" class="form-control" id="bid" name="bid" step="0.1" required>
           </div>
           <button type="submit" class="btn btn-primary form-control">Place bid</button>
         </form>
       <?php }?>

    <?php endif; ?>
  </div> 
</div> 

<div class="row mt-4">
  <div class="col-12">
    <h4>Bid History</h4>
    <?php
      $bid_history_sql = "
        SELECT b.amount, b.date, u.username
        FROM bids AS b
        JOIN buyer AS buyer_t ON b.buyer_id = buyer_t.buyer_id
        JOIN users AS u ON buyer_t.user_id = u.user_id
        WHERE b.auction_id = ?
        ORDER BY b.amount DESC, b.date ASC
      ";
      $bid_history_query = $connection->prepare($bid_history_sql);
      $bid_history_query->bind_param("i", $auction_id);
      $bid_history_query->execute();
      $bid_history_result = $bid_history_query->get_result();

      list_bid_history($bid_history_result);
      $bid_history_query->close();
    ?>
  </div>
</div>

<?php include_once "includes/footer.php"; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script> 
function addToWatchlist(button) {
  $.ajax('<?php echo BASE_URL; ?>/includes/watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'add_to_watchlist', arguments: [<?php echo($auction_id);?>]},

    success: 
      function (obj, textstatus) {
        console.log("Success");
        try {
            var objT = JSON.parse(obj).status;
            if (objT == "success") {
              $("#watch_nowatch").hide();
              $("#watch_watching").show();
            } else {
              alert("Operation failed: " + objT);
            }
        } catch(e) {
            console.error("Invalid JSON response", obj);
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  });
}

function removeFromWatchlist(button) {
  $.ajax('<?php echo BASE_URL; ?>/includes/watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'remove_from_watchlist', arguments: [<?php echo($auction_id);?>]},

    success: 
      function (obj, textstatus) {
        try {
            var objT = JSON.parse(obj).status;
            if (objT == "success") {
              $("#watch_watching").hide();
              $("#watch_nowatch").show();
            } else {
              alert("Operation failed: " + objT);
            }
        } catch(e) {
            console.error("Invalid JSON response", obj);
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  });
} 
</script>
