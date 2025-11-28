<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Session Defaults
if (!isset($_SESSION['logged_in'])) {
  $_SESSION['logged_in'] = false;
  $_SESSION['user_id'] = null;
  $_SESSION['account_type'] = null;
  $_SESSION['buyer_id'] = null;
  $_SESSION['seller_id'] = null;
  $_SESSION['admin_id'] = null;
}

// User Logic (Only run if logged in)
if (isset($_SESSION['user_id']) && $_SESSION['logged_in']) {
  $user_id = $_SESSION['user_id'];

  // 1. Fetch Username
  $query = $connection->prepare("SELECT username FROM users WHERE user_id = ?");
  $query->bind_param("i", $user_id);
  $query->execute();
  $query->bind_result($username);
  $query->fetch();
  $query->close();
  $_SESSION['username'] = $username;

  // Default role
  $role = 'buyer';

  // 2. Check Buyer
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

  // 3. Check Seller
  $query = $connection->prepare("SELECT seller_id FROM seller WHERE user_id = ?");
  $query->bind_param("i", $user_id);
  $query->execute();
  $query->store_result();
  if ($query->num_rows > 0) {
    $role = 'seller';
    $query->bind_result($seller_id);
    $query->fetch();
    $_SESSION['seller_id'] = $seller_id;
  }
  $query->close();

  // 4. Check Admin
  $query = $connection->prepare("SELECT admin_id FROM admin WHERE user_id = ?");
  $query->bind_param("i", $user_id);
  $query->execute();
  $query->store_result();
  if ($query->num_rows > 0) {
    $role = 'admin';
    $query->bind_result($admin_id);
    $query->fetch();
    $_SESSION['admin_id'] = $admin_id;
  }
  $query->close();
  
  $_SESSION['account_type'] = $role;
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/custom.css">
  
  <title>Auction Site</title>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light mx-2">
  <a class="navbar-brand" href="<?php echo BASE_URL; ?>/browse.php">auction.</a>
  
  <ul class="navbar-nav ml-auto">
    <?php
      if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
        
        echo '<li class="nav-item d-flex align-items-center">';
        echo '<span class="navbar-text mr-3">Logged in as: <strong>' . htmlspecialchars($_SESSION['account_type']) . '</strong></span>';
        echo '</li>';

        if ($_SESSION['user_id'] !== 1){
          echo '<li class="nav-item">';
          echo '<a class="nav-link" href="' . BASE_URL . '/my_profile.php">My Profile</a>';
          echo '</li>';
        }
        
        echo '<li class="nav-item">';
        echo '<a class="nav-link" href="' . BASE_URL . '/actions/logout.php">Logout</a>';
        echo '</li>';
        
      } else {
        echo '<li class="nav-item">';
        echo '<button type="button" class="btn nav-link" data-toggle="modal" data-target="#loginModal">Login</button>';
        echo '</li>';
      }
    ?>
  </ul>
</nav>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <ul class="navbar-nav align-middle">
    <?php
    if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'admin') {
      // ADMIN LINKS
      echo '<li class="nav-item mx-1"><a class="nav-link" href="'.BASE_URL.'/admin/admin_accs.php">Manage User Accounts</a></li>';
      echo '<li class="nav-item mx-1"><a class="nav-link" href="'.BASE_URL.'/admin/admin_listings.php">Manage Listings</a></li>';
    } 
    else {
      // STANDARD LINKS
      echo '<li class="nav-item mx-1"><a class="nav-link" href="'.BASE_URL.'/browse.php">Browse</a></li>';
      
      if (isset($_SESSION['account_type']) && ($_SESSION['account_type'] == 'buyer' || $_SESSION['account_type'] == 'seller')) {
        echo '<li class="nav-item mx-1"><a class="nav-link" href="'.BASE_URL.'/my_profile.php?section=buyer&tab=mybids">My Bids</a></li>';
        
        echo '<li class="nav-item mx-1"><a class="nav-link" href="'.BASE_URL.'/my_profile.php?section=buyer&tab=recommendations">Recommendations</a></li>';
        
        echo '<li class="nav-item ml-3"><a class="nav-link btn border-light" href="'.BASE_URL.'/create_auction.php">+ Create auction</a></li>';
      }

      // SELLER SPECIFIC
      if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'seller') {
         echo '<li class="nav-item mx-1"><a class="nav-link" href="'.BASE_URL.'/my_profile.php?section=seller&tab=listings">My Listings</a></li>';
      }
    }
    ?>
  </ul>
</nav>

<div class="modal fade" id="loginModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title">Login</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
        
        <?php if(isset($_SESSION['login_error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?></div>
            <?php $open_login_modal = true; ?>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>/actions/login_result.php">
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
        
        <div class="text-center mt-3">
            or <a href="<?php echo BASE_URL; ?>/register.php">create an account</a>
        </div>
      </div>

    </div>
  </div>
</div>

<?php if (isset($open_login_modal) && $open_login_modal): ?>
<script>
    document.addEventListener("DOMContentLoaded", function(){
        $('#loginModal').modal('show');
    });
</script>
<?php endif; ?>
