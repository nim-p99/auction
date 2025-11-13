<?php include_once("header.php")?>
<?php require("utilities.php")?>

<?php
  $seller_id = $_GET['seller_id'];


// TODO: GET seller_id from url --> run query to obtain
// seller info (eg username, user_id). Set variables below
// for ease. TO REMOVE.
session_start();
$_SESSION['user_id'] = "nim";

?>


<?php echo("<div class='container'><h2 class='my-3'>" . $seller_id . "'s Seller Profile</h2></div>");?>


<ul class="list-group">
<li>
<?php 
echo('<div class="p-2 mr-5"><h5><a href="mylistings.php?seller_id=' . $seller_id . '">Listings</a></h5></div>');
echo('<div class="p-2 mr-5"><h5><a href="reviews.php?seller_id=' . $seller_id . '">Seller reviews</a></h5></div>');
?>
</li>
</ul>

<?php
// TODO: Check users's credentials (cookie/session).
// TODO: If 'seller' != 'user': Insert message seller button.

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true && $_SESSION['user_id'] != $seller_id) {
  echo '<div class="p-2 mr-5"><h5><a href="sendmessage.php">Message seller</a></h5></div>';
}



// $_SESSION['logged_in'] = true;
// $_SESSION['username'] = "test";
?>

<?php include_once("footer.php")?>
