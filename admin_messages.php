<?php include_once("header.php")?>
<?php require("utilities.php")?>

<?php
// ensure user is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$check_admin = $connection->prepare("SELECT admin_id FROM admin WHERE user_id = ?");
$check_admin->bind_param("i", $_SESSION['user_id']);
$check_admin->execute();
$check_admin->store_result();

if ($check_admin->num_rows === 0) {
    // User is not an admin
    $check_admin->close();
    header("Location: browse.php");
    exit();
}
$check_admin->close();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2 class="border-bottom pb-2">Administrator Inbox</h2>
            <div class="alert alert-info">
                These are messages sent directly to the Site Administrator account.
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?php include "inbox.php"; ?>
        </div>
    </div>
</div>

<?php include_once("footer.php")?>
