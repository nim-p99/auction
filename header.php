<?php
  // Start session to maintain user login state
  session_start();
  require_once 'database.php';

  // Set seller_id and buyer_id from session if user is logged in
  if (isset($_SESSION['user_id'])) {
    $seller_id = $_SESSION['user_id'];
    $buyer_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
  }
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

  <!-- Bootstrap core JavaScript -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="js/bootstrap.bundle.min.js"></script>

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
    echo '<a class="nav-link" href="my_profile.php">My Profile</a>';
    echo '<a class="nav-link" href="logout.php">Logout</a>';

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
	<li class="nav-item mx-1">
      <a class="nav-link" href="browse.php">Browse</a>
    </li>
<?php
  if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'buyer') {
  echo('
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
      <a class="nav-link" href="mylistings.php?seller_id=' . $seller_id . '">My Listings</a>
    </li>
	<li class="nav-item ml-3">
      <a class="nav-link btn border-light" href="create_auction.php">+ Create auction</a>
    </li>');
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
            <input type="text" class="form-control" id="email" name="email" placeholder="Email">
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password">
          </div>
          <button type="submit" class="btn btn-primary form-control">Sign in</button>
        </form>
        <div class="text-center">or <a href="register.php">create an account</a></div>
      </div>

    </div>
  </div>
</div> <!-- End modal -->
