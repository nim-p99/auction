<?php
  // FIXME: At the moment, I've allowed these values to be set manually.
  // But eventually, with a database, these should be set automatically
  // ONLY after the user's login credentials have been verified via a 
  // database query.
session_start();

date_default_timezone_set('Europe/London');

require_once 'database.php';

/**
 * Handle cookie consent form submission
 * ----------------------------------------
 * This satisfies:
 *  - Tell site visitors how cookies are being used (see cookies.php)
 *  - Obtain user’s consent
 *  - Do not use optional cookies if user does not consent
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cookie_choice'])) {
    $choice = ($_POST['cookie_choice'] === 'accept') ? 'accept' : 'decline';

    // Remember consent decision for 1 year
    setcookie('cookie_consent', $choice, time() + (60 * 60 * 24 * 365), "/");

    // If the user declines, immediately remove any optional login cookies we use
    if ($choice === 'decline') {
        if (isset($_COOKIE['userID'])) {
            setcookie('userID', '', time() - 3600, "/");
        }
        if (isset($_COOKIE['username'])) {
            setcookie('username', '', time() - 3600, "/");
        }
    }

    // Make the choice visible in this request as well
    $_COOKIE['cookie_consent'] = $choice;
}


// user not logged in - session defaults 
if (!isset($_SESSION['logged_in'])) {
  $_SESSION['logged_in'] = false;
  $_SESSION['user_id'] = null;
  $_SESSION['account_type'] = null;
  $_SESSION['buyer_id'] = null;
  $_SESSION['seller_id'] = null;
  $_SESSION['admin_id'] = null;
}

/* If not logged in but we have login cookies AND consent, restore login */
if (
    $_SESSION['logged_in'] === false &&
    isset($_COOKIE['cookie_consent']) && $_COOKIE['cookie_consent'] === 'accept' &&
    isset($_COOKIE['userID']) && isset($_COOKIE['username'])
) {
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id']   = (int)$_COOKIE['userID'];
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
  //echo "You are logged in as: " . $_SESSION['account_type'];

}

# SETTING DEFAULT SESSION VARIABLES 

#$_SESSION['logged_in'] = false;
#$_SESSION['account_type'] = 'buyer';
#$_SESSION['user_id'] = 'Tony';
#$seller_id = $_SESSION['user_id'];

#$username = $_SESSION['user_id']

$hasCookieConsent = (isset($_COOKIE['cookie_consent']) && $_COOKIE['cookie_consent'] === 'accept');
// Track recently viewed items

