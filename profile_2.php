<?php 
include_once("header.php");
include "database.php";
include "utilities.php";


//require ("utilities.php") // Include if needed for func like print_listing_li
//require("database_connection.php") // Include if needed for DB access Assumed: you have a file that connects and sets a $connection variable


//--- Page Security & State ---
//enforces login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != true) {
   // Not logged in, redirect to browse page for now
   header("Location: browse.php");
   exit();
 }
 
//  // enforces account type
//  if ($_SESSION['account_type'] != 'buyer') {
//    // Not a buyer, redirect to home page
//    header("Location: index.php");
//    exit();
//  }
 
// 2. get user ID from session
// Assumes user_id is stored in $_Session upon login (in login_result.php)
$user_id = $_SESSION['user_id'] ?? null; // get user_id from session, default to null if not set
$username = $_SESSION['username'] ?? 'User'; //                               

if ($user_id === null) {
    // user_id not found in session, present error
    echo "<div class->'container'><div class='alert alert-danger'> Error: User not logged in properly. Please log in again.</div></div>";
    exit();
}

// --- Navigation State logic ---

// Define all allowed sections and their tabs
$sections = [
    'buyer' => ['mybids', 'orders', 'viewed', 'watchlist'],
    'seller' => ['listings', 'completed'],
    'account' => ['details', 'password', 'payment_info'],
    'messages' => ['inbox', 'sent'],
];

$allowed_section = array_keys($sections); // array keys is a function that gets the keys of an associative array

// Get section and tab from URL, or set defaults
$current_section = $_GET['section'] ?? 'buyer'; // default to 'buyer' section
$current_tab = $_GET['tab'] ?? $sections[$current_section][0]; // default to first tab of the section
if (!in_array($current_section, $allowed_section)) {
    $current_section = 'buyer'; // default to buyer if invalid
}

// get and validate filter/sort options all pages in this profile area
$filter_cat = $_GET['cat'] ?? 'fill'; // default to 'all' categories
$sort_by = $_GET['sort'] ?? 'date_asc'; // default to 'date_asc'

?>

