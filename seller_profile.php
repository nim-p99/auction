<?php 
// 1. Include Header & Utilities
include_once "includes/header.php";
require_once "includes/utilities.php";

$seller_username = null;
$seller_user_id = null;

// Get seller ID from URL
$seller_id = $_GET['seller_id'] ?? null;

if (!$seller_id) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>No seller specified.</div></div>";
    include_once "includes/footer.php";
    exit();
} else {
    // Get username AND user_id from seller table
    // We need user_id for the messaging system
    $query = $connection->prepare(
        "SELECT u.username, u.user_id 
         FROM users AS u 
         JOIN seller AS s ON u.user_id = s.user_id 
         WHERE s.seller_id = ?"
    );
    $query->bind_param("i", $seller_id);
    $query->execute();
    $query->bind_result($seller_username, $seller_user_id);
    $query->fetch();
    $query->close();

    if (!$seller_username) {
        echo "<div class='container mt-4'><div class='alert alert-danger'>Seller not found.</div></div>";
        include_once "includes/footer.php";
        exit();
    }
}

// Define tabs and their corresponding PHP files
$tabs = [
    'listings' => 'mylistings.php',
    'completed' => 'completed_auctions.php',
    'reviews'  => 'reviews.php',
    'message'  => 'messages.php'
];

// Get current tab from URL or default to 'listings'
$current_tab = $_GET['tab'] ?? 'listings';

// Validate tab value
if (!array_key_exists($current_tab, $tabs)) {
    $current_tab = 'listings';
}

// Construct path
$tab_path = 'partials/' . $tabs[$current_tab];
?>

<div class="container mt-4 mb-4">
  <h2 class="my-3">Seller Profile</h2>
  <p class="lead">Viewing seller: <strong><?php echo htmlspecialchars($seller_username); ?></strong></p>
  
  <div class="row">
    
    <div class="col-md-3">
      <div class="nav flex-column nav-pills" id="seller-tabs" role="tablist" aria-orientation="vertical">
        
        <a class="nav-link <?php if ($current_tab == 'listings') echo 'active'; ?>" 
           href="<?php echo BASE_URL; ?>/seller_profile.php?seller_id=<?php echo urlencode($seller_id); ?>&tab=listings">
          <i class="fa fa-gavel fa-fw mr-2"></i> Listings
        </a>

        <a class="nav-link <?php if ($current_tab == 'completed') echo 'active'; ?>" 
           href="<?php echo BASE_URL; ?>/seller_profile.php?seller_id=<?php echo urlencode($seller_id); ?>&tab=completed">
          <i class="fa fa-money fa-fw mr-2"></i> Completed Auctions
        </a>

        <a class="nav-link <?php if ($current_tab == 'reviews') echo 'active'; ?>" 
           href="<?php echo BASE_URL; ?>/seller_profile.php?seller_id=<?php echo urlencode($seller_id); ?>&tab=reviews">
          <i class="fa fa-star fa-fw mr-2"></i> Seller Reviews
        </a>

        <a class="nav-link <?php if ($current_tab == 'message') echo 'active'; ?>" 
           href="<?php echo BASE_URL; ?>/seller_profile.php?seller_id=<?php echo urlencode($seller_id); ?>&tab=message">
          <i class="fa fa-envelope fa-fw mr-2"></i> Message Seller
        </a>

      </div>
    </div>

    <div class="col-md-9">
      <div class="card">
        <div class="card-body">
          <?php 
          if (file_exists($tab_path)) {
              include $tab_path;
          } else {
              echo "<div class='alert alert-warning'>Tab content not found: " . htmlspecialchars($current_tab) . "</div>";
          }
          ?>
        </div>
      </div>
    </div>
    
  </div> </div> <?php include_once "includes/footer.php"; ?>
