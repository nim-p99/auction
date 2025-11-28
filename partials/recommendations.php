<?php
// Note: This is a partial loaded by buyer.php.

$current_buyer_id = $_SESSION['buyer_id'] ?? null;

if (!$current_buyer_id) {
    echo "<div class='alert alert-info'>Please register as a buyer to see recommendations.</div>";
    return;
}

// Page setup
$curr_page = $_GET['page'] ?? 1;
$max_recommendations = 10;

// 1. Check if user has bids
$bid_check = $connection->prepare("SELECT 1 FROM bids WHERE buyer_id = ? LIMIT 1");
$bid_check->bind_param("i", $current_buyer_id);
$bid_check->execute();
$bid_check_result = $bid_check->get_result();

$use_popular_items = ($bid_check_result->num_rows === 0);
$bid_check->close();


if ($use_popular_items) {
    // ======================================================
    // SCENARIO A: POPULAR ITEMS (User has 0 bids)
    // ======================================================
    
    $popular_items = $connection->prepare("
        SELECT
            a.auction_id, a.start_bid, a.reserve_price,
            a.buy_now_price, a.start_date_time, a.end_date_time,
            i.item_id, i.title, i.description, i.photo_url, i.item_condition,
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
    
    if ($popular_items->execute()) {
        $pop_items_result = $popular_items->get_result();

        if ($pop_items_result->num_rows == 0) {
            echo "<div class='alert alert-info'>No active popular auctions found.</div>";
        } else {
            echo "<div class='alert alert-info'>Since you haven't bid on anything yet, here are the most popular items!</div>";
            list_table_items($pop_items_result);
        }
    } else {
        echo "<div class='alert alert-danger'>SQL Error: " . $popular_items->error . "</div>";
    }
    
    $popular_items->close();

} else {
    // ======================================================
    // SCENARIO B: COLLABORATIVE FILTERING (User has bids)
    // ======================================================
    
    // 1. Find similar users
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
    $similar_users_query->close();

    $found_specific_recs = false;

    // 2. If we found similar users, TRY to get specific recommendations
    if (!empty($similar_user_ids)) {

        $placeholders = implode(',', array_fill(0, count($similar_user_ids), '?'));

        $recommendations_sql = "
            SELECT
                a.auction_id, a.start_bid, a.reserve_price,
                a.buy_now_price, a.start_date_time, a.end_date_time,
                i.item_id, i.title, i.description, i.photo_url, i.item_condition,
                COUNT(DISTINCT bid_table.bid_id) AS num_bids,
                GREATEST(a.start_bid, COALESCE(MAX(bid_table.amount), 0)) AS current_price,
                COUNT(DISTINCT similar_bids.buyer_id) AS similar_user_bids
            FROM bids similar_bids
            JOIN auction a ON similar_bids.auction_id = a.auction_id
            JOIN item i ON a.item_id = i.item_id
            LEFT JOIN bids bid_table ON bid_table.auction_id = a.auction_id
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
        
        if ($recommendations_query) {
            $types = str_repeat('i', count($similar_user_ids)) . 'ii';
            $params = array_merge($similar_user_ids, [$current_buyer_id, $max_recommendations]);
            $recommendations_query->bind_param($types, ...$params);
            $recommendations_query->execute();
            $recommendations_result = $recommendations_query->get_result();
            
            if ($recommendations_result->num_rows > 0) {
                // success - we found specific recommendations
                echo "<div class='alert alert-success'>Based on users with similar taste:</div>";
                list_table_items($recommendations_result);
                $found_specific_recs = true;
            }
            $recommendations_query->close();
        }
    }

    // 3. Fallback: Show Popular Items if no specific recs found
    if (!$found_specific_recs) {
        
        echo "<div class='alert alert-secondary'>No specific recommendations found. Here are some trending items you might like:</div>";

        $popular_items = $connection->prepare("
            SELECT
                a.auction_id, a.start_bid, a.reserve_price,
                a.buy_now_price, a.start_date_time, a.end_date_time,
                i.item_id, i.title, i.description, i.photo_url, i.item_condition,
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

        if ($pop_items_result->num_rows == 0) {
            echo "<div class='alert alert-info'>No recommendations available at all right now.</div>";
        } else {
            list_table_items($pop_items_result);
        }
        $popular_items->close();
    }
}
?>
