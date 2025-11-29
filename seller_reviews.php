<?php require_once("utilities.php")?>

<div class="container">


<?php
//if $seller_id is already set by my_profile, use it
//if not check the url
//if neither then get the session 
if (!isset($seller_id)) {
  if (isset($_GET['seller_id'])) {
    $seller_id = $_GET['seller_id'];
  }elseif (isset($_SESSION['seller_id'])){
    $seller_id = $_SESSION['seller_id'];
  }
}


$review_type = 'Seller';
if (isset($_GET['buyer_id'])) {
  $id = $_GET['buyer_id'];
  $review_type = 'Buyer';
}

if (!$seller_id) {
    die("No seller specified.");
}
else {
  // get username from seller_id 
  $query = $connection->prepare(
    "SELECT u.username, s.avg_seller_rating
    FROM users AS u 
    JOIN seller AS s 
    ON u.user_id = s.user_id 
    WHERE s.seller_id = ?"
  );
  $query->bind_param("i", $seller_id);
  $query->execute();
  $query->bind_result($seller_username, $seller_avg_rating);
  $query->fetch();
  $query->close();
}


  echo ('<h2 class="my-3">'.  $review_type . ' reviews</h2>');
  echo('<p class="text-muted">' . 'Average Rating: ' . $seller_avg_rating . '</p>' );
  


if (isset($_SESSION['seller_id'])): ?>
  
  <table class="table table-bordered table-striped">
      <thead class="thead-light">
          <tr>
              <th style="width: 25%">Item</th>
              <th style="width: 20%">Rating</th>
              <th style="width: 40%">Feedback</th>
          </tr>
      </thead>
      <tbody>
      <?php
      $query = $connection->prepare(
        " SELECT i.item_id,i.title,u.username AS buyer_name, t.seller_rating AS rating, t.seller_comments AS comment, t.transaction_id
          FROM transaction AS t
          JOIN bids AS b ON t.bid_id = b.bid_id
          JOIN buyer AS byr ON b.buyer_id = byr.buyer_id
          JOIN users AS u ON byr.user_id = u.user_id
          JOIN auction AS a On b.auction_id = a.auction_id 
          JOIN item AS i ON a.item_id = i.item_id
          WHERE a.seller_id = ? AND t.seller_rating IS NOT NULL 
          ORDER BY b.date DESC
      ");
      $query->bind_param("i", $seller_id);
      $query->execute();
      $result = $query->get_result();

      if ($result->num_rows == 0):?>
          <tr><td colspan="4">This seller has no reviews yet.</td></tr>
      <?php else:
          while ($row=$result->fetch_assoc()): ?>
              <tr>
                <td class="align-middle">
                    <a href="listing.php?item_id=<?php echo $row['item_id'] ?>" class="font-weight-bold">
                        <?php echo $row['title'];?>
                    </a>
                    <br>
                    <small class="text-muted">Buyer: <?php echo $row['buyer_name'];?></small>
                </td>
                    
                <td class="align-middle">
                    <?php echo$row['rating'] . " / 5";?>
                        
                </td>

                <td class="align-middle">
                    <?php echo $row['comment'];?>
          </td>
            </tr>
          <?php endwhile;
      endif;
      $query->close();
      ?>
      </tbody>
    </table>
  <?php endif;?>



