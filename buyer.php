<?php include_once("header.php")?>
<?php require("utilities.php")?>

<?php
// Ensure user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Define valid tabs for the buyer dashboard
$valid_tabs = [
  'mybids.php',
  'myorders.php',
  'recommendations.php',
  'recentlyviewed.php',
  'watchlist.php'];

// Get the tab from URL or default to 'mybids'
$current_tab = $_GET['tab'] ?? 'mybids.php';

// If invalid, fall back to default
if (!in_array($current_tab, $valid_tabs)) {
    $current_tab = 'mybids.php';
}
?>

<div class="container mt-4 mb-4">
  <!-- Load tab content -->
  <div class="tab-content p-3 border rounded bg-light">
    <?php
      // Build the path safely
      if (file_exists($current_tab)) {
        include $current_tab;
      } else {
        echo "<p>Sorry, that tab could not be loaded.</p>";
      }
    ?>
  </div>
</div>

<?php include_once "footer.php"; ?>


