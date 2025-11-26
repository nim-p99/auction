<?php
include_once("utilities.php");
// CHECK FOR MESSAGES FROM process_change_password.php
if (isset($_SESSION['pass_msg'])) {
    $msg = $_SESSION['pass_msg'];
    $type = $_SESSION['pass_type'] ?? 'info';
    
    echo '<div class="alert alert-' . $type . '">' . htmlspecialchars($msg) . '</div>';
    
    // Clear message
    unset($_SESSION['pass_msg']);
    unset($_SESSION['pass_type']);
}
?>


<form action= "process_change_password.php" method= "POST" class="form-horizontal">
	<div class="form-group row">
    <label for="currentpassword" class="col-sm-2 col-form-label text-right">Current Password</label>
    <div class="col-sm-10">
     <input type="password" class="form-control" id="currentpassword" placeholder="Current Password" name="currentpassword">
    </div>
  </div>

  <div class="form-group row">
    <label for="newpassword1" class="col-sm-2 col-form-label text-right">New Password</label>
    <div class="col-sm-10">
     <input type="password" class="form-control" id="newpassword1" placeholder="New Password" name="newpassword1">
    </div>
  </div>

  <div class="form-group row">
    <label for="newpassword2" class="col-sm-2 col-form-label text-right">Re-enter New Password</label>
    <div class="col-sm-10">
     <input type="password" class="form-control" id="newpassword2" placeholder="Re-enter New Password" name="newpassword2">
    </div>
  </div>

  <br><br>
  <button type="submit" class="btn btn-primary mt-2">SUBMIT CHANGES</button>
</form>



   



  
