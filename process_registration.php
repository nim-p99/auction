<?php
require_once "database.php";
session_start();
// TODO: Extract $_POST variables, check they're OK, and attempt to create
// an account. Notify user of success/failure and redirect/give navigation 
// options.


// Helper to handle errors nicely
function register_error($message) {
    $_SESSION['reg_msg'] = $message;
    $_SESSION['reg_type'] = "danger";
    // Save the input so the user doesn't have to retype everything
    $_SESSION['form_data'] = $_POST;
    header("Location: register.php");
    exit();
}

// Extract $_POST variables 
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$passwordConfirmation = trim($_POST['passwordConfirmation'] ?? '');
$username = trim($_POST['username'] ?? '');
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$phoneNumber = trim($_POST['phoneNumber'] ?? null);

// 1. Ensure all required fields are filled in 

if (
  empty($email) ||
  empty($password) ||
  empty($passwordConfirmation) ||
  empty($username) ||
  empty($firstName) ||
  empty($lastName)
) {
  register_error("Please fill in all required fields.");
}

// 2. validate email 
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  register_error("Invalid email address."); 
}

// 3. Confirm passwords match 
if ($password !== $passwordConfirmation) {
  register_error("Passwords do not match.");
}

// 4. ensure password >= 6
if (strlen($password) < 6) {
  register_error("Password must be at least 6 characters long.");
}

// 5. check if email already exists
//TODO: 
$query = $connection->prepare("SELECT user_id FROM users WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$query->store_result();

if ($query->num_rows > 0) {
  $query->close();
  register_error("Email is already in use.");
}
$query->close();

// 6. check if username already exists 
$query = $connection->prepare("SELECT user_id FROM users WHERE username = ?");
$query->bind_param("s", $username);
$query->execute();
$query->store_result();

if ($query->num_rows > 0) {
    $query->close();
    register_error("Username is already taken.");
}
$query->close();

// 7. hash the passowrd 
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// 8. Insert user 
$query = $connection->prepare("
  INSERT INTO users
  (email, password, username, first_name, family_name, phone_number)
  VALUES (?, ?, ?, ?, ?, ?)
  ");

$query->bind_param("ssssss",
  $email,
  $hashedPassword,
  $username,
  $firstName,
  $lastName,
  $phoneNumber
);

if (!$query->execute()) {
  register_error("Error creating user: " . $connection->error);
}

// get new user_id 
$newUserID = $connection->insert_id;
$query->close();

// 9. Insert user into buyer table 
$query = $connection->prepare("INSERT INTO buyer (user_id) VALUES (?)");
$query->bind_param("i", $newUserID);
if (!$query->execute()) {
  register_error("Error creating buyer record: " . $connection->error);  
}
$query->close();

// 10. email confirmation 
$capitalisedFirstName = ucfirst($firstName);
mail($email,"Account Created", "Hi $capitalisedFirstName! 
This email is to confirm the creation your of BUYER account. 
Thank you for registering!", "From: the auction_site");

// 11. success! redirect to register page
$_SESSION['reg_msg'] = "Registration successful! You can now log in.";
$_SESSION['reg_type'] = "success";

// Clear form data if it existed
unset($_SESSION['form_data']);

header("Location: register.php");
exit();
?>
