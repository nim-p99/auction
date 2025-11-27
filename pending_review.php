<?php 
include_once("header.php");
require("utilities.php");
?>

<div cladd="container my-4">
    <?php
    if (isset($_SESSION['sucess_message'])){
        echo '<div class - "alert alertt-success">'.$_SESSION['succeess_message'].'</div>';
        unsett($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])){
        echo '<div class - "alert alertt-danger">'.$_SESSION['error_message'].'</div>';
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
                    ");

                $sql_buyer->bind_param("i", $buyer_id);
                $sql_buyer->execute();
                $result = $sql_buyer->get_result();

                if ($result->num_rows == 0){
                    echo "<p class='mt-3'>You have no pending reviews for items you purchased.</p>";
                }else{
                    while ($row=$result->fetch_assoc()){
                        render_review_card($row['transaction_id'], $row['title'], "Seller: ".$row['seller_name'], $row['item_id'], 'buyer_reviewing_seller');
                    }
                }
                $sql_buyer->close();
                ?>
            </div>

            <?php if (isset($_SESSION['seller_id'])): ?>
            <div class="tab-pane fade p-3" id="mysold" role="tabpanel">
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

                ");

                $sql_seller->bind_parm("i", $seller_id);
                $result = $sql_seller->get_result();

                if ($result->num_rows == 0) {
                    echo "<p class='mt-3'>You have no pending reviews for item you sold.</p>";
                }else{
                    while ($row = $result->fetch_assoc()) {
                        render_review_card($row['transaction_id'], $row['title'], "Buyer: ".$row['buyer_name'], $row['item_id'], 'seller_reviewing_buyer');
                    }
                }
                $sql_seller->close();
                ?>
            </div>
            <?php endif; ?>
    </div>
</div>

<?php
// review card
function render_review_card($transaction_id, $title, $item_id, $rerview_type){
?>
    <div class = "card mb-3">
        <div class="card header">
            <strong><a href="listing.php?item_id=<?php echo $item_id; ?>"><?php echo htmlspecialchars($title);?>
            <span class ="text-muted float-right"><?php echo htmlspecialchars($subtitle);?></span>
        </div>
        <div class = "card-body">
            <form action="process_review.php" method="POST">
                <input type="hidden" name="transaction_id" value="<?php echo $transaction_id;?>">
                <input type="hidden" name = "review_type" value ="<?php echo $rerview_type;?>">

                <div  class="form-row align-items-center">
                    <div class="col-auto">
                        <label class ="sr-only" for="rating_<?php echo $transaction_id;?>">Rating</label>
                        <select class="form-control mb-2" id="rating_<?php echo $transaction_id; ?>" name="rating" required>
                            <option value="" disabled selected> Rate (1-5)</option>
                            <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                            <option value="4">⭐⭐⭐⭐ (4)</option>
                            <option value="3">⭐⭐⭐ (3)</option>
                            <option value="2">⭐⭐ (2)</option>
                            <option value="1">⭐ (1)</option> 
                        </select>
                    </div>
                    <div class = "col-sm-7">
                        <label class="sr-only" for="commnt_<?php echo $transaction_id;?>" name="comment" placeholder="Leave a comment (optional)..." maxlength="255">
                        <input type="text" class="form-control mb-2">Submit Review</button>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn  btn-primary mb-2">Submit Review</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php }; 
include_once("footer.php")?>


