<?php
// you can only get here from buyer.php
// utilities.php is already loaded by buyer.php, so don't need

$current_buyer_id = $_SESSION['buyer_id'];

  // page setup
  if (!isset($_GET['page'])) {
    $curr_page = 1;
  } else {
    $curr_page = $_GET['page'];
  }
  $results_per_page = 5;
  $max_recommendations = 10;


  // check if user has 1 BID, if 0 then shows popular items for recs (highest # of bids)
    $bid_check = $connection->prepare("
   SELECT 1
   FROM bids
   WHERE buyer_id = ?
   LIMIT 1
");
$bid_check->bind_param("i", $current_buyer_id);
$bid_check->execute();

$bid_check_result = $bid_check->get_result();



if ($bid_check_result->num_rows === 0) {
    // NO BIDS SO WE USE POPULAR ITEMS
    $use_popular_items = true;
} else {
    // yuser has at least 1 bid
    $use_popular_items = false;
}
 //FALLBACK
if ($use_popular_items){
    $popular_items=$connection->prepare("
    SELECT
      a.auction_id, a.start_bid, a.reserve_price,
        a.buy_now_price, a.start_date_time, a.end_date_time,
        i.item_id, i.title, i.description,
        COUNT(b.bid_id) AS num_bids,
        GREATEST(a.start_bid, COALESCE(MAX(b.amount), 0)) AS current_price
      FROM auction a
      JOIN item i ON a.item_id = i.item_id
      LEFT JOIN bids b ON b.auction_id = a.auction_id
      WHERE a.end_date_time > NOW()
        AND a.is_active = TRUE
      GROUP BY a.auction_id
      HAVING num_bids > 0
      ORDER BY num_bids DESC, a.end_date_time ASC
      LIMIT ?
    ");

    $popular_items->bind_param("i", $max_recommendations);
    $popular_items->execute();
    $pop_items_result = $popular_items->get_result();

    // Display using same asbrowse file
    list_table_items($pop_items_result);

} else {
  // find similar users who share at least 2 bid items with current user
  // this query only finds similar users / not the auctions
    $similar_users_query = $connection->prepare("
      SELECT b2.buyer_id, COUNT(DISTINCT b1.auction_id) AS shared_auctions
      FROM bids b1
      JOIN bids b2 ON b1.auction_id = b2.auction_id
      WHERE b1.buyer_id = ?
        AND b2.buyer_id != ?
      GROUP BY b2.buyer_id
      HAVING shared_auctions >= 2
      ORDER BY shared_auctions DESC
      LIMIT 5
    ");
    $similar_users_query->bind_param("ii", $current_buyer_id, $current_buyer_id);
    $similar_users_query->execute();
    $similar_users_result = $similar_users_query->get_result();

    $similar_user_ids = [];
    while ($row = $similar_users_result->fetch_assoc()) {
      $similar_user_ids[] = $row['buyer_id'];
    }

    // If no similar users found (but has bids), fall back to popular items
    if (empty($similar_user_ids)) {
      $popular_items=$connection->prepare("
    SELECT
      a.auction_id, a.start_bid, a.reserve_price,
        a.buy_now_price, a.start_date_time, a.end_date_time,
        i.item_id, i.title, i.description,
        COUNT(b.bid_id) AS num_bids,
        GREATEST(a.start_bid, COALESCE(MAX(b.amount), 0)) AS current_price
      FROM auction a
      JOIN item i ON a.item_id = i.item_id
      LEFT JOIN bids b ON b.auction_id = a.auction_id
      WHERE a.end_date_time > NOW()
        AND a.is_active = TRUE
        AND a.auction_id NOT IN (
          SELECT auction_id FROM bids WHERE buyer_id = ?
        )
      GROUP BY a.auction_id
      HAVING num_bids > 0
      ORDER BY num_bids DESC, a.end_date_time ASC
      LIMIT ?
    ");

      $popular_items->bind_param("ii", $current_buyer_id, $max_recommendations);
      $popular_items->execute();
      $pop_items_result = $popular_items->get_result();

      list_table_items($pop_items_result);
    } else {

      // recommendations based on similar users
      $placeholders = implode(',', array_fill(0, count($similar_user_ids), '?'));

      $recommendations_sql = "
        SELECT
          a.auction_id, a.start_bid, a.reserve_price,
          a.buy_now_price, a.start_date_time, a.end_date_time,
          i.item_id, i.title, i.description,
          COUNT(bid_table.bid_id) AS num_bids,
         MAX(bid_table.amount) AS current_price,
          COUNT(DISTINCT similar_bids.buyer_id) AS similar_user_bids
        FROM bids similar_bids
        JOIN auction a ON similar_bids.auction_id = a.auction_id
        JOIN item i ON a.item_id = i.item_id
        JOIN bids bid_table ON bid_table.auction_id = a.auction_id
        WHERE similar_bids.buyer_id IN ($placeholders)
          AND a.end_date_time > NOW()
          AND a.is_active = TRUE
          AND a.auction_id NOT IN (
            SELECT auction_id FROM bids WHERE buyer_id = ?
          )
        GROUP BY a.auction_id
        ORDER BY similar_user_bids DESC, a.end_date_time ASC
        LIMIT ?
      ";

      $recommendations_query = $connection->prepare($recommendations_sql);

      // Bind parameters: similar_user_ids + current_buyer_id + max_recommendations
      $types = str_repeat('i', count($similar_user_ids)) . 'ii';
      $params = array_merge($similar_user_ids, [$current_buyer_id, $max_recommendations]);
      $recommendations_query->bind_param($types, ...$params);

      $recommendations_query->execute();
      $recommendations_result = $recommendations_query->get_result();
      $recommendations_query->close();

      // Display recommendations
      list_table_items($recommendations_result);
    }
}
?>
