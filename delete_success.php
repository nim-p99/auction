<?php
include_once("header.php");
if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: browse.php");
    exit();
}

$redirect_delay = 4;
$target_url = "admin_listings.php";

header("Refresh: $redirect_delay; url=$target_url");
?>

<div class="container mt-5">
    <h2> Listing Deleted Successfully!</h2>
    <p> You will be redirected to the Manage Listings page  in <?php echo $redirect_delay; ?> seconds. </p>
    <p> If you are not redirected, click <a href="<?php echo $target_url; ?>">here</a>.</p>
</div>