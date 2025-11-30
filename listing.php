<?php include_once("header.php")?>
<?php require("utilities.php")?>

<?php
  
  if (isset($_SESSION['error_message'])) {
    echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
    unset($_SESSION['error_message']);
  }

  if (isset($_SESSION['success_message'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
    unset($_SESSION['success_message']);
  }

  $user_id = $_SESSION['user_id'];
  // Get info from the URL:
  $item_id = $_GET['item_id'];
  $seller_id = null;
  $auction_id = null;

// Get Auction details 

  $query = $connection->prepare("
    SELECT seller_id, auction_id
    FROM auction
    WHERE item_id = ?");
  $query->bind_param("i", $item_id);
  $query->execute();
  $query->bind_result($seller_id, $auction_id);

  if (!$query->fetch()) {
    // no row found --> redirect to error page 
    $query->close();
    header("Location: /error.php?error_id=1");
    exit();
  }
  $query->close();

  # can fine tune this query - dont need all rows from auction 
  $auction_sql = "SELECT a.*, i.title, i.description, i.photo_url, i.item_condition 
        FROM auction a
        JOIN item i ON a.item_id = i.item_id
        WHERE a.auction_id = ?";
  $query = $connection->prepare($auction_sql);
  $query->bind_param("i", $auction_id);
  $query->execute();
  $auction = $query->get_result()->fetch_assoc();

    // Prepare photo URLs (supports JSON array or single string)
  $photo_urls = [];
  if (!empty($auction['photo_url'])) {
    $decoded = json_decode($auction['photo_url'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
      // New format: JSON array of URLs
      $photo_urls = $decoded;
    } else {
      // Old format: single URL string
      $photo_urls = [$auction['photo_url']];
    }
  }



    // calculates / converts time
  $get_end_time = $auction["end_date_time"];
  $end_time = new DateTime($get_end_time);
  
  if (isset($auction['start_date_time'])) {
      $start_time = new DateTime($auction['start_date_time']);
  } elseif (isset($auction['start_date'])) {
      $start_time = new DateTime($auction['start_date']);
  } else {
      // If no start time exists in DB, assume it started in the past (always active)
      $start_time = new DateTime("2000-01-01");
  }

  //testing, remove last line to stop testing!!
  $now= new DateTime();


  //$now->modify("+5 years");


  
  

  // get highest bid 
  $highest_bid_sql = "
    SELECT MAX(amount) AS highest_bid
    FROM bids
    WHERE auction_id = ?";

  $query = $connection->prepare($highest_bid_sql);
  $query->bind_param("i", $auction_id);
  $query->execute();
  $result = $query->get_result();
  $row = $result->fetch_assoc();

  $highest_bid = $row["highest_bid"];
  // highest bid not empty then current price becomes highest bid
  if ($highest_bid == null) {
    $highest_bid = 0;
  }

  // get winning bidder if auction ended
  $winning_bidder = null;
  if ($now>$end_time && $highest_bid > 0) { // added 0 to make sure at least 1 bid placed
    $winner_id_query =$connection->prepare("
      SELECT bd.amount, bd.buyer_id, u.email, u.first_name, i.title, a.mail_sent, bd.bid_id
      FROM bids AS bd
      JOIN buyer AS b ON b.buyer_id = bd.buyer_id
      JOIN users AS u ON b.user_id = u.user_id
      JOIN auction AS a ON a.auction_id = bd.auction_id
      JOIN item AS i ON a.item_id = i.item_id
      Where bd.auction_id =? AND bd.amount =?
      ORDER BY date asc
      LIMIT 1
    ");
    $winner_id_query->bind_param("id", $auction_id, $highest_bid);
    $winner_id_query->execute();
    $winner_result = $winner_id_query->get_result();
  
    if (($winner_row = $winner_result->fetch_assoc()) && $winner_row['mail_sent'] !== 1) {
        $winner_id_query->close();

        #----- transaction update -----#
          #-checking if transcation for this item already exists --> preventing duplicates

        $check =$connection->prepare("
          SELECT transaction_id FROM transaction WHERE bid_id = ?
        ");
        $check->bind_param("i", $winner_row['bid_id']);
        $check->execute();
        $check_result = $check->get_result();

        if (!$check_result->fetch_assoc()) {
          $check->close();        
          $trans_query = $connection->prepare("
            INSERT INTO transaction (bid_id) 
            VALUES (?)");
          $trans_query-> bind_param("i", $winner_row['bid_id']);
          $trans_query->execute();
          $transaction_id = $trans_query->insert_id;
          $trans_query->close(); 
        }
        

        #-----email buyer -----#

        $winning_bidder_id = $winner_row['buyer_id'];
        $winning_bidder_name = ucfirst($winner_row['first_name']);
        $winning_bidder_item = $winner_row['title'];
        $winning_bidder_bid_amount =$winner_row['amount'];
        
        $to = $winner_row['email'];
        $subject = "You WON!!!";
        $message = "
        Dear {$winning_bidder_name},

        Congratulations! You won the auction: '{$winning_bidder_item}'. 
        With a bid of £{$winning_bidder_bid_amount}.

        From 
        The Auction Site
        ";
        $headers= "From: The Auction Site";

        if(mail($to, $subject, $message, $headers)) {
          //if the mail sends update auction table to say mail sent
          $update_mail_query = $connection->prepare("
          UPDATE auction
          SET mail_sent = 1
          WHERE auction_id = ?");
          $update_mail_query -> bind_param("i", $auction_id);
          $update_mail_query->execute();
          $update_mail_query->close();
        }

      #------emailing seller -----#
      $seller_query = $connection->prepare("
          SELECT u.first_name, u.email, a.mail_sent
          FROM auction AS a
          JOIN seller AS s ON a.seller_id = s.seller_id
          JOIN users AS u ON s.user_id = u.user_id
          WHERE a.auction_id = ?
      ");
          $seller_query-> bind_param("i", $auction_id);
          $seller_query->execute();
          $seller_result = $seller_query->get_result();
          if($seller_row = $seller_result->fetch_assoc()){
            $seller_name = ucfirst($seller_row['first_name']);
            $to = $seller_row['email'];
            $subject = "SOLD: {$winning_bidder_item}";
            $message = "
            Dear {$seller_name},

            Congratulations! Your auction for: '{$winning_bidder_item}'. 
            Was bought with a bid of £{$winning_bidder_bid_amount}.
            
            From 
            The Auction Site
            ";
            $headers= "From: The Auction Site";

            $headers= "From: The Auction Site";
            mail($to, $subject, $message, $headers);
          }
          $seller_query->close();


      
      #-----sending updates to people watching it
      $watchlist_query= $connection->prepare("
          SELECT u.email, u.first_name
          FROM watchlist AS w
          JOIN users AS u ON w.user_id = u.user_id
          JOIN auction AS a ON w.auction_id = a.auction_id
          WHERE w.auction_id = ?
      ");
      $watchlist_query-> bind_param("i", $auction_id);
      $watchlist_query-> execute();
      $watchlist_result = $watchlist_query->get_result();
      
      while ($watcher_row=$watchlist_result->fetch_assoc()){
          $watcher_name = ucfirst($watcher_row['first_name']);
          $to = $watcher_row['email'];
          $message ="
          To {$watcher_name},

          Someone won the auction for '{$winning_bidder_item}' that you are watching. With a bid of £{$winning_bidder_bid_amount}.
          
          If you wish to stop recieving updates, please remove this item from your watchlist.

          From The Auction_Site
          ";
          $subject = "Update: New activity on '{$winning_bidder_item}'";
          $headers = "From: the auction_site";
          $headers .= "Content-type: text/plain; charset=UTF-8";
          mail($to, $subject, $message, $headers); 
      }
      $watchlist_query->close();
    }
  }
    



  // TODO: Note: Auctions that have ended may pull a different set of data,
  //      like whether the auction ended in a sale or was cancelled due
  //      to lack of high-enough bids. Or maybe not.
  
  // Calculate time to auction end:
  if ($now < $end_time) {
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = ' (in ' . display_time_remaining($time_to_end) . ')';
  }
  
  // TODO: If the user has a session, use it to make a query to the database
  //      to determine if the user is already watching this item.
  //      For now, this is hardcoded. 
  $has_session = true;
  
  $query = $connection->prepare("SELECT watchlist_id FROM watchlist WHERE user_id = ? AND auction_ID = ?");
  $query->bind_param("ii", $user_id, $auction_id);
  $query->execute();
  $query->store_result();
  $watching = $query->num_rows > 0;
  $query->close();

?>


<div class="container">

<div class="row"> <div class="col-sm-8"> <h2 class="my-3"><?php echo($auction['title']); ?></h2>
  <?php if (!empty($auction['item_condition'])): ?>
      <p class="text-muted mb-1">
        <strong>Condition:</strong>
        <?php
          $raw_condition = $auction['item_condition'];
          $pretty_condition = ucwords(str_replace('_', ' ', $raw_condition));
          echo htmlspecialchars($pretty_condition);
        ?>
      </p>
    <?php endif; ?>
  </div>
  <div class="col-sm-8"> 
    <p class="my-3"><?php echo($auction['description']); ?></p>
  <?php if (!empty($photo_urls)): ?>
      <div class="d-flex flex-wrap mt-3">
        <?php foreach ($photo_urls as $url): ?>
          <img
            src="<?php echo htmlspecialchars($url); ?>"
            alt="Photo of <?php echo htmlspecialchars($auction['title']); ?>"
            class="img-thumbnail mr-2 mb-2"
            style="max-width: 150px; max-height: 150px;">
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  <div class="col-sm-4 align-self-center"> <?php
  echo('<a href="seller_profile.php?seller_id=' . $seller_id . '">Seller Profile</a>');
  /* The following watchlist functionality uses JavaScript, but could
     just as easily use PHP as in other places in the code */

  if ($now < $end_time):
    // user logged in and not admin 
    if (isset($_SESSION['user_id']) && $_SESSION['user_id']!==1): ?>
      <div id="watch_nowatch" <?php if ($has_session && $watching) echo('style="display: none"');?> >
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to watchlist</button>
      </div>
      <div id="watch_watching" <?php if (!$has_session || !$watching) echo('style="display: none"');?> >
        <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
      </div>
    <?php //user not logged in 
    elseif (!isset($_SESSION['user_id'])): ?>
      <div>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#loginModal">+ Add to watchlist</button>
      </div>
    <?php endif;?>
<?php endif ?>
  </div>
</div>



<div class="row"> <div class="col-sm-8"> </div>

  <div class="col-sm-4"> <?php if (!is_null($auction["buy_now_price"]) && $now < $end_time && $now >= $start_time): ?>
      <p></p>

      <?php if (isset($_SESSION['user_id'])): ?>
      <form method="POST" action="buy_now.php" onsubmit="return confirm('Are you sure you want to buy this item now for £<?php echo number_format($auction['buy_now_price'], 2); ?>?');">
        <input type="hidden" name="auction_id" value="<?php echo $auction_id; ?>">
        <button type="submit" class="btn-info btn-sm">Buy Now at: £<?php echo number_format($auction["buy_now_price"], 2); ?></button>
      </form>
      <?php else: ?>
        <button type="button" class="btn-info btn-sm" data-toggle="modal" data-target="#loginModal">Buy Now at: £<?php echo number_format($auction["buy_now_price"], 2); ?></button>
      <?php endif; ?>


    <?php endif; ?>
   
    <?php if ($now > $end_time): ?>
     <p>This auction ended <?php echo(date_format($end_time, 'j M H:i'))?></p>
     <p>Winning bid: £<?php echo(number_format($highest_bid, 2))?></p>
     <?php elseif ($now < $start_time): ?>
     <p>This auction starts on <?php echo(date_format($start_time, 'j M H:i'))?></p>
     <p class="lead">Starting bid: £<?php echo(number_format($auction['start_bid'], 2)) ?></p>
<?php else: ?>
     <p>Auction ends <?php echo(date_format($end_time, 'j M H:i') . $time_remaining) ?></p>  
    <p class="lead">Current bid: £<span id="current_price_display"><?php echo(number_format($highest_bid, 2)) ?></span></p>

    <?php
    // user logged in  
     if(isset($_SESSION['user_id']) && $_SESSION['user_id']!==1){ ?>
      <form method="POST" action="place_bid.php">
        <input type="hidden" name="auction_id" value="<?php echo $auction_id; ?>">
        <input type="hidden" name="highest_bid" id = "highest_bid_input" value="<?php echo $highest_bid; ?>">
        <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text">£</span>
          </div>
        <input type="number" class="form-control" id="bid" name="bid" step="0.1" required>
        </div>
        <button type="submit" class="btn btn-primary form-control">Place bid</button>
      </form>
    <?php // user not logged in 
    } elseif (!isset($_SESSION['user_id'])) {?>
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text">£</span>
          </div>
        <input type="number" class="form-control" id="bid" name="bid" step="0.1" placeholder="Login to bid" disabled>
        </div>
        <button type="button" class="btn btn-primary form-control" data-toggle="modal" data-target="#loginModal">Login to Place Bid</button>
    <?php } ?>
    
<?php endif ?>

  
  </div> </div> <div class="row mt-4">
  <div class="col-12">
    <h4>Bid History</h4>
    <div id="history-container">
    <?php
      // gets bid history for this auction
      $bid_history_sql = "
        SELECT b.amount, b.date, u.username, b.buyer_id
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

      // sdisplays bid history using function in utilties 
      list_bid_history($bid_history_result);

      $bid_history_query->close();
    ?>
    </div>
  </div>
</div>


<?php include_once("footer.php")?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script> 
// JavaScript functions: addToWatchlist and removeFromWatchlist.

function addToWatchlist(button) {
  console.log("These print statements are helpful for debugging btw");

  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'add_to_watchlist', arguments: [<?php echo($auction_id);?>]},

    success: 
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        console.log("ajax raw response: ", obj);
        //var objT = obj.trim();
        var objT = JSON.parse(obj).status;
        console.log("parsed status:", objT);

 
        if (objT == "success") {
          $("#watch_nowatch").hide();
          $("#watch_watching").show();
        }
        else {
          alert("Operation failed: " + objT);
          /* var mydiv = document.getElementById("watch_nowatch"); */
          /* mydiv.appendChild(document.createElement("br")); */
          /* mydiv.appendChild(document.createTextNode("Add to watch failed. Try again later.")); */
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func

function removeFromWatchlist(button) {
  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'remove_from_watchlist', arguments: [<?php echo($auction_id);?>]},

    success: 
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        //var objT = obj.trim();
        var objT = JSON.parse(obj).status;
 
        if (objT == "success") {
          $("#watch_watching").hide();
          $("#watch_nowatch").show();
        }
        else {
          alert("Operation failed: " + objT);
          /* var mydiv = document.getElementById("watch_watching"); */
          /* mydiv.appendChild(document.createElement("br")); */
          /* mydiv.appendChild(document.createTextNode("Watch removal failed. Try again later.")); */
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func

</script>

<script>
  //updating highest bid automatically
  $(document).ready(function() {
    var auctionId = <?php echo $auction_id; ?>;

    setInterval(function(){
      $.ajax({
        url: 'get_latest_bid.php',
        type: 'GET',
        data: {auction_id: auctionId},
        dataType: 'json',
        success: function(data) {
          if (data && data.amount){
            var newHighestBid = parseFloat(data.amount);

            var currentDisplayPrice = parseFloat($("#current_price_display").text().replace(/,/g, ''));
            
            //update if price changed
            if (newHighestBid > currentDisplayPrice){
              //update visible text
              $('#current_price_display').text(newHighestBid.toFixed(2));
              //update hidden input
              $('#highest_bid_input').val(newHighestBid);
              console.log("Price updated to: " + newHighestBid);
          
            }
          }
          if (data && data.history) {
             $("#history-container").html(data.history);
          }
        },
      });
    }, 2000); // run every second
  });
</script>
