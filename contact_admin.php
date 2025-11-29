<?php include_once("header.php")?>
<?php require("utilities.php")?>

<div class="container mt-5">
  <h2>Contact Site Administration</h2>

  <?php
  // check if user is logged in
  if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
      echo '<div class="alert alert-warning">You must be logged in to contact the administrator. <a href="browse.php">Go to Login</a></div>';
  } else {
      
      // 2. Get the Admin's User ID
      // We assume there is at least one row in the 'admin' table.
      // We join with 'users' just to be safe and get the name if needed.
      $admin_query = $connection->prepare("
          SELECT a.user_id, u.username 
          FROM admin a
          JOIN users u ON a.user_id = u.user_id
          LIMIT 1
      ");
      $admin_query->execute();
      $admin_query->bind_result($admin_user_id, $admin_username);
      
      if (!$admin_query->fetch()) {
          echo '<div class="alert alert-danger">Error: No administrator found in the database.</div>';
          $admin_query->close();
      } else {
          $admin_query->close();
          
          // display success message if redirected back here
          if (isset($_GET['sent']) && $_GET['sent'] == 1) {
              echo '<div class="alert alert-success">Your message has been sent to the administrator.</div>';
          }
          ?>

          <div class="row">
            <div class="col-md-8">
              <p class="lead">Need help with an auction, account, or technical issue? Send us a message below.</p>
              
              <div class="card bg-light">
                <div class="card-body">
                  <form action="process_send_message.php" method="POST">
                      <input type="hidden" name="recipient_id" value="<?php echo $admin_user_id; ?>">
                      
                      <input type="hidden" name="return_url" value="contact_admin.php?sent=1">

                      <div class="form-group">
                          <label for="message_body">Message Details</label>
                          <textarea class="form-control" name="message_body" id="message_body" rows="6" placeholder="I need help disabling my auction because..." required></textarea>
                      </div>
                      
                      <button type="submit" class="btn btn-primary">Send to Admin</button>
                  </form>
                </div>
              </div>
            </div>

            <div class="col-md-4">
               <div class="card"> 
                 <div class="card-body">
                   <p>Admins typically reply within 24 hours.</p>
                   <p>Check your <a href="my_profile.php?section=messages">Inbox</a> for replies.</p>
                 </div>
               </div>
            </div>
          </div>

      <?php 
      } 
  } 
  ?>
</div>

<?php include_once("footer.php")?>
