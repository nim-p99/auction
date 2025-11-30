<?php require_once("utilities.php")?>

<div class="container">

<?php

// Recently Viewed items using the `recent_items` cookie

// If user has not accepted cookies or the cookie isn't there,
// we can't show anything useful.
$hasCookieConsent = (isset($_COOKIE['cookie_consent']) && $_COOKIE['cookie_consent'] === 'accept');

if (!$hasCookieConsent) {
    echo "<p class='mt-3'>We can only show your recently viewed items if you accept cookies and view some listings.</p>";
} else {

    $recentItems = [];

    if (!empty($_COOKIE['recent_items'])) {
        $decoded = json_decode($_COOKIE['recent_items'], true);
        if (is_array($decoded)) {
            foreach ($decoded as $id) {
                $id = (int)$id;
                if ($id > 0) {
                    $recentItems[] = $id;
                }
            }
        }
    }

    // Remove duplicates while preserving order
    $recentItems = array_values(array_unique($recentItems));

    if (empty($recentItems)) {
        echo "<p class='mt-3'>You haven't viewed any items recently on this device.</p>";
    } else {
        // Build a query to fetch auctions for these item IDs
        // and exclude items listed by the current user (if they're a seller).
        $placeholders = implode(',', array_fill(0, count($recentItems), '?'));

        $currentSellerId = isset($_SESSION['seller_id']) ? (int)$_SESSION['seller_id'] : null;

        $sql = "
          SELECT 
            a.auction_id, a.start_bid, a.reserve_price,
            a.buy_now_price, a.start_date_time, a.end_date_time,
            i.item_id, i.title, i.description, i.photo_url, i.item_condition,
            c.category_id, c.category_name,
            COALESCE(MAX(b.amount), 0) AS highest_bid,
            COUNT(b.bid_id) AS num_bids,
            GREATEST(a.start_bid, COALESCE(MAX(b.amount), 0)) AS current_price,
            a.seller_id
          FROM auction AS a
          JOIN item AS i ON a.item_id = i.item_id
          JOIN category AS c ON c.category_id = i.category_id
          LEFT JOIN bids AS b ON b.auction_id = a.auction_id
          WHERE i.item_id IN ($placeholders)
        ";

        $params = $recentItems;
        $types  = str_repeat('i', count($recentItems));

        // If logged in as a seller, exclude their own listings
        if (!is_null($currentSellerId) && $currentSellerId > 0) {
            $sql .= " AND a.seller_id <> ? ";
            $params[] = $currentSellerId;
            $types   .= "i";
        }

        $sql .= " GROUP BY a.auction_id";

        $stmt = $connection->prepare($sql);
        if ($stmt === false) {
            echo "<p class='mt-3 text-danger'>Error preparing query for recently viewed items.</p>";
        } else {
            // Bind parameters dynamically
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                echo "<p class='mt-3'>No recently viewed items to show. 
                      (We don’t include items you have listed yourself.)</p>";
            } else {
                echo '<div class="list-container mt-3">';
                // Re-order the results to match the order in the cookie (most recent first)
                $rowsByItemId = [];
                while ($row = $result->fetch_assoc()) {
                    $rowsByItemId[(int)$row['item_id']] = $row;
                }

                // Build a temporary mysqli_result-like table for list_table_items
                // by iterating in the correct order and printing directly.
                echo '<ul class="list-group">';
                foreach ($recentItems as $itemId) {
                    if (!isset($rowsByItemId[$itemId])) {
                        continue;
                    }
                    $row = $rowsByItemId[$itemId];

                    $item_id        = $row['item_id'];
                    $title          = $row['title'];
                    $description    = $row['description'];
                    $end_date       = new DateTime($row['end_date_time']);
                    $start_date     = new DateTime($row['start_date_time']);
                    $current_price  = $row['current_price'];
                    $num_bids       = $row['num_bids'];
                    $buy_now_price  = $row['buy_now_price'];
                    $auction_id     = $row['auction_id'];

                    // Reuse your existing listing renderer
                    print_listing_li(
                        $item_id,
                        $title,
                        $description,
                        $current_price,
                        $num_bids,
                        $start_date,
                        $end_date,
                        $buy_now_price,
                        false,
                        $auction_id
                    );
                }
                echo '</ul>';
                echo '</div>';
            }

            $stmt->close();
        }
    }
}
?>

</div>