<div class="container mt-4 mb-4"> <!-- mt and mb are margin top and bottom -->
    <h2 class= "my-3">My Profile </h2> 
    <p class="lead"> Welcome back, <?php echo htmlspecialchars($username); ?>! </p>

    <div class = "row">

        <!-- vertical sidebar navigation -->
        <div class="col-md-3">
            <div class= "nav flex-column nav-pills" id="profile-sections" role="tablist" aria-orientation="vertical">
                
                <a class= "nav-link <?php if ($current_section == 'buyer') echo 'active'; ?>" 
                   href="profile_2.php?section=buyer"><!-- Buyer Dashboard link -->
                    <i class="fa fa-shopping-basket fa-fw mr-2"></i> Buyer Dashboard
                </a>

                <a class= "nav-link <?php if ($current_section == 'seller') echo 'active'; ?>" 
                   href="profile_2.php?section=seller"><!-- Seller Dashboard link -->
                    <i class="fa fa-gavel fa-fw mr-2"></i> Seller Dashboard
                </a>

                <a class= "nav-link <?php if ($current_section == 'account') echo 'active'; ?>" 
                   href="profile_2.php?section=account"><!-- Account Settings link -->
                    <i class="fa fa-user-circle fa-fw mr-2"></i> Account Settings
                </a>
                <a class= "nav-link <?php if ($current_section == 'messages') echo 'active'; ?>" 
                   href="profile_2.php?section=messages"><!-- Messages link -->
                    <i class="fa fa-envelope fa-fw mr-2"></i> Messages
                </a>    
            </div>
        </div> <!-- end sidebar column -->

        <!-- Main content area -->
        <div class="col-md-9">

        <!-- ======================================================================== -->
         <!--                         BUYER DASHBOARD CONTENT                         -->
        <!-- ======================================================================== -->
        <?php if ($current_section == 'buyer'): ?>
            <div class = "card">
                <div class="card-header">
                <!--- Horizontal sub tabs for buyer dashboard --->
                <ul class= "nav nav-tabs card-header-tabs" id="buyer-dashboard-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'mybids') echo 'active'; ?>" 
                           href="profile_2.php?section=buyer&tab=mybids">My Bids</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'orders') echo 'active'; ?>" 
                           href="profile_2.php?section=buyer&tab=orders">My Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'viewed') echo 'active'; ?>" 
                           href="profile_2.php?section=buyer&tab=viewed">Recently Viewed</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'watchlist') echo 'active'; ?>" 
                           href="profile_2.php?section=buyer&tab=watchlist">Watchlist</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="buyer-tab-content">
                    
                    <!-- My Bids Tab Content -->
                     <div class="tab-pane fade <?php if ($current_tab == 'mybids') echo 'show active'; ?>" 
                          id="mybids" role="tabpanel">
                        <h5 class="card-title">My Bids</h5>
                        <p class="card-text"> Here you can view all the bids you made. </p>
                        
                        <!-- hiddden form to maintain filter/sort state -->
                            <input type="hidden" name="section" value="buyer">
                            <input type="hidden" name="tab" value="mybids">
                        
                        <!-- filter and sort options -->
                        <form method ="get" action="profile_2.php">
                        <div class="row">
                            <div class="col-md-3 pr-0">
                                <div class="form-group">
                                    <label for="cat" class="sr-only">Filter by category:</label>
                                    <select class="form-control" id="cat" name= "cat">
                                        <!-- Probably need some loop that populates with all the categories from the category entity -->
                                        <option value="all">All categories</option>
                                        <option value="1">Sport</option>
                                        <option value="2">Home</option>
                                        <option value="3">Fashion</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 pr-0">
                                <div class="form-inline">
                                    <label class="mx-2" for="sort_by">Sort by:</label>
                                    <select class="form-control" id="sort_by" name= "sort">
                                        <option selected value="pricelow">Price (low to high)</option>
                                        <option value="date_desc">Date (latest first)</option>
                                        <option value="pricehigh">Price (high to low)</option>
                                        <option value="active">Active listings</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1 px-0">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>

                        </div>
                        </form>
                            <!-- ======================= -->
                             <!--        TEST            -->
                            <!-- ======================= -->
                            <!-- hopoefully printing database items -->
                            <?php
                            //SQL Query to fetch user's bids using the test database items
                            //static query: $query = "SELECT * from items ORDER BY current_price WHERE ";

                            //Dynamic Query for category filter
                            global $connection;
                            $allowed_categories = [1, 2, 3]; //current category primary keys from database example
                            $where_clause = " WHERE 1=1";
                            if ($filter_cat != 'all' && in_array((int)$filter_cat, $allowed_categories)) {
                                $int_filter_cat = (int)$filter_cat; 
                                $where_clause .= " AND i.CategoryID = $int_filter_cat "; 
                            }
                            $final_query = "SELECT * from items AS  i" . $where_clause;
                            $items_to_list = mysqli_query($connection, $final_query);

                            ?>
                            <div class="list-container">
                            <?php 
                            //loop through each item and display it
                              while ($row = mysqli_fetch_assoc($items_to_list)) : ?>
                                  <div class="list-group">
                                    <?php 
                                    $item_id = $row['item_id'];
                                    $title = $row['title'];
                                    $description = $row['description'];
                                    $end_date = new DateTime('2020-11-02T00:00:00'); // Placeholder end date
                                    $current_price = $row['current_price']; 
                                    $num_bids =$row['num_bids'];

                                    //using the print listing function from utilities.php
                                    print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date) ?>
                                  </div>
                                  
                              <?php endwhile; ?>
                              </div>

                        <!-- End filter and sort options -->
                    </div> <!-- end my bids tab pane -->

                    <!-- Orders Tab Content -->
                     <div class="tab-pane fade <?php if ($current_tab == 'orders') echo 'show active'; ?>" 
                          id="orders" role="tabpanel">
                        <h5 class="card-title">Orders</h5>
                        <p class="card-text"> Here you can view all the items you've purchased. </p>
                
                        <!-- filter and sort options -->
                        <form method ="get" action="profile_2.php">

                        <!-- hiddden form to maintain filter/sort state **NEEDS TO BE IN FORM OTHERWISE WEBPAGE WILL GO BACK TO DEFAULT**   -->
                        <input type="hidden" name="section" value="buyer">
                        <input type="hidden" name="tab" value="orders">
                        
                        <div class="row">
                            <div class="col-md-3 pr-0">
                                <div class="form-group">
                                    <label for="cat" class="sr-only">Filter by category:</label>
                                    <select class="form-control" id="cat" name= "cat">
                                        <!-- Probably need some loop that populates with all the categories from the category entity -->
                                        <option value="all">All categories</option>
                                        <option value="1">Sport</option>
                                        <option value="2">Home</option>
                                        <option value="3">Fashion</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 pr-0">
                                <div class="form-inline">
                                    <label class="mx-2" for="sort_by">Sort by:</label>
                                    <select class="form-control" id="sort_by"name= "sort">
                                        <option selected value="pricelow">Price (low to high)</option>
                                        <option value="date_desc">Date (latest first)</option>
                                        <option value="pricehigh">Price (high to low)</option>
                                        <option value="active">Active listings</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1 px-0">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                        </form>
                            <?php
                            //SQL Query to fetch user's bids using the test database items
                            //static query: $query = "SELECT * from items ORDER BY current_price WHERE ";

                            //Dynamic Query for category filter
                            global $connection;
                            $allowed_categories = [1, 2, 3]; //current category primary keys from database example
                            $where_clause = " WHERE 1=1";
                            if ($filter_cat != 'all' && in_array((int)$filter_cat, $allowed_categories)) {
                                $int_filter_cat = (int)$filter_cat; 
                                $where_clause .= " AND i.CategoryID = $int_filter_cat "; 
                            }
                            $final_query = "SELECT * from items AS  i" . $where_clause;
                            $items_to_list = mysqli_query($connection, $final_query);

                            ?>
                            <div class="list-container">
                            <?php 
                            //loop through each item and display it
                              while ($row = mysqli_fetch_assoc($items_to_list)) : ?>
                                  <div class="list-group">
                                    <?php 
                                    $item_id = $row['item_id'];
                                    $title = $row['title'];
                                    $description = $row['description'];
                                    $end_date = new DateTime('2020-11-02T00:00:00'); // Placeholder end date
                                    $current_price = $row['current_price']; 
                                    $num_bids =$row['num_bids'];

                                    //using the print listing function from utilities.php
                                    print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date) ?>
                                  </div>
                                  
                              <?php endwhile; ?>
                        </div><!-- End filter and sort options -->
                    </div> <!-- end orders tab pane -->    

                    <!-- Watchlist Tab Content -->
                     <div class="tab-pane fade <?php if ($current_tab == 'watchlist') echo 'show active'; ?>" 
                          id="watchlist" role="tabpanel">
                        <h5 class="card-title">Watchlist</h5>
                        <p class="card-text"> Here you can view all the items you saved. </p>
                        
                        <!-- filter and sort options -->
                        <form method ="get" action="profile_2.php">

                        <!-- hiddden form to maintain filter/sort state **NEEDS TO BE IN FORM OTHERWISE WEBPAGE WILL GO BACK TO DEFAULT**   -->
                        <input type="hidden" name="section" value="buyer">
                        <input type="hidden" name="tab" value="watchlist">
                        
                        <div class="row">
                            <div class="col-md-3 pr-0">
                                <div class="form-group">
                                    <label for="cat" class="sr-only">Filter by category:</label>
                                    <select class="form-control" id="cat" name= "cat">
                                        <!-- Probably need some loop that populates with all the categories from the category entity -->
                                        <option value="all">All categories</option>
                                        <option value="1">Sport</option>
                                        <option value="2">Home</option>
                                        <option value="3">Fashion</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 pr-0">
                                <div class="form-inline">
                                    <label class="mx-2" for="sort_by">Sort by:</label>
                                    <select class="form-control" id="sort_by"name= "sort">
                                        <option selected value="pricelow">Price (low to high)</option>
                                        <option value="date_desc">Date (latest first)</option>
                                        <option value="pricehigh">Price (high to low)</option>
                                        <option value="active">Active listings</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1 px-0">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                        </form>
                            <?php
                            //SQL Query to fetch user's bids using the test database items
                            //static query: $query = "SELECT * from items ORDER BY current_price WHERE ";

                            //Dynamic Query for category filter
                            global $connection;
                            $allowed_categories = [1, 2, 3]; //current category primary keys from database example
                            $where_clause = " WHERE 1=1";
                            if ($filter_cat != 'all' && in_array((int)$filter_cat, $allowed_categories)) {
                                $int_filter_cat = (int)$filter_cat; 
                                $where_clause .= " AND i.CategoryID = $int_filter_cat "; 
                            }
                            $final_query = "SELECT * from items AS  i" . $where_clause;
                            $items_to_list = mysqli_query($connection, $final_query);

                            ?>
                            <div class="list-container">
                            <?php 
                            //loop through each item and display it
                              while ($row = mysqli_fetch_assoc($items_to_list)) : ?>
                                  <div class="list-group">
                                    <?php 
                                    $item_id = $row['item_id'];
                                    $title = $row['title'];
                                    $description = $row['description'];
                                    $end_date = new DateTime('2020-11-02T00:00:00'); // Placeholder end date
                                    $current_price = $row['current_price']; 
                                    $num_bids =$row['num_bids'];

                                    //using the print listing function from utilities.php
                                    print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date) ?>
                                  </div>
                                  
                              <?php endwhile; ?>
                        </div><!-- End filter and sort options --> 
                    </div> <!-- end watchlist tab pane -->    
                    
                    
                    <!-- recently viewed Tab Content -->
                     <div class="tab-pane fade <?php if ($current_tab == 'viewed') echo 'show active'; ?>" 
                          id="viewed" role="tabpanel">
                        <h5 class="card-title">Recently Viewed</h5>
                        <p class="card-text"> Here you can view all items you recently viewed. </p>
                        
                        <!-- filter and sort options -->
                        <form method ="get" action="profile_2.php">

                        <!-- hiddden form to maintain filter/sort state **NEEDS TO BE IN FORM OTHERWISE WEBPAGE WILL GO BACK TO DEFAULT**   -->
                        <input type="hidden" name="section" value="buyer">
                        <input type="hidden" name="tab" value="orders">
                        
                        <div class="row">
                            <div class="col-md-3 pr-0">
                                <div class="form-group">
                                    <label for="cat" class="sr-only">Filter by category:</label>
                                    <select class="form-control" id="cat" name= "cat">
                                        <!-- Probably need some loop that populates with all the categories from the category entity -->
                                        <option value="all">All categories</option>
                                        <option value="1">Sport</option>
                                        <option value="2">Home</option>
                                        <option value="3">Fashion</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 pr-0">
                                <div class="form-inline">
                                    <label class="mx-2" for="sort_by">Sort by:</label>
                                    <select class="form-control" id="sort_by"name= "sort">
                                        <option selected value="pricelow">Price (low to high)</option>
                                        <option value="date_desc">Date (latest first)</option>
                                        <option value="pricehigh">Price (high to low)</option>
                                        <option value="active">Active listings</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1 px-0">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                        </form>
                            <?php
                            //SQL Query to fetch user's bids using the test database items
                            //static query: $query = "SELECT * from items ORDER BY current_price WHERE ";

                            //Dynamic Query for category filter
                            global $connection;
                            $allowed_categories = [1, 2, 3]; //current category primary keys from database example
                            $where_clause = " WHERE 1=1";
                            if ($filter_cat != 'all' && in_array((int)$filter_cat, $allowed_categories)) {
                                $int_filter_cat = (int)$filter_cat; 
                                $where_clause .= " AND i.CategoryID = $int_filter_cat "; 
                            }
                            $final_query = "SELECT * from items AS  i" . $where_clause;
                            $items_to_list = mysqli_query($connection, $final_query);

                            ?>
                            <div class="list-container">
                            <?php 
                            //loop through each item and display it
                              while ($row = mysqli_fetch_assoc($items_to_list)) : ?>
                                  <div class="list-group">
                                    <?php 
                                    $item_id = $row['item_id'];
                                    $title = $row['title'];
                                    $description = $row['description'];
                                    $end_date = new DateTime('2020-11-02T00:00:00'); // Placeholder end date
                                    $current_price = $row['current_price']; 
                                    $num_bids =$row['num_bids'];

                                    //using the print listing function from utilities.php
                                    print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date) ?>
                                  </div>
                                  
                              <?php endwhile; ?>
                        </div><!-- End filter and sort options -->
                    </div> <!-- end recentlyviewed tab pane -->        
                </div> <!-- end buyer tab content -->
            </div> <!-- end card body -->
        <?php endif; ?> <!-- end buyer dashboard section -->

