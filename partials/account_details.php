<?php
$first_name = "";
$family_name = "";
$email = "";
$phone_number = "";
$username = "";

// 1. Fetch Current Data
if (isset($_SESSION['user_id'])) {
    $query = $connection->prepare("SELECT first_name, family_name, email, phone_number, username FROM users WHERE user_id = ?");
    $query->bind_param("i", $_SESSION['user_id']);
    $query->execute();
    $query->bind_result($first_name, $family_name, $email, $phone_number, $username);
    $query->fetch();
    $query->close();
}

// 2. Display Flash Messages (Success/Error)
if (isset($_SESSION['details_msg'])) {
    $msg = $_SESSION['details_msg'];
    $type = $_SESSION['details_type'] ?? 'info';
    echo '<div class="alert alert-' . $type . '">' . htmlspecialchars($msg) . '</div>';
    
    // Clear message
    unset($_SESSION['details_msg']);
    unset($_SESSION['details_type']);
}
?>

<form action="<?php echo BASE_URL; ?>/actions/process_account_details.php" method="POST" class="form-horizontal">
    
    <div class="form-group row">
        <label for="firstname" class="col-sm-2 col-form-label text-right">First Name</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="firstname" name="firstname" 
                   value="<?php echo htmlspecialchars($first_name); ?>" required>
        </div>
    </div>

    <div class="form-group row">
        <label for="lastname" class="col-sm-2 col-form-label text-right">Last Name</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="lastname" name="lastname" 
                   value="<?php echo htmlspecialchars($family_name); ?>" required>
        </div>
    </div>

    <div class="form-group row">
        <label for="username" class="col-sm-2 col-form-label text-right">Username</label> 
        <div class="col-sm-10">
            <input type="text" class="form-control" id="username" name="username" 
                   value="<?php echo htmlspecialchars($username); ?>" required>
        </div>
    </div>

    <div class="form-group row">
        <label for="email" class="col-sm-2 col-form-label text-right">Email</label> 
        <div class="col-sm-10">
            <input type="email" class="form-control" id="email" name="email" 
                   value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
    </div> 

    <div class="form-group row">
        <label for="phonenumber" class="col-sm-2 col-form-label text-right">Phone Number</label> 
        <div class="col-sm-10">
            <input type="text" class="form-control" id="phonenumber" name="phonenumber" 
                   value="<?php echo htmlspecialchars($phone_number ?? ''); ?>" 
                   placeholder="No current number">
        </div>
    </div>

    <br><br>

    <button type="submit" class="btn btn-primary mt-2">SUBMIT CHANGES</button>

</form>
