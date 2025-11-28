<?php
include_once "../includes/header.php";
require_once "../includes/utilities.php";

// Access Control
if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: " . BASE_URL . "/browse.php");
    exit();
}

$base_url = BASE_URL . '/admin/admin_accs.php';
$current_params = $_GET;

// Variable Initialization
$filter_cat = $_GET['cat'] ?? 'all'; 
$sort_by = $_GET['sort'] ?? 'active'; 
$keyword = $_GET['keyword'] ?? '';
$curr_page = $_GET['page'] ?? 1;
?>

<div class="container">
<h2 class="my-3">Manage Accounts</h2>

<div id="searchSpecs">
<form method="get" action="<?php echo $base_url; ?>">
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
          <input type="text" class="form-control border-left-0" id="keyword" name="keyword" placeholder="Search user details" value="<?php echo htmlspecialchars($keyword); ?>">
        </div>
      </div>
    </div>
    
    <div class="col-md-3 pr-0">
      <div class="form-group">
        <label for="cat" class="sr-only">Search within:</label>
        <select class="form-control" id="cat" name="cat">
          <option value="all" <?php if ($filter_cat=='all') echo 'selected'; ?>>All Accounts</option>
          <?php
            $cat_options = [ 
              'buyer' => 'Buyer',
              'seller' => 'Seller'
            ];
            foreach ($cat_options as $key => $label) {
              $selected = ($filter_cat == $key) ? 'selected' : '';
              echo "<option value='$key' $selected>$label</option>";
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
              'active' => 'Acc. Active',
              'inactive' => 'Acc. inactive',
              'alpha' => 'A-Z',
              'r-alpha' => 'Z-A'
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
      u.user_id, u.email, u.username, u.acc_active, u.first_name, u.family_name, s.seller_id,
      s.avg_seller_rating, b.buyer_id, b.avg_buyer_rating  
    FROM users AS u
    LEFT JOIN seller AS s ON s.user_id = u.user_id 
    LEFT JOIN buyer AS b ON b.user_id = u.user_id
    WHERE 1=1 AND u.user_id != 1
    ";
  
  $final_query = filter_by_keyword_admin($connection, $keyword, $final_query);
  $final_query = filter_by_account($connection, $filter_cat,  $final_query);
  $final_query = sort_by_admin($sort_by, $final_query);
  
  // Get Total Count
  $count_result = mysqli_query($connection, $final_query);
  $num_results = mysqli_num_rows($count_result);

  // Pagination Logic
  $results_per_page = 10;
  $offset = ($curr_page - 1) * $results_per_page;
  $final_query .= " LIMIT $results_per_page OFFSET $offset";
  
  $accs_to_list = mysqli_query($connection, $final_query);

  if ($num_results == 0 || $num_results == null)  {
    echo '<h4> No Accounts Found! </h4>';
    if (!empty($keyword)){
        echo "<p> We couldn't find any accounts matching '". htmlspecialchars($keyword)."'. </p>";
    }
    else{
        echo "<p>No accounts matched your filtering criteria. </p>";
    }    
  }
  else{
    list_account_details($accs_to_list);
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
  
  $high_page_boost = max(3 - $curr_page, 0);
  $low_page_boost = max(2 - ($max_page - $curr_page), 0);
  $low_page = max(1, $curr_page - 2 - $low_page_boost);
  $high_page = min($max_page, $curr_page + 2 + $high_page_boost);
  
  if ($curr_page != 1 && $num_results != 0) {
    echo('
    <li class="page-item">
      <a class="page-link" href="'.$base_url.'?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
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
    
    echo '<a class="page-link" href="'.$base_url.'?' . $querystring . 'page=' . $i . '">' . $i . '</a></li>';
  }
  
  if ($curr_page != $max_page && $num_results != 0) {
    echo('
    <li class="page-item">
      <a class="page-link" href="'.$base_url.'?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
        <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
        <span class="sr-only">Next</span>
      </a>
    </li>');
  }
?>

  </ul>
</nav>

</div>

<?php include_once "../includes/footer.php"; ?>
