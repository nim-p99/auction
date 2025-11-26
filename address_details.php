<?php
/* ini_set('display_errors', 1); */
/* ini_set('display_startup_errors', 1); */
/* error_reporting(E_ALL); */
include_once("utilities.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$address_line1 = null;
$address_line2 = null;
$city = null;
$postal_code = null;
$addressID = null;

// Get current address_id
$stmt = $connection->prepare("SELECT address_id FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->bind_result($addressID);
$stmt->fetch();
$stmt->close();

// If address exists, fetch details
if ($addressID) {
    $stmt = $connection->prepare("SELECT address_line1, address_line2, city, postal_code FROM address WHERE address_id = ?");
    $stmt->bind_param("i", $addressID);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($address_line1, $address_line2, $city, $postal_code);
        $stmt->fetch();
    }
    $stmt->close();
}



// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    

    var_dump($_POST);
    die('Post triggered');

    $new_address1 = trim($_POST['address_line1']) ?? '';
    $new_address2 = trim($_POST['address_line2']) ?? '';
    $new_city = trim($_POST['city']) ?? '';
    $new_postal_code = trim($_POST['postal_code']) ?? '';

    if ($new_address1 === '' || $new_city === '' || $new_postal_code === '') {
        echo "<div class='alert alert-danger'>Address Line 1, City and Postal Code are required.</div>";
    } else {
    
        if (!$addressID) {
            // INSERT new address
            $insert = $connection->prepare("INSERT INTO address (address_line1, address_line2, city, postal_code) VALUES (?, ?, ?, ?)");
            $insert->bind_param("ssss", $new_address1, $new_address2, $new_city, $new_postal_code);
            if ($insert->execute()) {
                $addressID = $connection->insert_id;
                $insert->close();

                // Update user with new address_id
                $update = $connection->prepare("UPDATE users SET address_id = ? WHERE user_id = ?");
                $update->bind_param("ii", $addressID, $userID);
                if ($update->execute()) {
                  //$update->close();
                  echo '<div class="alert alert-success">Your address details were updated.</div>';
                  //header("Refresh: 1");
                  exit();
                } else {
                  echo '<div class="alert alert-danger">Failed to update address details: ' . htmlspecialchars($update->error) . '</div>';
                  //$update->close();
                }
                //header("Location: my_profile.php?section=account&tab=address"); 
            } else {
              $insert->close();
            }
        } else {
            // UPDATE existing address
            $update = $connection->prepare("UPDATE address SET address_line1 = ?, address_line2 = ?, city = ?, postal_code = ? WHERE address_id = ?");
            $update->bind_param("ssssi", $new_address1, $new_address2, $new_city, $new_postal_code, $addressID);
            if ($update->execute()) {
                //$update->close();
                
                // Successful redirect (PRG Pattern)
                echo '<div class="alert alert-success">Your address details were updated.</div>';
                header("Refresh: 1");
                exit(); 
            } else {
              echo '<div class="alert alert-danger">Failed to update address details: ' . htmlspecialchars($update->error) . '</div>';
              //$update->close();
            }
        }
    }
}
?>

  <form action="" method="POST" class="form-horizontal">
    <div class="form-group row">
        <label for="address_line1" class="col-sm-2 col-form-label text-right">Address Line 1</label>
        <div class="col-sm-10">
            <input type="text" name="address_line1" class="form-control" value="<?php echo htmlspecialchars($address_line1 ?? ''); ?>">
        </div>
    </div>

    <div class="form-group row">
        <label for="address_line2" class="col-sm-2 col-form-label text-right">Address Line 2</label>
        <div class="col-sm-10">
            <input type="text" name="address_line2" class="form-control" value="<?php echo htmlspecialchars($address_line2 ?? ''); ?>">
        </div>
    </div>

    <div class="form-group row">
        <label for="city" class="col-sm-2 col-form-label text-right">City</label>
        <div class="col-sm-10">
            <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($city ?? ''); ?>">
        </div>
    </div>

    <div class="form-group row">
        <label for="postal_code" class="col-sm-2 col-form-label text-right">Postal Code</label>
        <div class="col-sm-10">
            <input type="text" name="postal_code" class="form-control" value="<?php echo htmlspecialchars($postal_code ?? ''); ?>">
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-2">SUBMIT CHANGES</button>
</form>

