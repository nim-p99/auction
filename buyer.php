<?php 
include_once "includes/header.php";
require_once "includes/utilities.php";

// Ensure user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Define valid tabs for the buyer dashboard

$tabs = [
  'mybids.php' => 'My Bids',
  'myorders.php' => 'My Orders',
  'recommendations.php' => 'Recommendations For You',
  'recentlyviewed.php' => 'Recently Viewed',
  'watchlist.php' => 'Watchlist'
];

// Get the tab from URL or default to 'mybids.php'
$current_tab = $_GET['tab'] ?? 'mybids.php';

// If invalid, fall back to default
if (!array_key_exists($current_tab, $tabs)) {
    $current_tab = 'mybids.php';
}

$tab_heading = $tabs[$current_tab];

// Construct the full path to the file

$tab_path = 'partials/' . $current_tab;
?>

<div class="container mt-4 mb-4">
  <h2 class="mb-3"><?php echo htmlspecialchars($tab_heading); ?></h2>
  
  <div class="tab-content">
    <?php
      // Check if the file exists in the partials directory
      if (file_exists($tab_path)) {
        include $tab_path;
      } else {
        echo "<div class='alert alert-danger'>Error: The file '$tab_path' could not be found.</div>";
      }
    ?>
  </div>
</div>

<?php include_once "includes/footer.php"; ?>
