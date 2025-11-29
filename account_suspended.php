<?php include_once("header.php")?>
<?php require("utilities.php")?>

<div class="container mt-5">
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0"><i class="fa fa-exclamation-triangle"></i> Account Suspended</h4>
                </div>
                <div class="card-body text-center">
                    <h5 class="card-title mt-3">Access to your account has been temporarily disabled.</h5>
                    <p class="card-text">This may be due to a violation of our terms of service or a security precaution.</p>
                    <p class="text-muted">If you believe this is a mistake, you can contact the site administrator below to request a review.</p>
                    
                    <hr>

                    <?php
                    // Display success message if redirected back here
                    if (isset($_GET['sent']) && $_GET['sent'] == 1) {
                        echo '<div class="alert alert-success">Your request has been sent to the administrator.</div>';
                    } else {
                        // 1. Get Admin ID
                        $admin_query = $connection->prepare("SELECT user_id FROM admin LIMIT 1");
                        $admin_query->execute();
                        $admin_query->bind_result($admin_user_id);
                        $admin_query->fetch();
                        $admin_query->close();
                    ?>

                    <form action="process_send_message.php" method="POST" class="text-left">
                        <input type="hidden" name="recipient_id" value="<?php echo $admin_user_id; ?>">
                        <input type="hidden" name="return_url" value="account_suspended.php?sent=1">

                        <div class="form-group">
                            <label for="message_body"><strong>Message to Administrator:</strong></label>
                            <textarea class="form-control" name="message_body" id="message_body" rows="4" required>Hi, my account has been suspended. Please review my account and re-enable it.</textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-danger">Send Request</button>
                        </div>
                    </form>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