if ($hasCookieConsent) {
    $currentScript = basename($_SERVER['PHP_SELF'] ?? '');

    if ($currentScript === 'listing.php' && isset($_GET['item_id'])) {
        $itemId = (int)$_GET['item_id'];

        if ($itemId > 0) {
            $recentItems = [];

            if (!empty($_COOKIE['recent_items'])) {
                $decoded = json_decode($_COOKIE['recent_items'], true);
                if (is_array($decoded)) {
                    foreach ($decoded as $id) {
                        $id = (int)$id;
                        if ($id > 0) {
                            $recentItems[] = $id;
                        }
                    }
                }
            }

            // Remove if it already exists (add to the front)
            $recentItems = array_values(array_filter(
                $recentItems,
                function($id) use ($itemId) {
                    return (int)$id !== $itemId;
                }
            ));

            // Add item to the front
            array_unshift($recentItems, $itemId);

            // Keep only the 10 most recent
            $recentItems = array_slice($recentItems, 0, 10);

            $encoded = json_encode($recentItems);
            setcookie('recent_items', $encoded, time() + (60 * 60 * 24 * 30), "/");
            $_COOKIE['recent_items'] = $encoded;
        }
    }
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
  
  <!-- can use $pageTitle variable - which we set for each page -->
  <!-- $pageTitle = "Index" -->
  <!-- include header.php --> 
  <title>Auction</title>
</head>


<body>

<?php if ($_SESSION['logged_in'] === true && !isset($_COOKIE['cookie_consent'])): ?>
  <div class="alert alert-info altert-dismissible fade show mb-0" role="alert" style="position: sticy; top: 0; z-index: 1050;">
    <div class="container d-flex flex-column flex-md-row align-items-md-center">
      <div class="mr-md-3">
        <strong>Cookies on UCeL</strong>
        <p class="mb-0 small">
          We use essential cookies to make this site work and optional cookies to remember you and improve your experience. You can read more on our<a href="cookies.php" class="alert-link"> Cookies page</a>.
        </p>
      </div>
      <div class="ml-md-auto mt-3 mt-md-0">
        <form method="post" class="d-inline">
          <input type="hidden" name="cookie_choice" value="accept">
          <button type="submit" class="btn btn-sm btn-primary mr-4">Accept cookies</button>
        </form>

        <form method="post" class="d-inline">
          <input type="hidden" name="cookie_choice" value="decline">
          <button type="submit" class="btn btn-sm btn-secondary">Decline</button>
        </form>
      </div>
    </div>
  </div>
<?php endif; ?>  

<!-- Navbars -->
<nav class="navbar navbar-expand-lg navbar-light bg-light mx-2">

  <a class="navbar-brand" href="browse.php">
    <img src="ucel_logo.png" alt="UCeL" style="max-height: 50px; width: auto;">
  </a>
  <ul class="navbar-nav ml-auto">
    <li class="nav-item">
    
<?php
  // Displays either login or logout on the right, depending on user's
  // current status (session).
  if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
    echo '<div class="d-flex align-items-center">';

    $badgeType = 'secondary'; // default grey
    if ($_SESSION['account_type'] == 'admin') { $badgeType = 'danger'; } // Red
    elseif ($_SESSION['account_type'] == 'seller') { $badgeType = 'success'; } // Green
    elseif ($_SESSION['account_type'] == 'buyer') { $badgeType = 'info'; } // Blue

    echo '<span class="badge badge-' . $badgeType . ' mr-2">' . ucfirst($_SESSION['account_type']) . '</span>';

    if ($_SESSION['user_id'] !== 1 && (!isset($_SESSION['is_suspended']) || !$_SESSION['is_suspended'])){
      echo '<a class="nav-link mr-3" href="my_profile.php">My Profile</a>';
    }
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
<?php
  if (isset($_SESSION['is_suspended']) && $_SESSION['is_suspended'] === true) {

  }
  elseif (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'admin') {
  echo('
    <li class="nav-item mx-1">
        <a class="nav-link" href="admin_accs.php">Manage User Accounts</a>
      </li>
	<li class="nav-item mx-1">
      <a class="nav-link" href="admin_listings.php">Manage Listings</a>
    </li>
    <li class="nav-item mx-1">
      <a class="nav-link" href="admin_messages.php">Messages</a>
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
        <a class="nav-link" href="buyer.php?tab=recommendations.php">Recommendations</a>
      </li>  
    <li class="nav-item ml-3">
        <a class="nav-link btn border-light" href="create_auction.php">+ Create auction</a>
      </li>
    <li class="nav-item ml-3">
        <a class="nav-link btn border-light" href="pending_review.php">Pending Reviews</a>
      </li>'
      );
    }
    if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'seller') {
    echo('
    
    <li class="nav-item mx-1">
        <a class="nav-link" href="seller.php?tab=mylistings.php">My Listings</a>
      </li>
    <li class="nav-item mx-1">
        <a class="nav-link" href="buyer.php?tab=mybids.php">My Bids</a>
      </li>
    <li class="nav-item mx-1">
        <a class="nav-link" href="buyer.php?tab=recommendations.php">Recommendations</a>
      </li>  
    <li class="nav-item ml-3">
        <a class="nav-link btn border-light" href="create_auction.php">+ Create auction</a>
      </li>
    <li class="nav-item ml-3">
        <a class="nav-link btn border-light" href="pending_review.php">Pending Reviews</a>
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
