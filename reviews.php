<?php include_once("header.php")?>
<?php require("utilities.php")?>

<div class="container">


<?php
  // This page is for showing a user the auction listings they've made.
  // It will be pretty similar to browse.php, except there is no search bar.
  // This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.
if (isset($_GET['seller_id'])) {
  $id = $_GET['seller_id'];
  $review_type = 'Seller';
}
elseif (isset($_GET['buyer_id'])) {
  $id = $_GET['buyer_id'];
  $review_type = 'Buyer';
}

  echo ('<h2 class="my-3">' . $id . " 's " . $review_type . ' reviews</h2>');
  
  // TODO: Check user's credentials (cookie/session).
  
  // TODO: Perform a query to pull up their reviews.
  
  // TODO: Loop through results and print them out
  
?>


<?php include_once("footer.php")?>
