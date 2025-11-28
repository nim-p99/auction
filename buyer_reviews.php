<?php require_once("utilities.php")?>

<div class="container">


<?php
//if $seller_id is already set by my_profile, use it
//if not check the url
//if neither then get the session 
if (!isset($buyer_id)) {
  $buyer_id = $_GET['buyer_id'] ?? $buyer_id = $_SESSION['buyer_id'] ?? NULL;
  }



if (!$buyer_id){
  echo '<div class="alert alert-danger my-3">No buyer profile specified.</div>';
}else {
  // get username from seller_id 
  $query = $connection->prepare(
    "SELECT u.username, b.avg_buyer_rating
    FROM users AS u 
    JOIN buyer AS b 
    ON u.user_id = b.user_id 
    WHERE b.buyer_id = ?"
  );
  $query->bind_param("i", $buyer_id);
  $query->execute();
  $query->bind_result($buyer_username, $buyer_avg_rating);
  $query->fetch();
  $query->close();
}


  echo ('<h2 class="my-3">'. htmlspecialchars($buyer_username). ' Buyer Reviews</h2>');
  echo('<p class="text-muted">' . 'Average Rating: ' . $buyer_avg_rating . '</p>' );

?>
  
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
    " SELECT i.item_id,i.title,u.username AS seller_name, t.buyer_rating AS rating, t.buyer_comments AS comment, t.transaction_id
      FROM transaction AS t
      JOIN bids AS b ON t.bid_id = b.bid_id
      JOIN auction AS a On b.auction_id = a.auction_id 
      JOIN seller AS s ON a.seller_id = s.seller_id 
      JOIN users AS u ON s.user_id = u.user_id
      JOIN item AS i ON a.item_id = i.item_id
      WHERE b.buyer_id = ? AND t.buyer_rating IS NOT NULL 
      ORDER BY b.date DESC
  ");
  $query->bind_param("i", $buyer_id);
  $query->execute();
  $result = $query->get_result();

  if ($result->num_rows == 0):?>
      <tr><td colspan="4">This buyer has no reviews yet.</td></tr>
  <?php else:
      while ($row=$result->fetch_assoc()): ?>
          <tr>
            <td class="align-middle">
                <a href="listing.php?item_id=<?php echo $row['item_id'] ?>" class="font-weight-bold">
                    <?php echo $row['title'];?>
                </a>
                <br>
                <small class="text-muted">Seller: <?php echo $row['seller_name'];?></small>
            </td>
                
            <td class="align-middle">
                <?php echo$row['rating'] . " / 5" ;?>
                    
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




<?php include_once("footer.php")?>
