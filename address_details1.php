<?php
include_once("utilities.php");


$address_line1 = null;
$address_line2 = null;
$city = null;
$postal_code = null;
$userID = $_SESSION['user_id'];
$addressID = null;

$query = $connection->prepare("SELECT address_id FROM users WHERE user_id = ?");
$query->bind_param("i", $userID);
$query->execute();
$query->bind_result($addressID);
$query->fetch();
$query->close();

if ($addressID) {
  $query = $connection->prepare("SELECT address_line1, address_line2, city, postal_code FROM address WHERE address_id = ?");
  $query->bind_param("i", $addressID);
  $query->execute();
  $query->store_result();
  if ($query->num_rows > 0) {
  $query->bind_result($address_line1, $address_line2, $city, $postal_code);
  $query->fetch();
  }
  $query->close();
}

# Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Get updated values or use default if blank
  $new_address_line1 = trim($_POST['address_line1']) ?: $address_line1;
  $new_address_line2 = trim($_POST['address_line2']) ?: $address_line2;
  $new_city = trim($_POST['city']) ?: $city;
  $new_postal_code = trim($_POST['postal_code']) ?: $postal_code;
  
  // update users table
  $query = $connection->prepare("
    INSERT INTO address
    (address_line1, address_line2, city, postal_code)
    VALUES (?, ?, ?, ?)");
  
  if (!$query) {
    die("Database Error: " . $connection->error);
  }

  $query->bind_param(
    "ssss",
    $new_address_line1,
    $new_address_line2,
    $new_city,
    $new_postal_code
  );

  if ($query->execute()) {
    echo '<div class="alert alert-success">Your account details were updated.</div>';


    // update session value if username changed
    //$_SESSION['username'] = $new_user;

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
  <label for="address_line1" class="col-sm-2 col-form-label text-right">Address Line 1</label>
  <div class="col-sm-10">
    <input type="text" class="form-control" id="address_line1" value="<?php echo htmlspecialchars($address_line1); ?>" name="address_line1">
  </div>
</div>

<div class="form-group row">
  <label for="address_line2" class="col-sm-2 col-form-label text-right">Address Line 2</label>
  <div class="col-sm-10">
    <input type="text" class="form-control" id="address_line2" value="<?php echo htmlspecialchars($address_line2); ?>" name="address_line2">
  </div>
</div>

<div class="form-group row">
  <label for="city" class="col-sm-2 col-form-label text-right">City</label> 
  <div class="col-sm-10">
    <input type="text" class="form-control" id="city" value="<?php echo htmlspecialchars($city); ?>" name="city"> <!--should they be able to change?-->
  </div>
</div>

<div class="form-group row">
  <label for="postal_code" class="col-sm-2 col-form-label text-right">Postal Code</label> 
  <div class="col-sm-10">
    <input type="text" class="form-control" id="postal_code" value="<?php echo htmlspecialchars($postal_code); ?>" name="postal_code">
  </div>
</div> 
<br><br>

<button type="submit" class="btn btn-primary mt-2">SUBMIT CHANGES</button>

</form>


   



  
