<?php
// 1. Start Session & Connect DB
session_start();
require_once "../config/database.php";

// 2. Access Control
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$addressID = null;

// 3. Get current Address ID
$query = $connection->prepare("SELECT address_id FROM users WHERE user_id = ?");
$query->bind_param("i", $userID);
$query->execute();
$query->bind_result($addressID);
$query->fetch();
$query->close();

// 4. Process Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $new_address_line1 = trim($_POST['address_line1']);
    $new_address_line2 = trim($_POST['address_line2']);
    $new_city = trim($_POST['city']);
    $new_postal_code = trim($_POST['postal_code']);

    // Validation
    if (empty($new_address_line1) || empty($new_city) || empty($new_postal_code)) {
        $_SESSION['address_msg'] = "Please fill in all required fields.";
        $_SESSION['address_type'] = "danger"; 
    } 
    else {
        if ($addressID) {
            // --- UPDATE EXISTING ---
            $query = $connection->prepare("UPDATE address SET address_line1=?, address_line2=?, city=?, postal_code=? WHERE address_id=?");
            $query->bind_param("ssssi", $new_address_line1, $new_address_line2, $new_city, $new_postal_code, $addressID);
            
            if ($query->execute()) {
                $_SESSION['address_msg'] = "Address updated successfully.";
                $_SESSION['address_type'] = "success";
            } else {
                $_SESSION['address_msg'] = "Database Error: " . $connection->error;
                $_SESSION['address_type'] = "danger";
            }
            $query->close();
        } else {
            // --- INSERT NEW ---
            $query = $connection->prepare("INSERT INTO address (address_line1, address_line2, city, postal_code) VALUES (?, ?, ?, ?)");
            $query->bind_param("ssss", $new_address_line1, $new_address_line2, $new_city, $new_postal_code);
            
            if ($query->execute()) {
                $newID = $connection->insert_id;
                $query->close();
                
                // Link to User
                $linkQuery = $connection->prepare("UPDATE users SET address_id = ? WHERE user_id = ?");
                $linkQuery->bind_param("ii", $newID, $userID);
                
                if ($linkQuery->execute()) {
                    $_SESSION['address_msg'] = "New address created and linked.";
                    $_SESSION['address_type'] = "success";
                } else {
                    $_SESSION['address_msg'] = "Address created but failed to link to user.";
                    $_SESSION['address_type'] = "warning";
                }
                $linkQuery->close();
            } else {
                $_SESSION['address_msg'] = "Insert Failed: " . $connection->error;
                $_SESSION['address_type'] = "danger";
            }
        }
    }
}

// 5. Redirect back to Profile
header("Location: " . BASE_URL . "/my_profile.php?section=account&tab=address");
exit();
?>
