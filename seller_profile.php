<?php 
include_once "header.php";
include_once "utilities.php";

$seller_username = null;
// Example: Get seller ID from URL
$seller_id = $_GET['seller_id'] ?? null;
if (!$seller_id) {
    die("No seller specified.");
}
else {
  // get username from seller_id 
  $query = $connection->prepare(
    "SELECT u.username 
    FROM users AS u 
    JOIN seller AS s 
    ON u.user_id = s.user_id 
    WHERE s.seller_id = ?"
  );
  $query->bind_param("i", $seller_id);
  $query->execute();
  $query->bind_result($seller_username);
  $query->fetch();
  $query->close();
}

// Define tabs and their corresponding PHP includes
$tabs = [
    'listings' => 'mylistings.php',
    'reviews'  => 'reviews.php',
    'message'  => 'messages.php'
];

// Get current tab from URL or default to 'listings'
$current_tab = $_GET['tab'] ?? 'listings';

// Validate tab value
if (!array_key_exists($current_tab, $tabs)) {
    $current_tab = 'listings';
}
?>

<div class="container mt-4 mb-4">
  <h2 class="my-3">Seller Profile</h2>
  <p class="lead">Viewing seller: <strong><?php echo htmlspecialchars($seller_username); ?></strong></p>
  
  <div class="row">
    
    <!-- Sidebar navigation -->
    <div class="col-md-3">
      <div class="nav flex-column nav-pills" id="seller-tabs" role="tablist" aria-orientation="vertical">
        
        <a class="nav-link <?php if ($current_tab == 'listings') echo 'active'; ?>" 
           href="seller_profile.php?seller_id=<?php echo urlencode($seller_id); ?>&tab=listings">
          <i class="fa fa-gavel fa-fw mr-2"></i> Listings
        </a>

        <a class="nav-link <?php if ($current_tab == 'reviews') echo 'active'; ?>" 
           href="seller_profile.php?seller_id=<?php echo urlencode($seller_id); ?>&tab=reviews">
          <i class="fa fa-star fa-fw mr-2"></i> Seller Reviews
        </a>

        <a class="nav-link <?php if ($current_tab == 'message') echo 'active'; ?>" 
           href="seller_profile.php?seller_id=<?php echo urlencode($seller_id); ?>&tab=message">
          <i class="fa fa-envelope fa-fw mr-2"></i> Message Seller
        </a>

      </div>
    </div>

    <!-- Main content area -->
    <div class="col-md-9">
      <div class="card">
        <div class="card-body">
          <?php 
          // Safely include the selected tab content
          include $tabs[$current_tab];
          ?>
        </div>
      </div>
    </div>
    
  </div> <!-- end row -->
</div> <!-- end container -->

<?php include_once "footer.php"; ?>
