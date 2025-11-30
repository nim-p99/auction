
<?php
// INCLUDE GLOBAL FILES 
include_once("header.php");
require("utilities.php");
?>

<div class="container">


<h2 class="my-3">Manage Users</h2>


<?php
//VARIABLE INITIALISATION
$filter_cat = $_GET['cat'] ?? 'all'; // default to 'all' categories
$sort_by = $_GET['sort'] ?? 'hot'; //default to items that have lots of bids'
$keyword = $_GET['keyword'] ?? '';
if (!isset($_GET['page'])) {
    $curr_page = 1;
  }
else {
    $curr_page = $_GET['page'];
  }
?>


<div id="searchSpecs">
<!-- Search specifications bar -->
<form method="get" action="admin_accs.php">
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
          <input type="text" class="form-control border-left-0" id="keyword" name= "keyword" placeholder="Search user details" value ="<?php echo htmlspecialchars($keyword); ?>">
        </div>
      </div>
    </div>
    
    <!-- Acc Type filtering -->
    <div class="col-md-3 pr-0">
      <div class="form-group">
        <label for="cat" class="sr-only">Search within:</label>
        <select class="form-control" id="cat" name="cat">
          <option value="all" <?php if ($filter_cat=='all') echo 'selected'; ?>>All Accounts</option>
          <?php
            $sort_options = [ 
              'buyer' => 'Buyer',
              'seller' => 'Seller'
            ];
            foreach ($sort_options as $key => $label) {
              $selected = ($filter_cat == $key) ? 'selected' : '';
              echo "<option value='$key' $selected>$label</option>";
            }
            ?>
        </select>
      </div>
    </div>

     <!-- Sort by -->
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
  <!-- end sort by -->
</form>
</div> 
<!-- end search specifications bar-->


</div>

<div class="container mt-5">

<!--------------------------------------------------------------

!!!!!! TODO: If result set is empty, print an informative message. Otherwise...!!!! 

 ---------------------------------------------------------------------------->


<!----------------------------------------------------------------------------
                          Listing auctions
----------------------------------------------------------------------------->


<div class="list-container">
<?php 

  // Construct the final query using the filter category and sort by
  // need to change so only active auctions are shown
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
  $accs_to_list = mysqli_query($connection, $final_query);

  $num_results = mysqli_num_rows($accs_to_list); //96;
  
  // showing message if no results
  if ($num_results == 0 || $num_results == null)  {
    echo'<h4> No Auctions Found! </h4>';
    if (!empty($keyword)){
        echo "<p> We couldn't find any active listings matching '". htmlspecialchars($keyword)."'. </p>";
    }
    else{
        echo "<p>No auctions matched your filtering criteria. </p>";
    }    
  }
// Use the function from utilities.php to print the listings
  else{
    list_account_details($accs_to_list,$is_admin = true);
  }

  $results_per_page = 10;
  $max_page = ceil($num_results / $results_per_page);
?>
  </div>

<!-- Pagination for results listings -->
<nav aria-label="Search results pages" class="mt-5">
  <ul class="pagination justify-content-center">
  
<?php

  // Copy any currently-set GET variables to the URL.
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
  
  if ($curr_page != 1 && $num_results !=0) {
    echo('
    <li class="page-item">
      <a class="page-link" href="admin_accs.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
        <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
        <span class="sr-only">Previous</span>
      </a>
    </li>');
  }
    
  for ($i = $low_page; $i <= $high_page; $i++) {
    if ($i == $curr_page) {
      // Highlight the link
      echo('
    <li class="page-item active">');
    }
    else {
      // Non-highlighted link
      echo('
    <li class="page-item">');
    }
    
    // Do this in any case
    echo('
      <a class="page-link" href="admin_accs.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
  }
  
  if ($curr_page != $max_page && $num_results !=0) {
    echo('
    <li class="page-item">
      <a class="page-link" href="admin_accs.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
        <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
        <span class="sr-only">Next</span>
      </a>
    </li>');
  }
?>

  </ul>
</nav>


</div>



<?php include_once("footer.php")?>
