<?php
// 1. Determine Context (My Profile vs Public Profile)
// If included in seller_profile.php, we use that URL. If in my_profile/seller.php, use that.
$base_url = htmlspecialchars($_SERVER['PHP_SELF']); 

// 2. Variable Initialization
$current_params = $_GET;
$filter_cat = $_GET['cat'] ?? 'all'; 
$sort_by = $_GET['sort'] ?? 'date_dsc'; 
$keyword = $_GET['keyword'] ?? '';
$curr_page = $_GET['page'] ?? 1;

// Ensure we have a seller ID (passed from parent page)
if (!isset($seller_id)) {
    echo "<div class='alert alert-danger'>Error: Seller ID missing.</div>";
    return;
}
?>

<div class="container">

<div id="searchSpecs">
<form method="get" action="<?php echo $base_url; ?>">
  <?php
    // Keep existing params (like 'section', 'tab', 'seller_id')
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
              'hot' => 'Hottest items',
              'date_asc' => 'Earliest',
              'date_dsc' => 'Latest',
              'pricelow' => 'Ending price (low-high)',
              'pricehigh' => 'Ending price (high-low)'
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

<div class="list-container mt-3">
<?php 
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
    WHERE a.seller_id = $seller_id
      AND a.end_date_time < NOW()
    ";
  
  $final_query = filter_by_keyword($connection, $keyword, $final_query);
  $final_query = filter_by_category($connection, $filter_cat,  $final_query);
  $final_query .= " GROUP BY a.auction_id ";
  
  switch ($sort_by) {
    case "hot": $final_query .= " ORDER BY num_bids DESC"; break;
    case "date_asc": $final_query .= " ORDER BY a.end_date_time ASC"; break;
    case "date_dsc": $final_query .= " ORDER BY a.end_date_time DESC"; break;
    case "pricelow": $final_query .= " ORDER BY highest_bid ASC"; break;
    case "pricehigh": $final_query .= " ORDER BY highest_bid DESC"; break;
    default: $final_query .= " ORDER BY a.end_date_time DESC";
  }

  // Get Count for Pagination
  $count_result = mysqli_query($connection, $final_query);
  $num_results = mysqli_num_rows($count_result);
  
  // Apply Limit
  $results_per_page = 10;
  $offset = ($curr_page - 1) * $results_per_page;
  $final_query .= " LIMIT $results_per_page OFFSET $offset";
  
  $auctions_to_list = mysqli_query($connection, $final_query);

  if ($num_results == 0) {
      echo '<div class="alert alert-info">No completed auctions found.</div>';
  } else {
      list_table_items($auctions_to_list);
  }

  $max_page = ceil($num_results / $results_per_page);
?>
</div>

<nav aria-label="Search results pages" class="mt-5">
  <ul class="pagination justify-content-center">
  <?php
    $querystring = "";
    foreach ($_GET as $key => $value) {
      if ($key != "page") {
        $querystring .= "$key=$value&amp;";
      }
    }
    
    // Previous Button
    if ($curr_page > 1) {
      echo '<li class="page-item"><a class="page-link" href="'.$base_url.'?'.$querystring.'page='.($curr_page-1).'">&laquo; Previous</a></li>';
    }

    // Page Numbers (Simple version)
    for ($i = 1; $i <= $max_page; $i++) {
      $active = ($i == $curr_page) ? 'active' : '';
      echo '<li class="page-item '.$active.'"><a class="page-link" href="'.$base_url.'?'.$querystring.'page='.$i.'">'.$i.'</a></li>';
    }

    // Next Button
    if ($curr_page < $max_page) {
      echo '<li class="page-item"><a class="page-link" href="'.$base_url.'?'.$querystring.'page='.($curr_page+1).'">Next &raquo;</a></li>';
    }
  ?>
  </ul>
</nav>

</div>
