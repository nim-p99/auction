<?php 
include_once "includes/header.php";

// Recover inputs if the user messed up (so they don't have to retype everything)
$email_val = $_SESSION['form_data']['email'] ?? '';
$username_val = $_SESSION['form_data']['username'] ?? '';
$firstname_val = $_SESSION['form_data']['firstName'] ?? '';
$lastname_val = $_SESSION['form_data']['lastName'] ?? '';
$phone_val = $_SESSION['form_data']['phoneNumber'] ?? '';

// Clear the saved form data so it doesn't persist forever
unset($_SESSION['form_data']);
?>

<div class="container">
<h2 class="my-3">Register new account</h2>

<?php
    if (isset($_SESSION['reg_msg'])) {
        $msg = $_SESSION['reg_msg'];
        $type = $_SESSION['reg_type'] ?? 'info';
        
        echo '<div class="alert alert-' . $type . '">' . htmlspecialchars($msg) . '</div>';
        
        // Clear message
        unset($_SESSION['reg_msg']);
        unset($_SESSION['reg_type']);
    }
?>

<form method="POST" action="<?php echo BASE_URL; ?>/actions/process_registration.php">
        
        <div class="form-group row">
            <label for="email" class="col-sm-2 col-form-label text-right">Email</label>
            <div class="col-sm-10">
                <input name="email" type="email" class="form-control" id="email" 
                       placeholder="Email" value="<?php echo htmlspecialchars($email_val); ?>" required>
                <small id="emailHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <div class="form-group row">
            <label for="username" class="col-sm-2 col-form-label text-right">Username</label>
            <div class="col-sm-10">
                <input name="username" type="text" class="form-control" id="username" 
                       placeholder="Username" value="<?php echo htmlspecialchars($username_val); ?>" required>
                <small id="usernameHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <div class="form-group row">
            <label for="password" class="col-sm-2 col-form-label text-right">Password</label>
            <div class="col-sm-10">
                <input name="password" type="password" class="form-control" id="password" 
                       placeholder="Password" required minlength="6">
                <small id="passwordHelp" class="form-text text-muted"><span class="text-danger">* Required (Min 6 chars).</span></small>
            </div>
        </div>

        <div class="form-group row">
            <label for="passwordConfirmation" class="col-sm-2 col-form-label text-right">Repeat password</label>
            <div class="col-sm-10">
                <input name="passwordConfirmation" type="password" class="form-control" id="passwordConfirmation" 
                       placeholder="Enter password again" required>
                <small id="passwordConfirmationHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <div class="form-group row">
            <label for="firstName" class="col-sm-2 col-form-label text-right">First Name</label>
            <div class="col-sm-10">
                <input name="firstName" type="text" class="form-control" id="firstName" 
                       placeholder="First Name" value="<?php echo htmlspecialchars($firstname_val); ?>" required>
                <small id="firstNameHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <div class="form-group row">
            <label for="lastName" class="col-sm-2 col-form-label text-right">Last Name</label>
            <div class="col-sm-10">
                <input name="lastName" type="text" class="form-control" id="lastName" 
                       placeholder="Last Name" value="<?php echo htmlspecialchars($lastname_val); ?>" required>
                <small id="lastNameHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <div class="form-
