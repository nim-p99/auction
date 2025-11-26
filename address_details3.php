<?php
$userID = $_SESSION['user_id'];
$address_line1 = "";
$address_line2 = "";
$city = "";
$postal_code = "";
$addressID = null;

// CHECK FOR MESSAGES FROM process_address.php 
if (isset($_SESSION['address_msg'])) {
    $msg = $_SESSION['address_msg'];
    $type = $_SESSION['address_type'] ?? 'info'; // Default to info if type is missing
    
    echo '<div class="alert alert-' . $type . '">' . htmlspecialchars($msg) . '</div>';
    
    // Clear the message so it doesn't show again on refresh
    unset($_SESSION['address_msg']);
    unset($_SESSION['address_type']);
}



// Fetch current data to populate form
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
?>

<form action="process_address.php" method="POST" class="form-horizontal">
    
    <div class="form-group row">
        <label for="address_line1" class="col-sm-2 col-form-label text-right">Address Line 1</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="address_line1" name="address_line1" 
                   value="<?php echo htmlspecialchars($address_line1); ?>" required>
        </div>
    </div>

    <div class="form-group row">
        <label for="address_line2" class="col-sm-2 col-form-label text-right">Address Line 2</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="address_line2" name="address_line2" 
                   value="<?php echo htmlspecialchars($address_line2); ?>">
        </div>
    </div>

    <div class="form-group row">
        <label for="city" class="col-sm-2 col-form-label text-right">City</label> 
        <div class="col-sm-10">
            <input type="text" class="form-control" id="city" name="city" 
                   value="<?php echo htmlspecialchars($city); ?>" required>
        </div>
    </div>

    <div class="form-group row">
        <label for="postal_code" class="col-sm-2 col-form-label text-right">Postal Code</label> 
        <div class="col-sm-10">
            <input type="text" class="form-control" id="postal_code" name="postal_code" 
                   value="<?php echo htmlspecialchars($postal_code); ?>" required>
        </div>
    </div> 
    
    <br><br>
    <button type="submit" class="btn btn-primary mt-2">SUBMIT CHANGES</button>
</form>
