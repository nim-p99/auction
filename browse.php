<?php

include_once "includes/header.php";
require_once "includes/utilities.php";
?>

<div class="container">

<h2 class="my-3">Browse Listings</h2>

<?php
  
  $base_url = BASE_URL . '/browse.php'; 
  $current_params = $_GET;

  // VARIABLE INITIALISATION
  $filter_cat = $_GET['cat'] ?? 'all'; 
  $sort_by = $_GET['sort'] ?? 'hot'; 
  $keyword = $_GET['keyword'] ?? '';
  
  if (!isset($_GET['page'])) {
    $curr_page = 1;
  } else {
    $curr_page = $_GET['page'];
  }
?>

<div id="searchSpecs">
  <form method="get" action="<?php echo $base_url; ?>">
    <?php
      // Keep existing parameters (like sort) when searching
      foreach ($current_params as $key => $value) {
        if (!in_array($key, ['keyword', 'cat', 'sort', 'page'])) {
          echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
        }
      }
    ?>

    <div class="row">
      <div class="col-md-5 pr-0">
        <div class="form-group">
          <label for="keyword" class="sr-only">Search keyword:</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text bg-transparent pr-0 text-muted">
                <i class="fa fa-search"></i>
              </span>
            </div>
            <input type="text" class="form-control border-left-0" id="keyword" name="keyword" placeholder="Search for anything" value="<?php echo htmlspecialchars($keyword); ?>">
          </div>
        </div>
      </div>

      <div class="col-md-3 pr-0">
        <div class="form-group">
          <label for="cat" class="sr-only">Search within:</label>
          <select class="form-control" id="cat" name="cat">
            <option value="all" <?php if ($filter_cat=='all') echo 'selected'; ?>>All Categories</option>
            <?php
              // Load categories
              $sql = "SELECT c.category_id, c.category_name, c.parent_category
                      FROM category c
                      LEFT JOIN category p ON c.parent_category = p.category_id
                      ORDER BY
                        CASE WHEN c.parent_category IS NULL THEN c.category_name ELSE p.category_name END,
                        CASE WHEN c.parent_category IS NULL THEN '' ELSE c.category_name END";
              
              $result = mysqli_query($connection, $sql);

              if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                  $label = $row['category_name'];
                  // Indent child categories
                  if (!is_null($row['parent_category'])) {
                    $label = '— ' . $label;
                  }
                  $selected = ($filter_cat == $row['category_id']) ? 'selected' : '';
                  echo '<option value="' . htmlspecialchars($row['category_id']) . '" ' . $selected . '>'
                     . htmlspecialchars($label) . '</option>';
                }
              }
            ?>
          </select>
        </div>
      </div>

      <div class="col-md-3 pr-0">
        <div class="form-inline">
          <label class="mx-2" for="order_by">Sort by:</label>
          <select class="form-control" id="order_by" name="sort">
            <?php
              $sort_options = [
                'hot' => 'Hot items',
                'date_asc' => 'Soonest expiry',
                'date_dsc' => 'Latest expiry',
                'pricelow' => 'Price (low-high)',
                'pricehigh' => 'Price (high-low)',
                'buy_now_asc' => 'Buy Now (low-high)',
                'buy_now_dsc' => 'Buy Now (high-low)'
              ];
              foreach ($sort_options as $key => $label) {
                $selected = ($sort_by == $key) ? 'selected' : '';
                echo "<option value='$key' $selected>$label</option>";
              }
            ?>
          </select>
        </div>
      </div>

      <div class="col-md-1 px-0">
        <button type="submit" class="btn btn-primary">Search</button>
      </div>
    </div>
  </form>
</div> 
</div>

<div class="container mt-5">

  <div class="list-container">
  <?php 
    // Build Query
    $final_query = "
      SELECT 
        a.auction_id, a.start_bid, a.reserve_price,
        a.buy_now_price, a.start_date_time, a.end_date_time,
        i.item_id, i.title, i.description, i.photo_url, i.item_condition,
        c.category_id, c.category_name,
        COALESCE(MAX(b.amount), 0) AS highest_bid,
        COUNT(b.bid_id) AS num_bids,
        GREATEST(a.start_bid, COALESCE(MAX(b.amount), 0)) AS current_price
      FROM auction AS a 
      JOIN item AS i ON a.item_id = i.item_id
      JOIN category AS c ON c.category_id = i.category_id
      LEFT JOIN bids AS b ON b.auction_id = a.auction_id
      WHERE 1=1 AND a.is_active = 1
    ";
    
    
    $final_query = filter_by_keyword($connection, $keyword, $final_query);
    $final_query = filter_by_category($connection, $filter_cat,  $final_query);
    $final_query .= " GROUP BY a.auction_id ";
    $final_query = sort_by($sort_by, $final_query);

    // Get Total Count
    $count_result = mysqli_query($connection, $final_query);
    $num_results = mysqli_num_rows($count_result);

    // Pagination
    $results_per_page = 8;
    $offset = ($curr_page - 1) * $results_per_page;
    $final_query .= " LIMIT $results_per_page OFFSET $offset";
    $auctions_to_list = mysqli_query($connection, $final_query);

    // List Items
    list_table_items($auctions_to_list);

    // No Results Message
    if ($num_results == 0 || $num_results == null)  {
        echo '<h4> No Auctions Found! </h4>';
        if (!empty($keyword)){
            echo "<p> We couldn't find any active listings matching '". htmlspecialchars($keyword)."'. </p>";
        } else{
            echo "<p>No auctions matched your filtering criteria. </p>";
        }     
    }

    $max_page = ceil($num_results / $results_per_page);
  ?>
  </div>

  <nav aria-label="Search results pages" class="mt-5">
    <ul class="pagination justify-content-center">
    
    <?php
      // Copy GET variables to the URL, excluding 'page'
      $querystring = "";
      foreach ($_GET as $key => $value) {
        if ($key != "page") {
          $querystring .= "$key=$value&amp;";
        }
      }
      
      $high_page_boost = max(3 - $curr_page, 0);
      $low_page_boost = max(2 - ($max_page - $curr_page), 0);
      $low_page = max(1, $curr_page - 2 - $low_page_boost);
      $high_page = min($max_page, $curr_page + 2 + $high_page_boost);
      
      
      if ($curr_page != 1 && $num_results > 0) {
        echo('
        <li class="page-item">
          <a class="page-link" href="' . BASE_URL . '/browse.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
            <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
            <span class="sr-only">Previous</span>
          </a>
        </li>');
      }
        
      for ($i = $low_page; $i <= $high_page; $i++) {
        if ($i == $curr_page) {
          echo '<li class="page-item active">';
        } else {
          echo '<li class="page-item">';
        }
        
        echo '
          <a class="page-link" href="' . BASE_URL . '/browse.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
        </li>';
      }
      
      if ($curr_page != $max_page && $num_results > 0) {
        echo('
        <li class="page-item">
          <a class="page-link" href="' . BASE_URL . '/browse.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
            <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
            <span class="sr-only">Next</span>
          </a>
        </li>');
      }
    ?>

    </ul>
  </nav>

</div>

<?php include_once("includes/footer.php"); ?>
