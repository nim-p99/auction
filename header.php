<?php
  // FIXME: At the moment, I've allowed these values to be set manually.
  // But eventually, with a database, these should be set automatically
  // ONLY after the user's login credentials have been verified via a 
  // database query.
session_start();
require_once 'database.php';


// user not logged in - session defaults 
if (!isset($_SESSION['logged_in'])) {
  $_SESSION['logged_in'] = false;
  $_SESSION['user_id'] = null;
  $_SESSION['account_type'] = null;
  $_SESSION['buyer_id'] = null;
  $_SESSION['seller_id'] = null;
  $_SESSION['admin_id'] = null;
}

$username = null;
$buyer_id = null;
$seller_id = null;
$admin_id = null;
// user logged in - determine account type
if (isset($_SESSION['user_id']) && $_SESSION['logged_in']) {

  $user_id = $_SESSION['user_id'];

  // fetch username from db 
  $query = $connection->prepare("SELECT username FROM users WHERE user_id = ?");
  $query->bind_param("i", $_SESSION['user_id']);
  $query->execute();
  $query->bind_result($username);
  $query->fetch();
  $query->close();
  $_SESSION['username'] = $username;

  // default role = buyer 
  $role = 'buyer';
  $query = $connection->prepare("SELECT buyer_id FROM buyer WHERE user_id = ?");
  $query->bind_param("i", $user_id);
  $query->execute();
  $query->store_result();

  if ($query->num_rows > 0) {
    $query->bind_result($buyer_id);
    $query->fetch();
    $_SESSION['buyer_id'] = $buyer_id;
  }
  $query->close();


  // check if seller 
  $query = $connection->prepare("SELECT seller_id FROM seller WHERE user_id = ?");
  $query->bind_param("i", $user_id);
  $query->execute();
  $query->store_result();

  // upgrade to seller
  if ($query->num_rows > 0) {
    $role = 'seller';
    $query->bind_result($seller_id);
    $query->fetch();
    $_SESSION['seller_id'] = $seller_id;
  }
  $query->close();

  // check if admin
  $query = $connection->prepare("SELECT admin_id FROM admin WHERE user_id = ?");
  $query->bind_param("i", $user_id);
  $query->execute();
  $query->store_result();

  // upgrade to admin 
  if ($query->num_rows > 0) {
    $role = 'admin';
    $query->bind_result($admin_id);
    $query->fetch();
    $_SESSION['admin_id'] = $admin_id;

  }
  $query->close();
  

  $_SESSION['account_type'] = $role;
  echo "You are logged in as: " . $_SESSION['account_type'];

}

# SETTING DEFAULT SESSION VARIABLES 

#$_SESSION['logged_in'] = false;
#$_SESSION['account_type'] = 'buyer';
#$_SESSION['user_id'] = 'Tony';
#$seller_id = $_SESSION['user_id'];

#$username = $_SESSION['user_id']


?>


<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <!-- Bootstrap and FontAwesome CSS -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <!-- Custom CSS file -->
  <link rel="stylesheet" href="css/custom.css">
  
  <!-- can use $pageTitle variable - which we set for each page -->
  <!-- $pageTitle = "Index" -->
  <!-- include header.php --> 
  <title>[My Auction Site] <!--CHANGEME!--></title>
</head>


<body>

<!-- Navbars -->
<nav class="navbar navbar-expand-lg navbar-light bg-light mx-2">
  <a class="navbar-brand" href="browse.php">auction site<!--CHANGEME!--></a>
  <ul class="navbar-nav ml-auto">
    <li class="nav-item">
    
<?php
  // Displays either login or logout on the right, depending on user's
  // current status (session).
  if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
    echo '<div class="d-flex align-items-center">';
    echo '<a class="nav-link" href="logout.php">Logout</a>';
    if ($_SESSION['user_id'] !== 1){
      echo '<a class="nav-link" href="my_profile.php">My Profile</a>';
    }
    echo '</div>'; 
  }
  else {
    echo '<button type="button" class="btn nav-link" data-toggle="modal" data-target="#loginModal">Login</button>';
  }
?>

    </li>
  </ul>
</nav>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <ul class="navbar-nav align-middle">
<?php
  if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'admin') {
  echo('
    <li class="nav-item mx-1">
        <a class="nav-link" href="admin_accs.php">Manage User Accounts</a>
      </li>
	<li class="nav-item mx-1">
      <a class="nav-link" href="admin_listings.php">Manage Listings</a>
    </li>
    ');
  }
  else{
    echo('
    <li class="nav-item mx-1">
      <a class="nav-link" href="browse.php">Browse</a>
    ');
    
      if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'buyer') {
    echo('
    </li>
    <li class="nav-item mx-1">
        <a class="nav-link" href="buyer.php?tab=mybids.php">My Bids</a>
      </li>
    <li class="nav-item mx-1">
        <a class="nav-link" href="buyer.php?tab=recommendations.php">Recommended</a>
      </li>
    <li class="nav-item mx-1">
        <a class="nav-link" href="buyer.php?tab=watchlist.php">Watchlist</a>
      </li>
    <li class="nav-item mx-1">
        <a class="nav-link" href="buyer.php?tab=recentlyviewed.php">Recently Viewed</a>
      </li>
    <li class="nav-item mx-1">
        <a class="nav-link" href="buyer.php?tab=myorders.php">My Orders</a>
      </li>
    <li class="nav-item mx-1">
        <a class="nav-link" href="my_profile.php">My Profile</a>
      </li>
      ');
    }
    if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'seller') {
    echo('
    
    <li class="nav-item mx-1">
        <a class="nav-link" href="seller.php?tab=mylistings.php">My Listings</a>
      </li>
    <li class="nav-item ml-3">
        <a class="nav-link btn border-light" href="create_auction.php">+ Create auction</a>
      </li>');
    }
  }
?>
  </ul>
</nav>

<!-- Login modal -->
<div class="modal fade" id="loginModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Login</h4>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <form method="POST" action="login_result.php">
          <div class="form-group">
            <label for="email">Email</label>
            <input name="email" type="text" class="form-control" id="email" placeholder="Email" required>

          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input name="password" type="password" class="form-control" id="password" placeholder="Password" required>
          </div>
          <button type="submit" class="btn btn-primary form-control">Sign in</button>
        </form>
        <div class="text-center">or <a href="register.php">create an account</a></div>
      </div>

    </div>
  </div>
</div> <!-- End modal -->
