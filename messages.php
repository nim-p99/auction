<?php

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo '<div class="alert alert-warning">You must be <a href="#" data-toggle="modal" data-target="#loginModal">logged in</a> to message this seller.</div>';
} 
elseif ($_SESSION['user_id'] == $seller_user_id) {
    echo '<div class="alert alert-info">You cannot message yourself.</div>';
} 
else {
?>
    <h4>Send a message to <?php echo htmlspecialchars($seller_username); ?></h4>
    
    <form action="process_send_message.php" method="POST">
        <input type="hidden" name="recipient_id" value="<?php echo $seller_user_id; ?>">
        
        <input type="hidden" name="return_url" value="seller_profile.php?seller_id=<?php echo $seller_id; ?>&tab=message">

        <div class="form-group">
            <textarea class="form-control" name="message_body" rows="4" placeholder="Hi, I have a question about..." required></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Send Message</button>
    </form>
<?php } ?>
