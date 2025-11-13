<?php 
include_once "header.php";
include_once "utilities.php";

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header("Location: login.php");
  exit();
}

// Define all allowed sections and their tabs
$sections = [
    'buyer' => ['mybids', 'orders', 'viewed', 'watchlist'],
    'seller' => ['listings', 'completed'],
    'account' => ['details', 'password', 'payment_info'],
    'messages' => ['inbox', 'sent'],
];

// get keys of associative array  
$allowed_section = array_keys($sections);

// Get section and tab from URL, or set defaults
$current_section = $_GET['section'] ?? 'buyer'; // default to 'buyer' section
if (!in_array($current_section, $allowed_section)) {
    $current_section = 'buyer'; // default to buyer if invalid
}
$current_tab = $_GET['tab'] ?? $sections[$current_section][0]; 

// get and validate filter/sort options all pages in this profile area
$filter_cat = $_GET['cat'] ?? 'fill'; // default to 'all' categories
$sort_by = $_GET['sort'] ?? 'date_asc'; // default to 'date_asc'



$seller_id = 'Tony';
?>

<div class="container mt-4 mb-4"> <!-- mt and mb are margin top and bottom -->
    <h2 class= "my-3">My Profile </h2> 
    <p class="lead"> Welcome back, <?php echo htmlspecialchars($username); ?>! </p>

    <div class = "row">

        <!-- vertical sidebar navigation -->
        <div class="col-md-3">
            <div class= "nav flex-column nav-pills" id="profile-sections" role="tablist" aria-orientation="vertical">
                
                <a class= "nav-link <?php if ($current_section == 'buyer') echo 'active'; ?>" 
                   href="my_profile.php?section=buyer"><!-- Buyer Dashboard link -->
                    <i class="fa fa-shopping-basket fa-fw mr-2"></i> Buyer Dashboard
                </a>

                <a class= "nav-link <?php if ($current_section == 'seller') echo 'active'; ?>" 
                <?php echo('href="my_profile.php?section=seller&seller_id=' . $seller_id . '">') ?><!-- Seller Dashboard link -->
                    <i class="fa fa-gavel fa-fw mr-2"></i> Seller Dashboard
                </a>

                <a class= "nav-link <?php if ($current_section == 'account') echo 'active'; ?>" 
                   href="my_profile.php?section=account"><!-- Account Settings link -->
                    <i class="fa fa-user-circle fa-fw mr-2"></i> Account Settings
                </a>
                <a class= "nav-link <?php if ($current_section == 'messages') echo 'active'; ?>" 
                   href="my_profile.php?section=messages"><!-- Messages link -->
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
                           href="my_profile.php?section=buyer&tab=mybids">My Bids</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'orders') echo 'active'; ?>" 
                           href="my_profile.php?section=buyer&tab=orders">My Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'viewed') echo 'active'; ?>" 
                           href="my_profile.php?section=buyer&tab=viewed">Recently Viewed</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'watchlist') echo 'active'; ?>" 
                           href="my_profile.php?section=buyer&tab=watchlist">Watchlist</a>
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
                      <?php include "mybids.php";?>
                    </div>
                    <!-- My Orders tab content -->
                    <div class="tab-pane fade <?php if ($current_tab == 'orders') echo 'show active'; ?>" 
                          id="orders" role="tabpanel">
                      <h5 class="card-title">My Orders</h5>
                      <p class="card-text"> Here you can view all of your orders</p>
                      <?php include "myorders.php";?>
                    </div>
                    <!-- Recently viewed tab content -->
                    <div class="tab-pane fade <?php if ($current_tab == 'viewed') echo 'show active'; ?>" 
                          id="viewed" role="tabpanel">
                      <h5 class="card-title">Recently viewed</h5>
                      <p class="card-text"> Here you can view all items you recently viewed</p>
                      <?php include "recentlyviewedtab.php";?>
                    </div>
                    <!-- Watchlist tab content -->
                    <div class="tab-pane fade <?php if ($current_tab == 'watchlist') echo 'show active'; ?>" 
                          id="watchlist" role="tabpanel">
                      <h5 class="card-title">Watchlist</h5>
                      <p class="card-text"> Here you can view all items you saved.</p>
                      <?php include "watchlist.php";?>
                    </div>
                </div> <!-- end buyer tab content -->
            </div> <!-- end card body -->
        <?php endif; ?> <!-- end buyer dashboard section -->

        <!-- ======================================================================== -->
        <!--                         SELLER DASHBOARD CONTENT                         -->
        <!-- ======================================================================== -->
        <?php if ($current_section == 'seller'): ?>
            <div class = "card">
                <div class="card-header">
                <!--- Horizontal sub tabs for seller dashboard --->
                <ul class= "nav nav-tabs card-header-tabs" id="seller-dashboard-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'listings') echo 'active'; ?>" 
                        <?php echo('href="my_profile.php?section=seller&tab=listings&seller_id=' . $seller_id . '"');?>>My Listings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'completed') echo 'active'; ?>" 
                        <?php echo('href="my_profile.php?section=seller&tab=listings&complete=true&seller_id=' . $seller_id . '"');?>>Completed Auctions</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="seller-tab-content">
                    
                    <!-- My Listings Tab Content -->
                    <div class="tab-pane fade <?php if ($current_tab == 'listings') echo 'show active'; ?>" 
                          id="listings" role="tabpanel">
                      <h5 class="card-title">My Listings</h5>
                      <p class="card-text"> Here you can view all of your listings.</p>
                      <?php include "mylistings.php";?>
                    </div>
                    <!-- Completed Auctions tab content -->
                    <div class="tab-pane fade <?php if ($current_tab == 'completed') echo 'show active'; ?>" 
                          id="completed" role="tabpanel">
                      <h5 class="card-title">Completed Auctions</h5>
                      <p class="card-text"> Here you can view all of your completed auctions.</p>
                      <?php include "mylistings.php";?>
                    </div>
                </div> <!-- end seller tab content -->
            </div> <!-- end card body -->
        <?php endif; ?> <!-- end seller dashboard section -->
        <!-- ======================================================================== -->
        <!--                         ACCOUNT DASHBOARD CONTENT                         -->
        <!-- ======================================================================== -->
        <?php if ($current_section == 'account'): ?>
            <div class = "card">
                <div class="card-header">
                <!--- Horizontal sub tabs for seller dashboard --->
                <ul class= "nav nav-tabs card-header-tabs" id="account-dashboard-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'details') echo 'active'; ?>" 
                        href="my_profile.php?section=account&tab=details">Account Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'password') echo 'active'; ?>" 
                        href="my_profile.php?section=account&tab=password">Password Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'payment_info') echo 'active'; ?>" 
                        href="my_profile.php?section=account&tab=payment_info">Payment Info</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="account-tab-content">
                    <!-- Account details Tab Content -->
                    <div class="tab-pane fade <?php if ($current_tab == 'details') echo 'show active'; ?>" 
                          id="details" role="tabpanel">
                      <h5 class="card-title">Account Details</h5>
                      <p class="card-text"> Here you can view/change your account details.</p>
                      <?php include "account_details.php";?>
                    </div>
                    <!-- Password Tab Content -->
                    <div class="tab-pane fade <?php if ($current_tab == 'password') echo 'show active'; ?>" 
                          id="password" role="tabpanel">
                      <h5 class="card-title">Password Info</h5>
                      <p class="card-text"> Here you can change your password details.</p>
                      <?php include "password_details.php";?>
                    </div>
                    <!-- Account details Tab Content -->
                    <div class="tab-pane fade <?php if ($current_tab == 'payment_info') echo 'show active'; ?>" 
                          id="payment_info" role="tabpanel">
                      <h5 class="card-title">Payemnt Info</h5>
                      <p class="card-text"> Here you can view/change your payment information.</p>
                      <?php include "payment_info.php";?>
                    </div>
                </div> <!-- end seller tab content -->
            </div> <!-- end card body -->
        <?php endif; ?> <!-- end seller dashboard section -->




        </div>
    </div>
</div>
            






