<?php
require_once "database.php";
// TODO: Extract $_POST variables, check they're OK, and attempt to create
// an account. Notify user of success/failure and redirect/give navigation 
// options.

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
  die("Please fill in all required fields.");
}

// 2. validate email 
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  die("Invalid email.");
}

// 3. Confirm passwords match 
if ($password !== $passwordConfirmation) {
  die("Passwords do not match.");
}

// 4. ensure password >= 6
if (strlen($password) < 6) {
  die("Password must be atleast 6 characters long.");
}

// 5. check if email already exists
//TODO: 
$query = $connection->prepare("SELECT user_id FROM users WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$query->store_result();

if ($query->num_rows > 0) {
  die("Email already in use.");
}
$query->close();

// 6. check if username already exists 
$query = $connection->prepare("SELECT user_id FROM users WHERE username = ?");
$query->bind_param("s", $username);
$query->execute();
$query->store_result();

if ($query->num_rows > 0) {
  die("Username already taken.");
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
  die("Error creating user: " . $query->error);
}

// get new user_id 
$newUserID = $connection->insert_id;
$query->close();

// 9. Insert user into buyer table 
$query = $connection->prepare("INSERT INTO buyer (user_id) VALUES (?)");
$query->bind_param("i", $newUserID);
if (!$query->execute()) {
  die("Error creating buyer record: " . $query->error);
}
$query->close();


// 10. success message
echo "Registration successful! <a href='login.php'>Login here</a>";

// 11. email confirmation 
$capitalisedFirstName = ucfirst($firstName);
mail($email,"Account Created", "Hi $capitalisedFirstName! 
This email is to confirm the creation your of BUYER account. 
Thank you for registering!", "From: the auction_site");
?>
