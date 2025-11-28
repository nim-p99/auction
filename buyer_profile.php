<?php 
include_once "header.php";
include_once "utilities.php";

$buyer_username = null;
$buyer_user_id = null;
// Example: Get seller ID from URL
$buyer_id= $_GET['buyer_id'] ?? null;

if (!$buyer_id) {
    die("No buyer specified.");
}
else {
  // get username from seller_id 
  $query = $connection->prepare(
    "SELECT u.username, u.user_id 
    FROM users AS u 
    JOIN buyer AS b 
    ON u.user_id = b.user_id 
    WHERE b.buyer_id = ?"
  );
  $query->bind_param("i", $buyer_id);
  $query->execute();
  $query->bind_result($buyer_username, $buyer_user_id);
  $query->fetch();
  $query->close();
}

// Define tabs and their corresponding PHP includes
$tabs = [
    'reviews'  => 'buyer_reviews.php',
];

// Get current tab from URL or default to 'listings'
$current_tab = $_GET['tab'] ?? 'reviews';

// Validate tab value
if (!array_key_exists($current_tab, $tabs)) {
    $current_tab = 'reviews';
}
?>

<div class="container mt-4 mb-4">
  <h2 class="my-3">Buyer Profile</h2>
  <p class="lead">Viewing buyer: <strong><?php echo htmlspecialchars($buyer_username); ?></strong></p>
  
  <div class="row">
    
    <!-- Sidebar navigation -->
    <div class="col-md-3">
      <div class="nav flex-column nav-pills" id="seller-tabs" role="tablist" aria-orientation="vertical">
        
        <a class="nav-link <?php if ($current_tab == 'reviews') echo 'active'; ?>" 
           href="buyer_profile.php?buyer_id=<?php echo urlencode($buyer_id); ?>&tab=reviews">
          <i class="fa fa-star fa-fw mr-2"></i> Buyer Reviews
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
