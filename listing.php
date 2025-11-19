
<?php 
session_start();
include_once("header.php");
require("utilities.php");
require_once("database.php");


if (isset($_SESSION['error_message'])) {
    echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
    unset($_SESSION['success_message']);
}

  // Get info from the URL:
  $item_id = $_GET['item_id'];

  // Get auction id from item id that is in URL (one auctionid for each itemid)
   $get_auctionID = "SELECT auction_id FROM auction WHERE item_id= ?";
        $stmt = $connection->prepare($get_auctionID);
        $stmt->bind_param("i", $item_id); // 
        $stmt->execute();
        $result = $stmt->get_result();

         if ($row=$result->fetch_assoc()) {
            $auction_id = $row["auction_id"];
         }
    //gets All auctions columns and only item title/description / joined item and auctions tables
    $auction_sql = "SELECT a.*, i.title, i.description 
        FROM auction a
        JOIN item i ON a.item_id = i.item_id
        WHERE a.auction_id = ?";

$stmt = $connection->prepare($auction_sql);
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$auction = $stmt->get_result()->fetch_assoc();

// gets highest bid for auction
    $highest_bid_sql = "SELECT MAX(amount)as highest_bid 
        FROM bids
        WHERE auction_id = ?";

        $stmt = $connection->prepare($highest_bid_sql);
        $stmt->bind_param("i", $auction_id);
        $stmt->execute();
        $result = $stmt ->get_result();
        $row=$result->fetch_assoc();

        $highest_bid=$row["highest_bid"];
         // highest bid not empty then current price becomes highest bid, can else once we decide what default willl be
        if ($highest_bid !== null) {
          $auction["current_price"]= $highest_bid;
        }


// calculates/convert time
$get_end_time = $auction["end_date_time"];
$end_time = new DateTime($get_end_time);
$now = new DateTime();



?>
<h2><?php echo $auction["title"]; ?></h2>
<p><?php echo $auction["description"]; ?></p>
<?php
    
        


// TODO: Use item_id to make a query to the database.
// extract seller_id for seller profile, remove example below
$seller_id = "spiderman";
 

  // TODO: Note: Auctions that have ended may pull a different set of data,
  //       like whether the auction ended in a sale or was cancelled due
  //       to lack of high-enough bids. Or maybe not.
  
  // Calculate time to auction end:
  $now = new DateTime();
  
  if ($now < $end_time) {
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = ' (in ' . display_time_remaining($time_to_end) . ')';
  }
  
  // TODO: If the user has a session, use it to make a query to the database
  //       to determine if the user is already watching this item.
  //       For now, this is hardcoded.
  $has_session = true;
  $watching = false;
?>


<div class="container">

<div class="row"> <!-- Row #1 with auction title + watch button -->
  <div class="col-sm-8"> <!-- Left col -->
    <!--<h2 class="my-3"><?php echo($title); ?></h2>-->
  </div>
  <div class="col-sm-4 align-self-center"> <!-- Right col -->
<?php
  /* The following watchlist functionality uses JavaScript, but could
     just as easily use PHP as in other places in the code */
  if ($now < $end_time):
?>
    <div id="watch_nowatch" <?php if ($has_session && $watching) echo('style="display: none"');?> >
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to watchlist</button>
    </div>
    <div id="watch_watching" <?php if (!$has_session || !$watching) echo('style="display: none"');?> >
      <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
      <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
    </div>
<?php endif /* Print nothing otherwise */ ?>
  </div>
</div>

<div class="row"> <!-- Row #2 with seller profile button -->
  <div class="col-sm-4">
    <?php echo('<a href="seller_profile.php?seller_id=' . $seller_id . '">Seller Profile</a>');?>
  </div>
</div>

<div class="row"> <!-- Row #3 with auction description + bidding info -->
  <div class="col-sm-8"> <!-- Left col with item info -->

    <div class="itemDescription">
   <!-- <?php echo($description); ?> -->
    </div>

  </div>

  <div class="col-sm-4"> <!-- Right col with bidding info -->

    <p><strong>Buy Now Price:</strong> £<?php echo number_format($auction["buy_now_price"], 2); ?></p>
    <p><strong>Auction Ends On:</strong> <?php echo $auction["end_date_time"]; ?></p>
    <p><strong>Current Bid:</strong> £<?php echo number_format($auction["current_price"], 2); ?></p>

<?php if ($now < $end_time): ?>
    <!-- Bidding form -->
    <form method="POST" action="place_bid.php">
      <input type="hidden" name="auction_id" value="<?php echo $auction_id; ?>">
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text">£</span>
        </div>
	    <input type="number" class="form-control" id="bid" name="bid" step="0.1" required>
      </div>
      <button type="submit" class="btn btn-primary form-control">Place bid</button>
    </form>
<?php endif ?>

  
  </div> <!-- End of right col with bidding info -->

</div> <!-- End of row #2 -->



<?php include_once("footer.php")?>


<script> 
// JavaScript functions: addToWatchlist and removeFromWatchlist.

function addToWatchlist(button) {
  console.log("These print statements are helpful for debugging btw");

  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'add_to_watchlist', arguments: [<?php echo($item_id);?>]},

    success: 
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();
 
        if (objT == "success") {
          $("#watch_nowatch").hide();
          $("#watch_watching").show();
        }
        else {
          var mydiv = document.getElementById("watch_nowatch");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Add to watch failed. Try again later."));
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
    data: {functionname: 'remove_from_watchlist', arguments: [<?php echo($item_id);?>]},

    success: 
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();
 
        if (objT == "success") {
          $("#watch_watching").hide();
          $("#watch_nowatch").show();
        }
        else {
          var mydiv = document.getElementById("watch_watching");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Watch removal failed. Try again later."));
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func
</script>
