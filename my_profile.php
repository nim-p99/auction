<?php
include_once "includes/header.php";
require_once "includes/utilities.php";

// 1. Access Control
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$username = $_SESSION['username'];
$seller_id = $_SESSION['seller_id'] ?? null;

// 2. Define Sections
$sections = [
    'buyer' => ['mybids', 'orders', 'recommendations', 'watchlist'],
    'seller' => ['listings', 'completed'],
    'account' => ['details', 'password', 'address'],
    'messages' => ['inbox'],
];

// 3. Map Short Names to Real Filenames
$file_mapping = [
    // Buyer
    'mybids'          => 'mybids.php',
    'orders'          => 'myorders.php',
    'recommendations' => 'recommendations.php',
    'watchlist'       => 'watchlist.php',
    
    // Seller
    'listings'        => 'mylistings.php',
    'completed'       => 'completed_auctions.php',
    
    // Account
    'details'         => 'account_details.php',
    'password'        => 'password_details.php',
    'address'         => 'address_details.php',
    
    // Messages
    'inbox'           => 'inbox.php'
];

// 4. Determine Current Section and Tab
$allowed_section = array_keys($sections);
$current_section = $_GET['section'] ?? 'buyer'; 

if (!in_array($current_section, $allowed_section)) {
    $current_section = 'buyer'; 
}

$current_tab = $_GET['tab'] ?? $sections[$current_section][0]; 

// Validation: Ensure the tab belongs to the section
if (!in_array($current_tab, $sections[$current_section])) {
    $current_tab = $sections[$current_section][0];
}

// 5. Construct Path
$real_filename = $file_mapping[$current_tab];
$tab_path = __DIR__ . '/partials/' . $real_filename;
?>

<div class="container mt-4 mb-4">
    <h2 class="my-3">My Profile</h2> 
    <p class="lead"> Welcome back, <?php echo htmlspecialchars($username); ?>! </p>

    <div class="row">

        <div class="col-md-3">
            <div class="nav flex-column nav-pills" id="profile-sections" role="tablist" aria-orientation="vertical">
                
                <a class="nav-link <?php if ($current_section == 'buyer') echo 'active'; ?>" 
                   href="<?php echo BASE_URL; ?>/my_profile.php?section=buyer">
                    <i class="fa fa-shopping-basket fa-fw mr-2"></i> Buyer Dashboard
                </a>

                <?php if ($seller_id): ?>
                <a class="nav-link <?php if ($current_section == 'seller') echo 'active'; ?>" 
                   href="<?php echo BASE_URL; ?>/my_profile.php?section=seller">
                    <i class="fa fa-gavel fa-fw mr-2"></i> Seller Dashboard
                </a>
                <?php endif; ?>

                <a class="nav-link <?php if ($current_section == 'account') echo 'active'; ?>" 
                   href="<?php echo BASE_URL; ?>/my_profile.php?section=account">
                    <i class="fa fa-user-circle fa-fw mr-2"></i> Account Settings
                </a>
                
                <a class="nav-link <?php if ($current_section == 'messages') echo 'active'; ?>" 
                   href="<?php echo BASE_URL; ?>/my_profile.php?section=messages">
                    <i class="fa fa-envelope fa-fw mr-2"></i> Messages
                </a>    
            </div>
        </div> 
        
        <div class="col-md-9">

        <?php if ($current_section == 'buyer'): ?>
            <div class="card">
                <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'mybids') echo 'active'; ?>" 
                           href="<?php echo BASE_URL; ?>/my_profile.php?section=buyer&tab=mybids">My Bids</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'orders') echo 'active'; ?>" 
                           href="<?php echo BASE_URL; ?>/my_profile.php?section=buyer&tab=orders">My Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'recommendations') echo 'active'; ?>" 
                           href="<?php echo BASE_URL; ?>/my_profile.php?section=buyer&tab=recommendations">Recommendations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'watchlist') echo 'active'; ?>" 
                           href="<?php echo BASE_URL; ?>/my_profile.php?section=buyer&tab=watchlist">Watchlist</a>
                    </li>
                </ul>
                </div>
                <div class="card-body">
                    <?php 
                        if (file_exists($tab_path)) { include $tab_path; } 
                        else { echo "<div class='alert alert-danger'>Error: File '$real_filename' not found.</div>"; }
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($current_section == 'seller'): ?>
            <div class="card">
                <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'listings') echo 'active'; ?>" 
                           href="<?php echo BASE_URL; ?>/my_profile.php?section=seller&tab=listings">My Listings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'completed') echo 'active'; ?>" 
                           href="<?php echo BASE_URL; ?>/my_profile.php?section=seller&tab=completed">Completed Auctions</a>
                    </li>
                </ul>
                </div>
                <div class="card-body">
                    <?php 
                        if (file_exists($tab_path)) { include $tab_path; } 
                        else { echo "<div class='alert alert-danger'>Error: File '$real_filename' not found.</div>"; }
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($current_section == 'account'): ?>
            <div class="card">
                <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'details') echo 'active'; ?>" 
                           href="<?php echo BASE_URL; ?>/my_profile.php?section=account&tab=details">Account Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'password') echo 'active'; ?>" 
                           href="<?php echo BASE_URL; ?>/my_profile.php?section=account&tab=password">Password Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'address') echo 'active'; ?>" 
                           href="<?php echo BASE_URL; ?>/my_profile.php?section=account&tab=address">Address Info</a>
                    </li>
                </ul>
                </div>
                <div class="card-body">
                    <?php 
                        if (file_exists($tab_path)) { include $tab_path; } 
                        else { echo "<div class='alert alert-danger'>Error: File '$real_filename' not found.</div>"; }
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($current_section == 'messages'): ?>
            <div class="card">
                <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link <?php if ($current_tab == 'inbox') echo 'active'; ?>" 
                           href="<?php echo BASE_URL; ?>/my_profile.php?section=messages&tab=inbox">My Inbox</a>
                    </li>
                </ul>
                </div>
                <div class="card-body">
                    <?php 
                        if (file_exists($tab_path)) { include $tab_path; } 
                        else { echo "<div class='alert alert-danger'>Error: File '$real_filename' not found.</div>"; }
                    ?>
                </div>
            </div>
        <?php endif; ?>

        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>
