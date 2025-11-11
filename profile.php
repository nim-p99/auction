<?php include_once("header.php")?>
<?php require("utilities.php")?>
<?php 
// Take session user_id --> convert to seller_id
# $_SESSION['user_id'] = "User";
// TODO: query database to extract buyer/ seller ids from user_id
?>

<div class="container">
<h2 class="my-3">My Profile</h2>
</div>
<ul class="list-group">
<?php
echo('<div class="p-2 mr-5"><h5><a href="seller.php?seller_id=' . $seller_id . '">Seller Profile</a></h5></div>');
echo('<div class="p-2 mr-5"><h5><a href="buyer.php">Buyer Profile</a></h5></div>');
echo('<div class="p-2 mr-5"><h5><a href="account.php">Account Information</a></h5></div>');
echo('<div class="p-2 mr-5"><h5><a href="messages.php">Messages</a></h5></div>');
?>
</ul>

<?php include_once("footer.php")?>
