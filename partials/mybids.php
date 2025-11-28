<?php
$buyerID = $_SESSION['buyer_id'] ?? null;

if (!$buyerID) {
    echo "<div class='alert alert-info'>You must be logged in as a buyer to view bids.</div>";
    return;
}

// 1. Determine current page context for links
// This checks if we are on 'buyer.php' or 'my_profile.php' to ensure links stay on the same page
$current_page_url = basename($_SERVER['PHP_SELF']); 
$qs_prefix = ($current_page_url == 'buyer.php') ? 'tab=mybids&' : 'section=buyer&tab=mybids&';

// 2. Variable Initialization
$current_params = $_GET;
$filter_cat = $_GET['cat'] ?? 'all'; 
$sort_by = $_GET['sort'] ?? 'hot';
$keyword = $_GET['keyword'] ?? '';
$curr_page = $_GET['page'] ?? 1;
?>

<div class="container">

<div id="searchSpecs">
  <form method="get" action="<?php echo htmlspecialchars($current_page_url); ?>">
    
    <?php if($current_page_url == 'my_profile.php'): ?>
        <input type="hidden" name="section" value="buyer">
    <?php endif; ?>
    <input type="hidden" name="tab" value="mybids">

    <?php
      // Keep other params except the ones we change in this form
      foreach ($current_params as $key => $value) {
        if (!in_array($key, ['keyword', 'cat', 'sort', 'page', 'section', 'tab'])) {
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
                'hot' => 'Default',
                'date_asc' => 'Earliest Bids',
                'date_dsc' => 'Latest Bids',
                'pricelow' => 'Amount (low-high)',
                'pricehigh' => 'Amount (high-low)'
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
      b.bid_id, b.amount, b.date,
      i.title, i.item_id, a.auction_id, a.end_date_time,
      c.category_id, i.description
    FROM bids AS b 
    JOIN auction AS a ON b.auction_id = a.auction_id 
    JOIN item AS i ON a.item_id = i.item_id 
    JOIN category AS c ON c.category_id = i.category_id
    WHERE b.buyer_id = $buyerID 
  ";
  
  $final_query = filter_by_keyword($connection, $keyword, $final_query);
  $final_query = filter_by_category($connection, $filter_cat,  $final_query);
  
  switch ($sort_by) {
    case "pricehigh": $final_query .= " ORDER BY b.amount DESC"; break;
    case "pricelow":  $final_query .= " ORDER BY b.amount ASC"; break;
    case "date_asc":  $final_query .= " ORDER BY b.date ASC"; break;
    default:          $final_query .= " ORDER BY b.date DESC";
  }

  // Count
  $count_result = mysqli_query($connection, $final_query);
  $num_results = mysqli_num_rows($count_result);

  // Pagination
  $results_per_page = 8;
  $offset = ($curr_page - 1) * $results_per_page;
  $final_query .= " LIMIT $results_per_page OFFSET $offset";

  $auctions_to_list = mysqli_query($connection, $final_query);

  if (mysqli_num_rows($auctions_to_list) > 0) {
    list_user_bids($auctions_to_list);
  } else {
    echo "<div class='alert alert-info'>You haven't placed any bids yet.</div>";
  }
  
  $max_page = ceil($num_results / $results_per_page);
?>
</div>

<nav aria-label="Search results pages" class="mt-5">
  <ul class="pagination justify-content-center">
  <?php
    // Construct Query String for Pagination
    $querystring = "";
    foreach ($_GET as $key => $value) {
      if ($key != "page" && $key != "tab" && $key != "section") {
        $querystring .= "$key=$value&amp;";
      }
    }
    
    // Combine with our prefix (e.g. section=buyer&tab=mybids&)
    $final_qs = $qs_prefix . $querystring;

    // Previous Link
    if ($curr_page > 1) {
      echo '<li class="page-item"><a class="page-link" href="'.$current_page_url.'?'.$final_qs.'page='.($curr_page-1).'">&laquo; Previous</a></li>';
    }
    
    // Page Numbers
    for ($i = 1; $i <= $max_page; $i++) {
      $active = ($i == $curr_page) ? 'active' : '';
      echo '<li class="page-item '.$active.'"><a class="page-link" href="'.$current_page_url.'?'.$final_qs.'page='.$i.'">'.$i.'</a></li>';
    }
    
    // Next Link
    if ($curr_page < $max_page) {
      echo '<li class="page-item"><a class="page-link" href="'.$current_page_url.'?'.$final_qs.'page='.($curr_page+1).'">Next &raquo;</a></li>';
    }
  ?>
  </ul>
</nav>

</div>
