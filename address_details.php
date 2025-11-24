<?php
// Ensure error reporting is at the top of the included file too, for safety
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("utilities.php");

// *** FIX A: REMOVED session_start(); ***
// The parent script (my_profile.php or header.php) should already start the session.

if (!isset($_SESSION['user_id'])) {
    // This is a correct check for a non-logged-in user
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];

// Initialize variables
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

    // IMPORTANT: Changed ?: '' to handle empty strings submitted by the form
    $new_address1 = trim($_POST['address_line1']) ?: '';
    $new_address2 = trim($_POST['address_line2']) ?: '';
    $new_city = trim($_POST['city']) ?: '';
    $new_postal_code = trim($_POST['postal_code']) ?: '';

    if ($new_address1 === '' || $new_city === '' || $new_postal_code === '') {
        echo "<div class='alert alert-danger'>Address Line 1, City and Postal Code are required.</div>";
    } else {

        if (!$addressID) {
            // INSERT new address
            $stmt = $connection->prepare("INSERT INTO address (address_line1, address_line2, city, postal_code) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $new_address1, $new_address2, $new_city, $new_postal_code);
            if ($stmt->execute()) {
                $addressID = $connection->insert_id;
                $stmt->close();

                // Update user with new address_id
                $stmt = $connection->prepare("UPDATE users SET address_id = ? WHERE user_id = ?");
                $stmt->bind_param("ii", $addressID, $userID);
                $stmt->execute();
                $stmt->close();

                // Successful redirect (PRG Pattern)
                header("Location: my_profile.php?section=account&tab=address");
                exit();
            } else {
                die("Insert failed: " . $stmt->error);
            }

        } else {
            // UPDATE existing address
            $stmt = $connection->prepare("UPDATE address SET address_line1 = ?, address_line2 = ?, city = ?, postal_code = ? WHERE address_id = ?");
            $stmt->bind_param("ssssi", $new_address1, $new_address2, $new_city, $new_postal_code, $addressID);
            if ($stmt->execute()) {
                $stmt->close();
                
                // Successful redirect (PRG Pattern)
                header("Location: my_profile.php?section=account&tab=address");
                exit();
            } else {
                die("Update failed: " . $stmt->error);
            }
        }
    }
}
?>

<form action="" method="POST" class="form-horizontal">
    <div class="form-group row">
        <label class="col-sm-2 col-form-label text-right">Address Line 1</label>
        <div class="col-sm-10">
            <input type="text" name="address_line1" class="form-control" value="<?= htmlspecialchars($address_line1 ?: '') ?>">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-2 col-form-label text-right">Address Line 2</label>
        <div class="col-sm-10">
            <input type="text" name="address_line2" class="form-control" value="<?= htmlspecialchars($address_line2 ?: '') ?>">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-2 col-form-label text-right">City</label>
        <div class="col-sm-10">
            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($city ?: '') ?>">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-2 col-form-label text-right">Postal Code</label>
        <div class="col-sm-10">
            <input type="text" name="postal_code" class="form-control" value="<?= htmlspecialchars($postal_code ?: '') ?>">
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-2">SUBMIT CHANGES</button>
</form>

