<?php
$to = "luke.adams.25@ucl.ac.uk";
$subject = "Mail Test";
$message = "This is a test email from XAMPP.";
$headers = "From: xampp_test";

if (mail($to, $subject, $message, $headers)) {
    echo "Message sent!";
} else {
    echo "Failed to send.";
}
?>