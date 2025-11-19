<?php include_once("header.php")?>
<?php require("utilities.php")?>

<?php
// Ensure user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
// Ensure user is a seller 
if ($_SESSION['account_type'] == 'buyer'){
  echo "You do not have a seller account. Create an auction to become a seller";
  # echo "<meta http-equiv='refresh' content='2;url=browse.php'>";
  echo "<p>To go back, <a href='browse.php'>click here</a></p>";
  #header("Location: browse.php");
  #exit();
}

// Define valid tabs for the seller dashboard
$tabs = [
  'mybids.php' => 'My Bids',
  'mylistings.php' => 'My Listings'
];

// Get the tab from URL or default to 'mylistings'
$current_tab = $_GET['tab'] ?? 'mylistings.php';

// If invalid, fall back to default
if (!array_key_exists($current_tab, $tabs)) {
    $current_tab = 'mylistings.php';
}

$tab_heading = $tabs[$current_tab];
?>

<div class="container mt-4 mb-4">
  <!-- Tab heading -->
  <h2 class="mb-3"><?php echo htmlspecialchars($tab_heading); ?></h2>
  <!-- Load tab content -->
  <div class="tab-content p-3 border rounded bg-light">
    <?php
      // Build the path safely
      if (file_exists($current_tab) && $_SESSION['account_type'] != 'buyer') {
        include $current_tab;
      } else {
        echo "<p>Sorry, that tab could not be loaded.</p>";
      }
    ?>
  </div>
</div>

<?php include_once "footer.php"; ?>