<!-- ================================================================================= -->
<!-- TEMPLATE FOR OTHER SECTIONS COME BACK TO IT LATER AFTER DISCUSSION WITH THE GROUP -->
 <!-- ================================================================================ -->
 
      <!-- =================================================================== -->
      <!-- =                  SELLER DASHBOARD CONTENT                       = -->
      <!-- =================================================================== -->
      <?php if ($current_section == 'seller'): ?>
        <div class="card">
          <div class="card-header">
            <!-- Horizontal Sub-tabs for Seller -->
            <ul class="nav nav-tabs card-header-tabs" id="seller-tabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link <?php echo ($current_tab == 'listings') ? 'active' : ''; ?>"
                   href="profile_2.php?section=seller&tab=listings">My Listings</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?php echo ($current_tab == 'completed') ? 'active' : ''; ?>"
                   href="profile_2.php?section=seller&tab=completed">Completed Auctions</a>
              </li>
            </ul>
          </div>
          <div class="card-body">
            <div class="tab-content" id="seller-tab-content">

              <!-- My Listings Pane -->
              <div class="tab-pane fade <?php echo ($current_tab == 'listings') ? 'show active' : ''; ?>" id="listings" role="tabpanel">
                <h5 class="card-title">My Active Listings</h5>
              </div> <!-- /My Listings Pane -->

              <!-- Completed Auctions Pane -->
              <div class="tab-pane fade <?php echo ($current_tab == 'completed') ? 'show active' : ''; ?>" id="completed" role="tabpanel">
                <h5 class="card-title">Completed Auctions</h5>
                <p class="card-text">Auctions that have finished.</p>
                <div class="alert alert-secondary">Database logic to show completed auctions goes here.</div>
              </div> <!-- /Completed Auctions Pane -->

            </div>
          </div>
        </div>
      <?php endif; ?>

      
      <!-- =================================================================== -->
      <!-- =                  ACCOUNT SETTINGS CONTENT                       = -->
      <!-- =================================================================== -->
      <?php if ($current_section == 'account'): ?>
        <div class="card">
          <div class="card-header">
            <!-- Horizontal Sub-tabs for Account -->
            <ul class="nav nav-tabs card-header-tabs" id="account-tabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link <?php echo ($current_tab == 'details') ? 'active' : ''; ?>"
                   href="profile_2.php?section=account&tab=details">Personal Details</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?php echo ($current_tab == 'password') ? 'active' : ''; ?>"
                   href="profile_2.php?section=account&tab=password">Change Password</a>
              </li>
            </ul>
          </div>
          <div class="card-body">
            <div class="tab-content" id="account-tab-content">

              <!-- Personal Details Pane -->
              <div class="tab-pane fade <?php echo ($current_tab == 'details') ? 'show active' : ''; ?>" id="details" role="tabpanel">
                <h5 class="card-title">Personal Details</h5>
              </div> <!-- /Personal Details Pane -->

              <!-- Change Password Pane -->
              <div class="tab-pane fade <?php echo ($current_tab == 'password') ? 'show active' : ''; ?>" id="password" role="tabpanel">
                <h5 class="card-title">Change Password</h5>
              </div> 

            </div>
          </div>
        </div>
      <?php endif; ?>


      <!-- =================================================================== -->
      <!-- =                     MESSAGES CONTENT                            = -->
      <!-- =================================================================== -->
      <?php if ($current_section == 'messages'): ?>
        <div class="card">
          <div class="card-header">
            <!-- Horizontal Sub-tabs for Messages -->
            <ul class="nav nav-tabs card-header-tabs" id="message-tabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link <?php echo ($current_tab == 'inbox') ? 'active' : ''; ?>"
                   href="profile_2.php?section=messages&tab=inbox">Inbox</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?php echo ($current_tab == 'sent') ? 'active' : ''; ?>"
                   href="profile_2.php?section=messages&tab=sent">Sent</a>
              </li>
            </ul>
          </div>
          <div class="card-body">
            <div class="tab-content" id="message-tab-content">

              <!-- Inbox Pane -->
              <div class="tab-pane fade <?php echo ($current_tab == 'inbody') ? 'show active' : ''; ?>" id="inbox" role="tabpanel">
                <h5 class="card-title">Inbox</h5>
                <p class="card-text">Your received messages.</p>
              </div> <!-- /Inbox Pane -->

              <!-- Sent Pane -->
              <div class="tab-pane fade <?php echo ($current_tab == 'sent') ? 'show active' : ''; ?>" id="sent" role="tabpanel">
                <h5 class="card-title">Sent Messages</h5>
              </div> 

            </div>
          </div>
        </div>
      <?php endif; ?>


    </div> <!-- /col-md-9 -->
  </div> <!-- /row -->
</div> <!-- /container -->

<?php include_once("footer.php")?>
                
                
