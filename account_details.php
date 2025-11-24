<?php
include_once("utilities.php");


$first_name = null;
$family_name = null;
$email = null;
$phone_number = null;
$username = $_SESSION['username'];

$query = $connection->prepare("SELECT first_name, family_name, email, phone_number FROM users WHERE user_id = ?");
$query->bind_param("i", $_SESSION['user_id']);
$query->execute();
$query->bind_result($first_name, $family_name, $email, $phone_number);
$query->fetch();
$query->close();

if (!$phone_number) {
  $phone_number = "No Current Number";
}

# Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Get updated values or use default if blank
  $new_first = trim($_POST['firstname']) ?: $first_name;
  $new_last = trim($_POST['lastname']) ?: $family_name;
  $new_user = trim($_POST['username']) ?: $username;
  $new_email = trim($_POST['email']) ?: $email;
  $new_phone = trim($_POST['phonenumber']) ?: $phone_number;

  // update users table
  $query = $connection->prepare("
    UPDATE users
    SET first_name = ?, family_name = ?, username = ?,
    email = ?, phone_number = ?
    WHERE user_id = ?");
  
  if (!$query) {
    die("Database Error: " . $connection->error);
  }

  $query->bind_param(
    "sssssi",
    $new_first,
    $new_last,
    $new_user,
    $new_email,
    $new_phone,
    $_SESSION['user_id']
  );

  if ($query->execute()) {
    echo '<div class="alert alert-success">Your account details were updated.</div>';


    // update session value if username changed
    $_SESSION['username'] = $new_user;

    // refresh page to show new placeholders
    header("Refresh: 1");
    exit();
  } else {
    echo '<div class="alert alert-danger">Failed to update account: ' . htmlspecialchars($query->error) . '</div>';
  }
  
  $query->close();
}


?>

<form action= "" method= "POST" class="form-horizontal">
<div class="form-group row">
  <label for="firstname" class="col-sm-2 col-form-label text-right">First Name</label>
  <div class="col-sm-10">
    <input type="text" class="form-control" id="firstname" value=<?php echo htmlspecialchars($first_name); ?> name="firstname">
  </div>
</div>

<div class="form-group row">
  <label for="lastname" class="col-sm-2 col-form-label text-right">Last Name</label>
  <div class="col-sm-10">
    <input type="text" class="form-control" id="lastname" value=<?php echo htmlspecialchars($family_name); ?> name="lastname">
  </div>
</div>

<div class="form-group row">
  <label for="username" class="col-sm-2 col-form-label text-right">Username</label> 
  <div class="col-sm-10">
    <input type="text" class="form-control" id="username" value=<?php echo htmlspecialchars($username); ?> name="username"> <!--should they be able to change?-->
  </div>
</div>

<div class="form-group row">
  <label for="email" class="col-sm-2 col-form-label text-right">Email</label> 
  <div class="col-sm-10">
    <input type="text" class="form-control" id="email" value=<?php echo htmlspecialchars($email); ?> name="email">
  </div>
</div> 

<div class="form-group row">
  <label for="phonenumber" class="col-sm-2 col-form-label text-right">Phone Number</label> 
  <div class="col-sm-10">
    <input type="text" class="form-control" id="phonenumber" value= <?php echo htmlspecialchars($phone_number); ?> name="phonenumber">
  </div>
</div>

<br><br>
<button type= "submit">SUBMIT CHANGES</button>

</form>


   



  
