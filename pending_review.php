<?php 
include_once("header.php");
require("utilities.php");
?>

<div class="container my-4">
    <?php
    if (isset($_SESSION['success_message'])){
        echo '<div class - "alert alert-sucess">'.$_SESSION['success_message'].'</div>';
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])){
        echo '<div class - "alert alert-danger">'.$_SESSION['error_message'].'</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <h2 class = "mb-4">Pending Reviews</h2>

    <ul class="nav nav-tabs" id="reviewTabs" role="tablist">
        <li class="nav-item">
            <a class ="nav-link active" id="to-review-tab" data-toggle="tab" href="#toreview" role="tab">Items I Bought</a>
        </li>
        <?php if (isset($_SESSION['seller_id'])): ?>
        <li class="nav-item"> 
            <a class="nav-link" id ="my-sold-tab" data-toggle="tab" href="#mysold" role="tab">Items I Sold</a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="tab-content" id="reviewTabsContent">
        <div class="tab-pane fade show active p-3" id="toreview" role="tabpanel">
            <table class="table table-bordered table-striped">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 25%">Item</th>
                        <th style="width: 20%">Rating</th>
                        <th style="width: 40%">Feedback</th>
                        <th style="width: 15%"></th>
                    </tr>
                </thead>
                <tbody>
                <?php  
                $buyer_id = $_SESSION['buyer_id'];
                //find transactions where I am the buyer but seller_rating is NULL
                $sql_buyer = $connection->prepare("
                    SELECT  t.transaction_id, i.title, i.item_id, a.auction_id, u.username AS seller_name
                    FROM transaction AS t
                    JOIN bids AS b ON t.bid_id = b.bid_id
                    JOIN auction AS a On b.auction_id = a.auction_id
                    JOIN item AS i ON  a.item_id = i.item_id
                    JOIN seller AS s ON a.seller_id = s.seller_id
                    JOIN users AS u ON s.user_id =  u.user_id
                    WHERE b.buyer_id = ? AND t.seller_rating IS NULL
                    GROUP BY a.auction_id
                    ");

                $sql_buyer->bind_param("i", $buyer_id);
                $sql_buyer->execute();
                $result = $sql_buyer->get_result();

                if ($result->num_rows == 0):?>
                    <tr><td colspan="4">You have no pending reviews for items you purchased.</td></tr>
                <?php else:
                    while ($row=$result->fetch_assoc()): 
                        $current_title = $row['title'];
                        $current_subtitle = "Seller: ".$row['seller_name'];?>
                        <?php render_review_card($row['transaction_id'], $current_title, "Seller: ".$current_subtitle, $row['item_id'], 'buyer_reviewing_seller'); ?>
                    <?php endwhile;
                endif;
                $sql_buyer->close();
                ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($_SESSION['seller_id'])): ?>
        <div class="tab-pane fade p-3" id="mysold" role="tabpanel">
            <table class="table table-bordered table-striped">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 25%">Item</th>
                        <th style="width: 20%">Rating</th>
                        <th style="width: 40%">Feedback</th>
                        <th style="width: 15%"></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $seller_id = $_SESSION['seller_id'];
                $sql_seller = $connection->prepare("
                    SELECT t.transaction_id, i.title, i.item_id,u.username AS buyer_name
                    FROM transaction AS t
                    JOIN bids AS b ON t.bid_id = b.bid_id
                    JOIN buyer AS byr ON b.buyer_id = byr.buyer_id
                    JOIN users AS u ON byr.user_id = u.user_id
                    JOIN auction AS a On b.auction_id = a.auction_id 
                    JOIN item AS i ON a.item_id = i.item_id
                    WHERE a.seller_id = ? AND t.buyer_rating IS NULL
                    GROUP BY a.auction_id

                ");

                $sql_seller->bind_parm("i", $seller_id);
                $sql_seller->execute();
                $result = $sql_seller->get_result();

                if ($result->num_rows == 0):?>
                    <tr><td colspan="4">You have no pending reviews for items you sold.</td></tr>
                <?php else:
                    while ($row=$result->fetch_assoc()): ?>
                        <?php render_review_card($row['transaction_id'], $row['title'], "Buyer: ".$row['buyer_name'], $row['item_id'], 'seller_reviewing_buyer'); ?>
                    <?php endwhile;
                endif;
                $sql_buyer->close();
                ?>
                </tbody>
            </table>
        </div>
        <?php endif;?>
    </div>
</div>

<?php
// review card
function render_review_card($transaction_id, $title,$subtitle, $item_id, $review_type){
?>
    <tr>
        <td class="align-middle">
            <a href="listing.php?item_id=<?php echo $item_id; ?>" class="font-weight-bold">
                <?php echo htmlspecialchars($title);?>
            </a>
            <br>
            <small class = "text-muted"><?php echo htmlspecialchars($subtitle);?><small>
        </td>
            
        <form action="process_review.php" method="POST">
            <input type="hidden" name="transaction_id" value="<?php echo $transaction_id;?>">
            <input type="hidden" name = "review_type" value ="<?php echo $rerview_type;?>">

            <td class="align-middle">
                <select class="custom-select" id="rating_<?php echo $transaction_id; ?>" name="rating" required>
                    <option value="" disabled selected> Rate (1-5)</option>
                    <option value="5">5 Stars - Excellent</option>
                    <option value="4">4 Stars - Good</option>
                    <option value="3">3 Stars - Average</option>
                    <option value="2">2 Stars - Poor</option>
                    <option value="1">1 Star - Very Poor </option> 
                </select>
            </td>

            <td class="align-middle">
                <textarea 
                class="formcontrol w=100 p-1" 
                name="comment" rows="2" 
                style="min-width: 100%; resize: none; border-radius: 5px;"
                placeholder="Share your experience (optional)..." 
                maxlength ="255"></textarea>
            </td>
            <td class="align-middle text-center">
                <button type="submit" class="btn  btn-primary mb-2">Submit</button>
            </td>
        </form>
    </tr>
<?php }; 
include_once("footer.php")?>


