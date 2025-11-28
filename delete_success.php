<?php

include_once "includes/header.php";


if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: " . BASE_URL . "/browse.php");
    exit();
}

$redirect_delay = 4;


$target_url = BASE_URL . "/admin/admin_listings.php";

header("Refresh: $redirect_delay; url=$target_url");
?>

<div class="container mt-5">
    <div class="alert alert-success shadow-sm">
        <h2 class="alert-heading">Listing Deleted Successfully!</h2>
        <hr>
        <p class="mb-0">You will be redirected to the Manage Listings page in <?php echo $redirect_delay; ?> seconds.</p>
        <p class="mb-0">If you are not redirected, click <a href="<?php echo $target_url; ?>" class="alert-link">here</a>.</p>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>
